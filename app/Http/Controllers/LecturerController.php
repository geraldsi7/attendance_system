<?php

namespace App\Http\Controllers;

use App\Http\Requests\LecturerLoginRequest;
use App\Http\Requests\LecturerProfileUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Imports\LecturerImport;
use App\Mail\LecturerSessionEnded;
use App\Mail\LecturerSessionScheduled;
use App\Mail\LecturerSessionStarted;
use App\Mail\StudentSessionEnded;
use App\Mail\StudentSessionScheduled;
use App\Mail\StudentSessionStarted;
use App\Models\Attendance;
use App\Models\Classe;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Lecturer;
use App\Models\Section;
use App\Models\Session;
use App\Models\Student;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class LecturerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $lecturer = Auth::guard('lecturer')->user();
        $lecturer->load('course', 'session', 'classe.student');

        $courses  = $lecturer->course;
        $sessions = $lecturer->session;
        $classes =  $lecturer->classe;
        $students = $classes->sum(function ($classe) {
            return $classe->student->count();
        });

        $number_of_courses = number_format($courses->count());
        $number_of_sessions = number_format($sessions->count());
        $number_of_classes = number_format($classes->count());
        $number_of_students = number_format($students);

        $classeData = [];

        foreach ($classes as $classe) {
            $classe->title = $classe->department->title . ' ' . $classe->level->title;

            $sectionCount = Classe::where('department_id', $classe->department->id)
                ->where('level_id', $classe->level->id)
                ->distinct('section_id')
                ->count('section_id');

            $classe->title .= $sectionCount > 1 ? ' ' . $classe->section->title : '';


            $classeData[] = [
                'label'  => $classe->title,
                'students'  => $classe->student->count()
            ];
        }

        return Inertia::render('Lecturer/Index', compact(
            'number_of_courses',
            'number_of_sessions',
            'number_of_classes',
            'number_of_students',
            'classeData'
        ));
    }

    public function fetchAttendanceAnalytics(Request $request)
    {
        $lecturer = Auth::guard('lecturer')->user();
        $lecturer->load('session.attendance');

        $filter_session = $request->session;

        $sessions = $lecturer->session()->take($filter_session)->get();

        $sessionData = [];

        foreach ($sessions as $session) {
            $sessionData[] = [
                'label'  => $session->title,
                'attendance'  => $session->attendance->count()
            ];
        }

        return response()->json([
            'sessions_data'  => $sessionData,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function showLoginForm()
    {
        return Inertia::render('Auth/Lecturer/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status')
        ]);
    }

    public function login(LecturerLoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->route('lecturer.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('lecturer')->logout();

        return redirect()->route('home');
    }

    public function showForgotPasswordForm()
    {
        return Inertia::render('Auth/Lecturer/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function requestPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email', 
        ]);

        $status = Password::broker('lecturers')->sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function showResetPasswordForm(Request $request)
    {
        return Inertia::render('Auth/Lecturer/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    public function storePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::broker('lecturers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($lecturer) use ($request) {
                $lecturer->forceFill([
                    'password' => Hash::make($request->password)
                ])->save();

                event(new PasswordReset($lecturer));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('lecturer.login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }    

    public function courses()
    {
        $lecturer = Auth::guard('lecturer')->user();
        $lecturer->load('course.classe.department', 'course.classe.level', 'course.classe.section');

        $courses = $lecturer->course;

        foreach ($courses as $course) {
            foreach ($course->classe as $classe) {
                $classe->title = $classe->department->title . ' ' . $classe->level->title;

                $sectionCount = Classe::where('department_id', $classe->department->id)
                    ->where('level_id', $classe->level->id)
                    ->distinct('section_id')
                    ->count('section_id');

                $classe->title .= $sectionCount > 1 ? ' ' . $classe->section->title : '';
            }
        }

        return Inertia::render('Lecturer/Courses', compact('courses'));
    }

    public function students()
    {
        $lecturer = Auth::guard('lecturer')->user();
        $lecturer->load('classe');

        $classes = $lecturer->classe;

        foreach ($classes as $classe) {
            $classe->title = $classe->department->title . ' ' . $classe->level->title;

            $sectionCount = Classe::where('department_id', $classe->department->id)
                ->where('level_id', $classe->level->id)
                ->distinct('section_id')
                ->count('section_id');

            $classe->title .= $sectionCount > 1 ? ' ' . $classe->section->title : '';
        }

        $sortedClasses = $classes->sortBy('title')->values();

        $classes = $sortedClasses;

        return Inertia::render('Lecturer/Students', compact('classes'));
    }

    public function fetchStudents(Request $request)
    {
        $classe = Classe::find($request->class_id);

        $students = $classe->student;

        return DataTables::of($students)
            ->make(true);
    }

    public function sessions()
    {
        $lecturer = Auth::guard('lecturer')->user();
        $lecturer->load('course');

        $courses = $lecturer->course()->orderBy('title')->get();
        $venues = Venue::orderBy('title')->get();

        $courses  = $lecturer->course;
         return Inertia::render('Lecturer/Sessions', compact('courses', 'venues'));
    }

    public function fetchSessions(Request $request)
    {
        $lecturer = Auth::guard('lecturer')->user();
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $data = $lecturer->session()->with([
            'course',
            'venue',
            'attendance.student' => function ($q) {
                $q->orderBy('name');
            }
        ])->whereDate('starts_at', $date)
            ->orderByDesc('starts_at')
            ->get();

        foreach ($data as $session) {
            $session->starts = Carbon::parse($session->starts_at)->format('D, d M, Y H:i');
            $session->ends = Carbon::parse($session->ends_at)->format('D, d M, Y H:i');
            $session->full_date = Carbon::parse($session->starts_at)->format('l d F, Y H:i') . ' - ' . Carbon::parse($session->ends_at)->format('l d F, Y H:i');

            $session->classe->title = $session->classe->department->title . ' ' . $session->classe->level->title;

            $sectionCount = Classe::where('department_id', $session->classe->department->id)
                ->where('level_id', $session->classe->level->id)
                ->distinct('section_id')
                ->count('section_id');

            $session->classe->title .= $sectionCount > 1 ? ' ' . $session->classe->section->title : '';

            foreach($session->attendance as $attendance){
                $attendance->signed_at = Carbon::parse($attendance->created_at)->format('d/m/Y H:i');
            }
        }

        
        return response()->json([
            'row' => $data
        ]);
    }

    public function fetchSessionsClasses(Request $request)
    {
        $course = Course::find($request->id);
        $course->load('classe');

        $classes = $course->classe;

        foreach ($classes as $classe) {
            $classe->title = $classe->department->title . ' ' . $classe->level->title;

            $sectionCount = Classe::where('department_id', $classe->department->id)
                ->where('level_id', $classe->level->id)
                ->distinct('section_id')
                ->count('section_id');

            $classe->title .= $sectionCount > 1 ? ' ' . $classe->section->title : '';
        }

        // Sort classes by the composited title in ascending order
        $sortedClasses = $classes->sortBy('title')->values();

        $classes = $sortedClasses;

        return response()->json([
            'row' => $classes
        ]);
    }

    public function storeSession(Request $request)
    {
        $request->validate(
            [
                'title'  => 'required|string|max:100',
                'course_id' => 'required',
                'classe_id' => 'required',
                'venue_id'   => 'required',
                'starts_at'  => 'required|before:ends_at',
                'ends_at'    => 'required|after:starts_at',
            ],
            [
                'course_id.required'  => 'The course field is required.',
                'classe_id.required'  => 'The class field is required.',
                'venue_id.required' => 'The venue field is required.',
            ]
        );

        $lecturer = Auth::guard('lecturer')->user();
        $input = $request->all();

        $input['status'] = 'Scheduled';

        $session = new Session($input);

        $data = $lecturer->session()->save($session);

        $this->sendScheduledSessionMail($data);
    }

    public function editSession(Request $request)
    {
        $session = Session::find($request->id);

        return response()->json([
            'row' => $session
        ]);
    }


    public function updateSession(Request $request, Session $session)
    {
        $request->validate(
            [
                'title'  => 'required|string|max:100',
                'course_id' => 'required',
                'classe_id' => 'required',
                'venue_id'   => 'required',
                'starts_at'  => 'required|before:ends_at',
                'ends_at'    => 'required|after:starts_at',
            ],
            [
                'course_id.required'  => 'The course field is required.',
                'classe_id.required'  => 'The class field is required.',
                'venue_id.required' => 'The venue field is required.',
            ]
        );


        $input = $request->all();
        $input['status'] = 'Scheduled';

        $session->fill($input)->save();

        $this->sendScheduledSessionMail($session);
    }

    public function startSession(Request $request)
    {
        $input = $request->all();

        $data = Session::find($input['id']);

        $oldStartDate = Carbon::parse($data->starts_at);
        $oldEndDate = Carbon::parse($data->ends_at);

        // Calculate the difference between the old start and end dates in seconds
        $durationInSeconds = $oldEndDate->diffInSeconds($oldStartDate);

        $newStartDate = Carbon::now();

        // Calculate the new end date by adding the duration to the new start date
        $newEndDate = $newStartDate->copy()->addSeconds($durationInSeconds);


        $input['starts_at'] = $newStartDate;
        $input['ends_at'] = $newEndDate;
        $input['status'] = 'Running';

        $data->fill($input)->save();

        $this->sendStartedSessionMail($data);
    }

    public function endSession(Request $request)
    {
        $input = $request->all();

        $data = Session::find($input['id']);

        $input['ends_at'] = Carbon::now();
        $input['status'] = 'Ended';

        $data->fill($input)->save();

        $this->sendEndedSessionMail($data);
    }

    public function sendScheduledSessionMail($data)
    {
        // Send mail notification to lecturer
        Mail::to($data->lecturer->email)->queue(new LecturerSessionScheduled($data));

        // Send mail notification to attendees
        $classe = $data->classe;

        $classe->student()->chunk(10, function ($students) use ($data) {
            foreach ($students as $student) {
                // Send email to each student in the chunk
                Mail::to($student->email)->queue(new StudentSessionScheduled($data));
            }
        });
    }

    public function sendStartedSessionMail($data)
    {
        // Send mail notification to lecturer
        Mail::to($data->lecturer->email)->queue(new LecturerSessionStarted($data));


        // Send mail notification to attendees
        $classe = $data->classe;

        $classe->student()->chunk(10, function ($students) use ($data) {
            foreach ($students as $student) {
                // Send email to each student in the chunk
                Mail::to($student->email)->queue(new StudentSessionStarted($data));
            }
        });
    }

    public function sendEndedSessionMail($data)
    {
        // Send mail notification to lecturer
        Mail::to($data->lecturer->email)->queue(new LecturerSessionEnded($data));


        // Send mail notification to attendees
        $classe = $data->classe;

        $classe->student()->chunk(10, function ($students) use ($data) {
            foreach ($students as $student) {
                // Send email to each student in the chunk
                Mail::to($student->email)->queue(new StudentSessionEnded($data));
            }
        });
    }
    public function showSessionAttendance(Request $request)
    {
        $session = Session::find($request->id);
        $section = Section::find($session->section->id);
        $totalStudents = $section->student->count();

        $attendance = $session->attendance;
        $students_present = $attendance->count();
        $students_absent = $totalStudents - $students_present;

        if ($totalStudents == 0) {
            $students_present_percentage = 0;
        } else {
            $students_present_percentage = ($students_present / $totalStudents) * 100;
        }

        $students_absent_percentage = 100 - $students_present_percentage;

        return response()->json([
            'students_present' => number_format($students_present),
            'students_absent' => number_format($students_absent),
            'students_present_percentage' =>  number_format($students_present_percentage, 2),
            'students_absent_percentage' =>  number_format($students_absent_percentage, 2),
        ]);
    }
    public function destroySession(Request $request)
    {
        $input = $request->all();

        $data = Session::find($input['id']);

        $data->attendance()->delete();

        $data->delete();
    }
    public function create()
    {
        //
    }

    public function showLecturer()
    {
        return Inertia::render('Setup/Lecturer');
    }

    public function fetch()
    {

        $data = Lecturer::get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $btnClasses = 'flex items-center justify-center p-1 bg-gray-800 text-white font-normal text-sm leading-tight rounded-sm shadow-sm hover:bg-gray-700 hover:shadow-lg focus:bg-gray-700 focus:shadow-md focus:outline-none focus:ring-0 active:bg-gray-800 active:shadow-md transition duration-150 ease-in-out';
                return '
                <div class="flex items-center gap-4">
                <button type="button" data-id="' . $row->id . '" class="edit ' . $btnClasses . '">
                <span class="material-symbols-outlined">
                edit
                </span>
                </button>
                
                <button type="button" data-id="' . $row->id . '" class="reset ' . $btnClasses . '">
                <span class="material-symbols-outlined">
                lock_reset
                </span>
                </button>
               
                <button type="button" data-id="' . $row->id . '" class="delete flex items-center justify-center p-1 w-10 h-10 bg-red-600 text-white font-normal text-sm leading-tight rounded-sm shadow-sm hover:bg-red-700 hover:shadow-lg focus:bg-red-700 focus:shadow-md focus:outline-none focus:ring-0 active:bg-red-800 active:shadow-md transition duration-150 ease-in-out">
                <span class="material-symbols-outlined">
                delete
                </span>
                </button>
                </div>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|lowercase|email|max:100|unique:' . Lecturer::class,
            'staff_id' => 'required|string|max:20|unique:' . Lecturer::class,
            // 'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $input = $request->all();

        $input['password'] = Hash::make($input['staff_id']);
        $input['email_verified_at'] = Carbon::now();

        Lecturer::create($input);
    }

    public function import(Request $request)
    {
        $file = $request->file;

        // Validate the import file
        $validator = Validator::make(['file' => $file], [
            'file' => 'required|file|mimes:csv,txt', // Add any validation rules you need
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400); // Return validation errors
        }

        $headingRow = (new HeadingRowImport)->toArray($file);

        $headings = $headingRow[0][0];

        // Define the expected headers with corresponding column letters.
        $headers = [
            'A' => 'name',
            'B' => 'email',
            'C' => 'staff_id'
        ];

        $headingErrorMsg = [];

        foreach ($headings as $key => $heading) {
            // Convert the 0-based index to a column letter (e.g., 0 becomes 'A', 1 becomes 'B', etc.).
            $column = chr(ord('A') + $key);
            $expectedHeader = $headers[$column];

            if ($heading === null || is_numeric($heading)) {
                $headingErrorMsg[] = "Column {$column} in the provided spreadsheet does not match column {$column} of the import template. This column must be \"{$expectedHeader}\" and is required to be present.";
            } else if ($heading !== $headers[$column]) {
                $headingErrorMsg[] = "Column {$column} in the provided spreadsheet does not match column {$column} of the import template. This column must be \"{$expectedHeader}\" and the value provided was \"" .  $heading . '".';
            }

            if ($key === count($headers) - 1) {
                break;
            }
        }

        $validHeaders = array_filter($headings, function ($header) {
            return !is_numeric($header);
        });

        $headingCount = count($validHeaders);

        if ($headingCount > count($headers)) {
            $action = 'remove';
        } else {
            $action = 'add';
        }

        if ($headingCount !== count($headers)) {
            // Calculate the expected column count
            $expectedColumnCount = count($headers);

            // Determine if 'column' should be plural
            $plural = $expectedColumnCount !== 1 ? 's' : '';

            // Construct the error message using concatenation
            $msg = 'The spreadsheet should have exactly ' . $expectedColumnCount . ' column' . $plural . '. The provided spreadsheet has ' . number_format($headingCount) . ' columns. Please reference the import template and ' . $action . ' all additional columns from your spreadsheet.';

            // Add the error message to the array
            array_push($headingErrorMsg, $msg);
        }

        if (!empty($headingErrorMsg)) {

            return response()->json([
                'message' => 'The header row of your import file does not match the required header. The header row must exactly match the header of the import template (' . implode(', ', $headers) . ').',
                'header_errors' => $headingErrorMsg
            ], 412);
        }

        try {
            Excel::import(new LecturerImport, $file);
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $failedRows = [];

            if (!empty($failures)) {
                foreach ($failures as $failure) {
                    $errorRow = $failure->row();
                    $errorMsg = $failure->errors();

                    if (array_key_exists($errorRow, $failedRows)) {
                        $failedRows[$errorRow] = array_merge($failedRows[$errorRow], $errorMsg);
                    } else {
                        $failedRows[$errorRow] = $errorMsg;
                    }
                }

                return response()->json([
                    'message' => 'There are validation errors in your import file.',
                    'failed_rows' => $failedRows
                ], 412);
            }
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $user = Auth::guard('lecturer')->user();

        return Inertia::render('Lecturer/Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(LecturerProfileUpdateRequest $request)
    {
        $user = Auth::guard('lecturer')->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('lecturer.profile.edit');
    }

    public function editProfile(Request $request)
    {
        $data = Lecturer::find($request->id);

        return response()->json([
            'row'   => $data
        ]);
    }

    public function updateProfile(Request $request, Lecturer $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:100', Rule::unique(Lecturer::class)->ignore($user)],
            'staff_id' => ['required', 'string', 'max:20', Rule::unique(Lecturer::class)->ignore($user)],
        ]);

        $input = $request->all();

        $user->fill($input)->save();
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('lecturer')->user();

        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', Rules\Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
    public function updateLecturerPassword(Request $request, Lecturer $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($request->password);

        $user->fill($input)->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $data = Lecturer::find($request->id);

        $data->delete();
    }
}
