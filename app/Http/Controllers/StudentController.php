<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentLoginRequest;
use App\Http\Requests\StudentProfileUpdateRequest;
use App\Imports\StudentImport;
use App\Models\Attendance;
use App\Models\Classe;
use App\Models\Course;
use App\Models\Department;
use App\Models\DepartmentYear;
use App\Models\DepartmentYearSection;
use App\Models\Grade;
use App\Models\Level;
use App\Models\Section;
use App\Models\Session;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Yajra\DataTables\Facades\DataTables;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

use function PHPUnit\Framework\isNull;

class StudentController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $student = Auth::guard('student')->user();
        $student->load('classe');

        $courses = $student->classe->course()
            ->with([
                'session' => function ($query) {
                    $query->where('status', '!=', 'Scheduled');
                },
                'session.attendance' => function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                }
            ])
            ->orderBy('title')
            ->get();

        foreach ($courses as $course) {
            // Count the total number of attendance records for the student across all sessions of this course
            $course->attendance_count = $course->session->sum(function ($session) {
                return $session->attendance->count();
            });
        }

        return Inertia::render('Student/Index', compact('courses'));
    }

    public function showLoginForm()
    {
        return Inertia::render('Auth/Student/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status')
        ]);
    }

    public function login(StudentLoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();

        return redirect()->route('home');
    }

    public function showForgotPasswordForm()
    {
        return Inertia::render('Auth/Student/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function requestPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email', 
        ]);

        $status = Password::broker('students')->sendResetLink(
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
        return Inertia::render('Auth/Student/ResetPassword', [
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
        $status = Password::broker('students')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($student) use ($request) {
                $student->forceFill([
                    'password' => Hash::make($request->password)
                ])->save();

                event(new PasswordReset($student));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('student.login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    public function sessions()
    {
        return Inertia::render('Student/Sessions');
    }

    public function fetchSessions(Request $request)
    {
        $student = Auth::guard('student')->user();
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $data = $student->classe->session()->with([
            'course',
            'lecturer',
            'venue',
            'attendance' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
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

            $session->attended = $session->attendance->isNotEmpty();
        }

        return response()->json([
            'row' => $data
        ]);
    }

    public function signSession(Request $request)
    {
        $student = Auth::guard('student')->user();

        $input = $request->all();
        $input['session_id'] = $input['id'];
        $attendance = new Attendance($input);

        $student->attendance()->save($attendance);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showStudent()
    {
        $departments = Department::orderBy('title')->get();
        return Inertia::render('Setup/Student', compact('departments'));
    }

    public function fetch()
    {

        $data = Student::all();

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
            ->editColumn('department', function ($row) {
                return $row->classe->department->title;
            })
            ->editColumn('level', function ($row) {
                return $row->classe->level->title;
            })
            ->editColumn('section_id', function ($row) {
                $sectionCount = Classe::where('department_id', $row->classe->department->id)
                    ->where('level_id', $row->classe->level->id)
                    ->distinct('section_id')
                    ->count('section_id');

                return $sectionCount > 1 ? $row->classe->section->title : '- -';
            })
            ->rawColumns(['department', 'level', 'section_id', 'action'])
            ->make(true);
    }

    public function fetchLevel(Request $request)
    {
        $data = Department::find($request->id);

        // Retrieve classes for the department, group by level_id, and order by level title
        $levels = $data->classe()
            ->select('level_id')
            ->distinct()
            ->get()
            ->pluck('level.id'); // Retrieve the levels

        $levels = Level::whereIn('id', $levels)->orderBy('title')->get();

        return response()->json([
            'row'   => $levels
        ]);
    }

    public function fetchSection(Request $request)
    {
        // Retrieve classes for the department, group by level_id, and order by level title
        $sections = Classe::where('department_id', $request->department_id)
            ->where('level_id', $request->level_id)
            ->select('section_id')
            ->distinct()
            ->get()
            ->pluck('section.id'); // Retrieve the sections

        $sections = Section::whereIn('id', $sections)->orderBy('title')->get();

        return response()->json([
            'row'   => $sections
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:100',
                'email' => 'required|string|lowercase|email|max:100|unique:' . Student::class,
                'student_id' => 'required|string|max:50|unique:' . Student::class,
                'index_number' => 'required|string|max:50|unique:' . Student::class,
                'department_id' => 'required',
                'level_id' => 'required',
                'section_id' => [
                    Rule::requiredIf(function () use ($request) {
                        // Check if multiple sections exist for the given department and level
                        return Classe::where('department_id', $request->department_id)
                            ->where('level_id', $request->level_id)
                            ->distinct()
                            ->count('section_id') > 1;
                    })
                ],
                //'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'department_id.required' => 'The department field is required.',
                'level_id.required' => 'The level field is required.',
                'section_id.required' => 'The section field is required.'
            ]
        );

        $input = $request->all();

        // Determine the class ID based on department, level, and section
        $classeQuery = Classe::where('department_id', $input['department_id'])
            ->where('level_id', $input['level_id']);

        // Cache the class query result for reuse
        $classe = $classeQuery->when(
            $classeQuery->distinct()->count('section_id') > 1, // Check for multiple sections
            function ($query) use ($input) {
                return $query->where('section_id', $input['section_id']);
            }
        )->first();

        $input['password'] = Hash::make($input['student_id']);
        $input['email_verified_at'] = Carbon::now();

        $classe->student()->create($input);
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
            'C' => 'student_id',
            'D' => 'index_number',
            'E' => 'department',
            'F' => 'level',
            'G' => 'section',
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
            Excel::import(new StudentImport, $file);
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
        $user = Auth::guard('student')->user();

        return Inertia::render('Student/Profile/Edit', [
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
    public function update(StudentProfileUpdateRequest $request)
    {
        $user = Auth::guard('student')->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('student.profile.edit');
    }

    public function editProfile(Request $request)
    {
        $data = Student::find($request->id);
        $data['department_id'] = $data->classe->department->id;
        $data['level_id'] = $data->classe->level->id;
        $data['section_id'] = $data->classe->section->id;

        // Retrieve classes for the department, group by level_id, and order by level title
        $levels = $data->classe->department->classe()
            ->select('level_id')
            ->distinct()
            ->get()
            ->pluck('level.id'); // Retrieve the levels

        $levels = Level::whereIn('id', $levels)->orderBy('title')->get();

        // Retrieve classes for the department, group by level_id, and order by level title
        $sections = Classe::where('department_id', $data['department_id'])
            ->where('level_id', $data['level_id'])
            ->select('section_id')
            ->distinct()
            ->get()
            ->pluck('section.id'); // Retrieve the sections

        $sections = Section::whereIn('id', $sections)->orderBy('title')->get();

        return response()->json([
            'row'   => $data,
            'levels'   => $levels,
            'sections' => $sections
        ]);
    }

    public function updateProfile(Request $request, Student $user)
    {
        $request->validate(
            [
                'name' => 'required|string|max:100',
                'email' => ['required', 'string', 'lowercase', 'email', 'max:100', Rule::unique(Student::class)->ignore($user)],
                'student_id' => ['required', 'string', 'max:50', Rule::unique(Student::class)->ignore($user)],
                'index_number' => ['required', 'string', 'max:50', Rule::unique(Student::class)->ignore($user)],
                'department_id' => 'required',
                'level_id' => 'required',
                'section_id' => [
                    Rule::requiredIf(function () use ($request) {
                        // Check if multiple sections exist for the given department and level
                        return Classe::where('department_id', $request->department_id)
                            ->where('level_id', $request->level_id)
                            ->distinct()
                            ->count('section_id') > 1;
                    })
                ],
                //'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ],
            [
                'department_id.required' => 'The department field is required.',
                'level_id.required' => 'The level field is required.',
                'section_id.required' => 'The section field is required.'
            ]
        );

        $input = $request->all();

        // Determine the class ID based on department, level, and section
        $classeQuery = Classe::where('department_id', $input['department_id'])
            ->where('level_id', $input['level_id']);

        // Cache the class query result for reuse
        $classe = $classeQuery->when(
            $classeQuery->distinct()->count('section_id') > 1, // Check for multiple sections
            function ($query) use ($input) {
                return $query->where('section_id', $input['section_id']);
            }
        )->first();

        $input['classe_id'] = $classe->id;

        $user->fill($input)->save();
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('student')->user();

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

    public function updateStudentPassword(Request $request, Student $user)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($request->password);

        $user->fill($input)->save();
    }

    public function destroy(Request $request)
    {
        $data = Student::find($request->id);

        $data->delete();
    }
}
