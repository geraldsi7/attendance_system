<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\AdminProfileUpdateRequest;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Classe;
use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Session;
use App\Models\Student;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;


use function PHPUnit\Framework\isNull;

class AdminController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admin = Auth::guard('admin')->user();
        $admins = Admin::all();
        $number_of_admins = number_format($admins->count());
        $lecturers = Lecturer::all();
        $number_of_lecturers = number_format($lecturers->count());
        $students = Student::all();
        $number_of_students = number_format($students->count());
        $classes = Classe::all();
        $number_of_classes = number_format($classes->count());
        $courses = Course::all();
        $number_of_courses = number_format($courses->count());
        $venues = Venue::all();
        $number_of_venues = number_format($venues->count());
        $sessions = Session::all();
        $number_of_sessions = number_format($sessions->count());

        return Inertia::render('Admin/Index', compact(
            'number_of_admins',
            'number_of_lecturers',
            'number_of_students',
            'number_of_classes',
            'number_of_courses',
            'number_of_venues',
            'number_of_sessions',
        ));
    }

    public function showLoginForm()
    {
        return Inertia::render('Auth/Admin/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status')
        ]);
    }

    public function login(AdminLoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        return redirect()->route('home');
    }

    public function showForgotPasswordForm()
    {
        return Inertia::render('Auth/Admin/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    public function requestPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email', 
        ]);

        $status = Password::broker('admins')->sendResetLink(
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
        return Inertia::render('Auth/Admin/ResetPassword', [
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
        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin) use ($request) {
                $admin->forceFill([
                    'password' => Hash::make($request->password)
                ])->save();

                event(new PasswordReset($admin));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('admin.login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }  

    public function showAdmin()
    {
        return Inertia::render('Setup/Admin');
    }

    public function fetch()
    {

        $data = Admin::get();

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
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|lowercase|email|max:100|unique:' . Admin::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $input = $request->all();

        $input['password'] = Hash::make($request->password);
        $input['email_verified_at'] = Carbon::now();

        Admin::create($input);
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
        $user = Auth::guard('admin')->user();

        return Inertia::render('Admin/Profile/Edit', [
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
    public function update(AdminProfileUpdateRequest $request)
    {
        $user = Auth::guard('admin')->user();

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('admin.profile.edit');
    }

    public function editProfile(Request $request)
    {
        $data = Admin::find($request->id);

        return response()->json([
            'row'   => $data
        ]);
    }

    public function updateProfile(Request $request, Admin $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:100', Rule::unique(Admin::class)->ignore($user)],
        ]);

        $input = $request->all();

        $user->fill($input)->save();
    }
    public function updatePassword(Request $request)
    {
        $user = Auth::guard('admin')->user();

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

    public function updateAdminPassword(Request $request, Admin $user)
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
    public function destroy(Request $request){
        $data = Admin::find($request->id);

        $data->delete();
    }
}
