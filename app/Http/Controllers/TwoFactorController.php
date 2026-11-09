<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function showSetup()
    {
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.edit');
        }

        $secret = session('2fa_setup_secret') ?: $this->google2fa->generateSecretKey();
        session(['2fa_setup_secret' => $secret]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $qrCodeSvg = $this->generateQrCodeSvg($qrCodeUrl);

        return view('auth.two-factor.setup', compact('secret', 'qrCodeSvg'));
    }

    public function enable(Request $request)
    {
        $request->validate(['code' => 'required|string|digits:6']);

        $user   = Auth::user();
        $secret = session('2fa_setup_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'Сесія налаштування завершилася. Спробуйте знову.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Невірний код. Перевірте додаток і спробуйте знову.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_confirmed_at'   => now(),
        ]);

        session()->forget('2fa_setup_secret');

        return redirect()->route('profile.edit')
            ->with('recovery_codes', $recoveryCodes)
            ->with('success', '2FA успішно увімкнено!');
    }

    public function manage()
    {
        $user = Auth::user();
        $recoveryCodes = session('recovery_codes', []);

        return view('auth.two-factor.manage', compact('user', 'recoveryCodes'));
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required']);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Невірний пароль.']);
        }

        Auth::user()->update([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        return redirect()->route('profile.edit')
            ->with('success', '2FA вимкнено.');
    }

    public function regenerateCodes(Request $request)
    {
        $request->validate(['password' => 'required']);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Невірний пароль.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        Auth::user()->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return redirect()->route('profile.edit')
            ->with('recovery_codes', $recoveryCodes)
            ->with('success', 'Коди відновлення оновлено.');
    }

    public function showVerify()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor.verify');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = session('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::findOrFail($userId);

        $secret = decrypt($user->two_factor_secret);
        $code   = $request->input('code');

        $valid = $this->google2fa->verifyKey($secret, $code);

        if (!$valid) {
            $valid = $this->checkRecoveryCode($user, $code);
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'Невірний код. Спробуйте ще раз.']);
        }

        session()->forget('2fa_user_id');

        Auth::loginUsingId($userId);
        session(['2fa_verified' => true]);

        return redirect()->intended(route('dashboard'));
    }

    private function checkRecoveryCode(\App\Models\User $user, string $code): bool
    {
        $codes = $user->twoFactorRecoveryCodesArray();
        $index = array_search(trim($code), $codes);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
        ]);

        return true;
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }

    private function generateQrCodeSvg(string $url): string
    {
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        return $writer->writeString($url);
    }
}
