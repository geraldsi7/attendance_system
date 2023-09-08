<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentLoginRequest;
use App\Http\Requests\StudentProfileUpdateRequest;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

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

        $courses = $student->section->grade->course()
            ->with([
                'session' => function ($query) {
                    $query->where('status', '!=', 'Scheduled');
                }
            ])
            ->orderBy('title', 'asc')
            ->get();

        foreach ($courses as $course) {
            $attendance = collect();

            foreach ($course->session as $session) {
                $attendance = $attendance->merge(Attendance::where('student_id', $student->id)
                    ->where('session_id', $session->id)
                    ->get());      
            }
            
            $course['attendance'] = $attendance;
        }

        return Inertia::render('Student/Index', compact(
            'courses'
        ));
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

    public function sessions()
    {
        return Inertia::render('Student/Sessions');
    }

    public function fetchSessions()
    {
        $student = Auth::guard('student')->user();
        $sessions = $student->section->session()->with(['course', 'venue'])->latest()->get();
        
        foreach($sessions as $session){
            $session['starts_at'] = Carbon::parse($session->starts_at)->format('d/m/Y H:i');
            $session['ends_at'] = Carbon::parse($session->ends_at)->format('d/m/Y H:i');
            $presence = $session->attendance()->where('student_id', $student->id)->get();

            if($presence->count() == 0){
                $session['presence'] = false;
            }else{
                $session['presence'] = true;
            }
        }

        return response()->json([
            'row' => $sessions
        ]);
    }

    public function signSession(Request $request){
        $session = Session::find($request->id);

        $student = Auth::guard('student')->user();

        $attendance = new Attendance();
        $attendance->student_id = $student->id;
        $attendance->session_id = $session->id;
        $attendance->save();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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

    public function updatePassword(Request $request)
    {
        $user = Auth::guard('student')->user();

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
