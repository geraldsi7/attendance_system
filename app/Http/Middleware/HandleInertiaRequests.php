<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed[]
     */
    public function share(Request $request)
    {
        if (Auth::guard('lecturer')->user()) {
            $user = Auth('lecturer')->user();
        } else if(Auth::guard('student')->user()) {
           $user = Auth('student')->user();
        } else if(Auth::guard('admin')->user()) {
            $user = Auth('admin')->user();
         } else{
            $user = null;
        }
        
        if($request->user()){
        $auth_user = User::find($request->user()->id);
        } 

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
                 ],
            'flash' => [
                    'error' => fn () => $request->session()->get('error'),
                    'success' => fn () => $request->session()->get('success'),
                ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
