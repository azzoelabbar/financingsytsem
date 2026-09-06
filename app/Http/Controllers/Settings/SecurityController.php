<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    public function edit(TwoFactorAuthenticationRequest $request): View
    {
        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'twoFactorEnabled' => false,
        ];

        if (Features::canManageTwoFactorAuthentication()) {
            $request->ensureStateIsValid();
            $props['twoFactorEnabled'] = $request->user()->hasEnabledTwoFactorAuthentication();
        }

        return view('settings.security', $props);
    }

    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->password,
        ]);

        return back()->with('status', __('تم تحديث كلمة المرور.'));
    }
}
