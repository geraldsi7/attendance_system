<?php

namespace App\Http\Controllers;

use App\Http\Requests\LecturerLoginRequest;
use App\Http\Requests\LecturerProfileUpdateRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Grade;
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
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Yajra\DataTables\Facades\DataTables;
use App\Providers\RouteServiceProvider;

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
        $courses  = $lecturer->course;

        $totalStudents = 0;
        $gradeChartLabels = [];
        $gradeChartData = [];
        $attendanceReportChartLabels = [];
        $attendanceReportChartData = [];
        $grades = collect();
        $sessions = collect();
        $studentsPresentAtSession = 0;

        foreach ($courses as $course) {
            $pivot_grades = $course->grade()->orderBy('title', 'asc')->get();

            foreach ($pivot_grades as $grade) {
                if (!$grades->contains('id', $grade->id)) {
                    $grades->push($grade);
                }
            }

            $sessions = $sessions->merge($course->session()
                ->where('status', '!=', 'Scheduled')
                ->get());
        }

        $sessions_report = $sessions->take(-7);

        // dd($sessions_report[0]);
        $totalSessions = $sessions->count();

        foreach ($grades as $key => $grade) {
            $gradeChartLabels[$key] = $grade->iso . ' ' . $grade->year;

            $students = 0;
            foreach ($grade->section as $section) {
                $students += $section->student->count();
            }

            $totalStudents += $students;

            $gradeChartData[$key] = $students;
        }

        foreach ($sessions->take(-7) as $session) {

            $attendanceReportChartLabels[] = 'S' . $session->id . ' ' . $session->course->course_code . ' [' . $session->section->grade->iso . ' ' . $session->section->grade->year . ']';
            $attendanceReportChartData[] = $session->attendance->count();
        }

        $number_of_students = number_format($totalStudents);
        $number_of_sessions = number_format($totalSessions);
        $number_of_courses = number_format($courses->count());
        return Inertia::render('Lecturer/Index', compact(
            'number_of_courses',
            'number_of_students',
            'number_of_sessions',
            'gradeChartLabels',
            'gradeChartData',
            'attendanceReportChartLabels',
            'attendanceReportChartData',
        ));
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

    public function courses()
    {
        $lecturer = Auth::guard('lecturer')->user();
        $courses = Course::where('lecturer_id', $lecturer->id)->with('grade')->get();

        return Inertia::render('Lecturer/Courses', compact('courses'));
    }

    public function students()
    {
        $lecturer = Auth::guard('lecturer')->user();
        $courses = $lecturer->course;
        $grades = collect();

        foreach ($courses as $course) {
            $pivot_grades = $course->grade()->orderBy('title', 'asc')->get();

            foreach ($pivot_grades as $grade) {
                if (!$grades->contains('id', $grade->id)) {
                    $grades->push($grade);
                }
            }
        }
       
        return Inertia::render('Lecturer/Students', compact('grades'));
    }

    public function fetchStudents(Request $request)
    {
        $class = Grade::find($request->class_id);
        $sections = $class->section;

        $students = collect();
        foreach ($sections as $section) {
            $students = $students->merge($section->student()->orderBy('name', 'asc')->get());
        }

        return DataTables::of($students)
            ->addIndexColumn()
            ->editColumn('section', function ($row) {
                if ($row->section->grade->section->count() == 1) {
                    return '- -';
                }
                return $row->section->title;
            })
            ->make(true);
    }


    public function sessions()
    {
        $minDateTime = date('Y-m-d\TH:i');
        $lecturer = Auth::guard('lecturer')->user();
        $courses = Course::where('lecturer_id', $lecturer->id)->orderBy('title', 'asc')->get();
        $venues = Venue::orderBy('title', 'asc')->get();
        $classes = collect();

        foreach ($courses as $course) {
            $classes = $classes->merge($course->grade()->orderBy('title', 'asc')->get());
        }

        return Inertia::render('Lecturer/Sessions', compact('courses', 'classes', 'venues', 'minDateTime'));
    }

    public function fetchSessions(Request $request)
    {
        $lecturer = Auth::guard('lecturer')->user();
        $courses = $lecturer->course;
        $data = collect();

        foreach ($courses as $course) {
            $data = $data->merge($course->session()->latest()->get());
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                
                $linkClass = 'block px-4 py-2 w-full text-sm text-gray-900 disabled:cursor-not-allowed disabled:opacity-25 hover:text-gray-50 hover:bg-gray-100 text-left';

                $action = '
    <div class="relative inline-block text-left">
        <button type="button" class="rowDropdown">
            <span class="material-symbols-outlined" dropdown-log="' . $row->id . '">
                more_vert
            </span>
        </button>
       
        <div id="dropdown-menu' . $row->id . '" class="hidden dropdown-menu origin-top-right right-0 absolute z-50 mt-2 w-40 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
            <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                <button data-log-id="' . $row->id . '"' . ($row->status == 'Scheduled' ? '' : ' disabled') . ' class="edit-session ' . $linkClass . '">Edit</button>
                <button data-log-id="' . $row->id . '"' . ($row->status == 'Scheduled' ? '' : ' disabled') . ' class="publish-session ' . $linkClass . '">Publish</button>
                <button data-log-id="' . $row->id . '" class="view-session ' . $linkClass . '">View</button>';

                if ($row->status == 'Running') {
                    $action .= '<button data-log-id="' . $row->id . '" class="end-session block px-4 py-2 w-full text-sm text-red-600 hover:bg-gray-100 hover:text-red-900 text-left">End</button>';
                }

                $action .= '<button data-log-id="' . $row->id . '" class="delete-session block px-4 py-2 w-full text-sm text-red-600 hover:bg-gray-100 hover:text-red-900 text-left">Delete</button>
            </div>
        </div>
    </div>';
                /*'
                
                    <button data-log-id="' . $row->id . '" class="editPurchases inline-block mt-2 px-6 py-2.5 bg-purple-600 text-white font-bold text-sm leading-tight capitalize rounded shadow-md hover:bg-purple-700 hover:shadow-lg focus:bg-purple-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-purple-800 active:shadow-lg transition duration-150 ease-in-out">Edit</button>
                    <a href="' . asset('storage/images/receipts/' . $row->receipt) . '" target="_blank" class="inline-block mt-2 px-6 py-2.5 bg-purple-600 text-white font-bold text-sm leading-tight capitalize rounded shadow-md hover:bg-purple-700 hover:shadow-lg focus:bg-purple-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-purple-800 active:shadow-lg transition duration-150 ease-in-out">Publish</a>
                    <a href="' . $url . '" class="inline-block mt-2 px-6 py-2.5 bg-purple-600 text-white font-bold text-sm leading-tight capitalize rounded shadow-md hover:bg-purple-700 hover:shadow-lg focus:bg-purple-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-purple-800 active:shadow-lg transition duration-150 ease-in-out">Download</a>
                    <button data-log-id="' . $row->id . '"  class="deletePurchases inline-block mt-2 px-6 py-2.5 bg-red-600 text-white font-bold text-sm leading-tight capitalize rounded shadow-md hover:bg-red-700 hover:shadow-lg focus:bg-red-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-red-800 active:shadow-lg transition duration-150 ease-in-out">Delete</a>';
               
               */
                return $action;
            })
            ->editColumn('course', function ($row) {
                return $row->course->title;
            })
            ->editColumn('class', function ($row) {
                return $row->section->grade->title . ' ' . $row->section->grade->year . ' ' . '[' . $row->section->grade->iso . ' ' . $row->section->grade->year . ']';
            })
            ->editColumn('section', function ($row) {
                if ($row->section->grade->section->count() == 1) {
                    return '- -';
                }
                return $row->section->title;
            })
            ->editColumn('created_at', function ($row) {
                return '<span class="hidden">' . strtotime($row->created_at) . '</span>' . Carbon::parse($row->created_at)->format('d/m/Y H:i');
            })
            ->editColumn('starts_at', function ($row) {
                return '<span class="hidden">' . strtotime($row->starts_at) . '</span>' . Carbon::parse($row->starts_at)->format('d/m/Y H:i');
            })
            ->editColumn('ends_at', function ($row) {
                return '<span class="hidden">' . strtotime($row->ends_at) . '</span>' . Carbon::parse($row->ends_at)->format('d/m/Y H:i');
            })
            ->editColumn('venue', function ($row) {
                return $row->venue->title;
            })
            ->editColumn('status', function ($row) {
                return  '<span class="text-xs inline-block py-1 px-2.5 leading-none text-center whitespace-nowrap font-bold bg-gray-800 text-white rounded">' . $row->status . '</span>';
            })
            ->rawColumns(['created_at', 'course', 'class', 'section', 'starts_at', 'ends_at', 'venue', 'status', 'action'])
            ->make(true);
    }

    public function fetchSessionsClasses(Request $request)
    {
        $course = Course::find($request->id);
        $grade = $course->grade;
        $sections = Section::where('grade_id', $grade->id)->orderBy('title', 'asc')->get();

        return response()->json([
            'class' => $grade->title . ' ' . $grade->year . ' [' . $grade->iso . ' ' . $grade->year . ']',
            'sections' => $sections
        ]);
    }

    public function storeSession(Request $request)
    {
        if (!is_null($request->course_id)) {
            $course = Course::find($request->course_id);
            $grade = $course->grade;
            $sectionsCount = Section::where('grade_id', $grade->id)->count();
            if ($sectionsCount > 1) {
                $request['count'] = 1;
            }
        } else {
            $request['count'] = null;
        }
        $request->validate(
            [
                'course_id'  => 'required',
                'section_id' => 'required_if:count,' . 1,
                'venue_id'   => 'required',
                'starts_at'  => 'required|before:ends_at',
                'ends_at'    => 'required|after:starts_at',
            ],
            [
                'course_id.required'  => 'The course field is required.',
                'section_id.required_if'  => 'The section field is required.',
                'venue_id.required' => 'The venue field is required.',
            ]
        );


        $data = $request->all();

        if ($request['count'] != 1) {
            $course = Course::find($data['course_id']);
            $grade = $course->grade;
            $section = Section::where('grade_id', $grade->id)->first();
            $data['section_id'] = $section->id;
        }

        $currentTime = time();

        $startTimestamp = strtotime($request['starts_at']);
        $endTimestamp = strtotime($request['ends_at']);

        if ($currentTime < $startTimestamp) {
            // The session has not started yet
            $data['status'] = 'Scheduled';
        } elseif ($currentTime >= $startTimestamp && $currentTime <= $endTimestamp) {
            // The session is currently running
            $data['status'] = 'Running';
        } else {
            // The session has already ended
            $data['status'] = 'Ended';
        }

        Session::create($data);

        return Redirect::back();
    }

    public function editSession(Request $request)
    {
        $session = Session::find($request->id);
        $session['class'] = $session->section->grade->title . ' ' . $session->section->grade->year . ' [' . $session->section->grade->iso . ' ' . $session->section->grade->year . ']';
        if (count($session->section->grade->section) == 1) {
            $session['section_id'] = '';
        }

        $sections = $session->section->grade->section;

        return response()->json([
            'row' => $session,
            'sections' => $sections
        ]);
    }


    public function updateSession(Request $request, Session $session)
    {
        if (!is_null($request->course_id)) {
            $course = Course::find($request->course_id);
            $grade = $course->grade;
            $sectionsCount = Section::where('grade_id', $grade->id)->count();
            if ($sectionsCount > 1) {
                $request['count'] = 1;
            }
        } else {
            $request['count'] = null;
        }
        $request->validate(
            [
                'course_id'  => 'required',
                'section_id' => 'required_if:count,' . 1,
                'venue_id'   => 'required',
                'starts_at'  => 'required|before:ends_at',
                'ends_at'    => 'required|after:starts_at',
            ],
            [
                'course_id.required'  => 'The course field is required.',
                'section_id.required_if'  => 'The section field is required.',
                'venue_id.required' => 'The venue field is required.',
            ]
        );


        $data = $request->all();

        if ($request['count'] != 1) {
            $course = Course::find($data['course_id']);
            $grade = $course->grade;
            $section = Section::where('grade_id', $grade->id)->first();
            $data['section_id'] = $section->id;
        }

        $currentTime = time();

        $startTimestamp = strtotime($request['starts_at']);
        $endTimestamp = strtotime($request['ends_at']);

        if ($currentTime < $startTimestamp) {
            // The session has not started yet
            $data['status'] = 'Scheduled';
        } elseif ($currentTime >= $startTimestamp && $currentTime <= $endTimestamp) {
            // The session is currently running
            $data['status'] = 'Running';
        } else {
            // The session has already ended
            $data['status'] = 'Ended';
        }

        $session->fill($data)->save();

        return Redirect::back();
    }

    public function publishSession(Request $request)
    {
        $data = Session::find($request->id);

        $data->update([
            'status' => 'Running'
        ]);
    }


    public function endSession(Request $request)
    {
        $data = Session::find($request->id);

        $data->update([
            'status' => 'Ended'
        ]);
    }

    public function showSessionAttendance(Request $request)
    {
        $session = Session::find($request->id);
        $section = Section::find($session->section->id);
        $totalStudents = $section->student->count();

        //dd($section);

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

    public function fetchSessionAttendance(Request $request)
    {
        $session = Session::find($request->id);
        $section = Section::find($session->section->id);

        $attendance = $session->attendance->pluck('student_id')->toArray();

        if ($request->status == 'present') {
            $data = Student::where('section_id', $section->id)
                ->where('id', $attendance)
                ->orderBy('student_id', 'asc')
                ->get();
        } else {
            $data = Student::where('section_id', $section->id)
                ->whereNotIn('id', $attendance)
                ->orderBy('student_id', 'asc')
                ->get();
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }


    public function destroySession(Request $request)
    {
        $session = Session::find($request->id);
        // dd($data);
        $attendance = Attendance::where('session_id', $session->id)->delete();

        $session->delete();
    }
    public function create()
    {
        //
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('lecturer')->user();

        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back();
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
