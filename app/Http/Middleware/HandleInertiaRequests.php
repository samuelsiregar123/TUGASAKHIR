<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'                 => $request->user()->id,
                    'name'               => $request->user()->name,
                    'email'              => $request->user()->email,
                    'role'               => $request->user()->role,
                    'nama_instansi'      => $request->user()->nama_instansi,
                    'profile_photo_url'  => $request->user()->profile_photo_url,
                    'two_factor_enabled' => ! is_null($request->user()->two_factor_secret),
                ] : null,
            ],
            'flash' => [
                'success'           => fn () => $request->session()->get('success'),
                'error'             => fn () => $request->session()->get('error'),
                'generatedPassword' => fn () => $request->session()->get('generatedPassword'),
            ],
        ];
    }
}
