<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function zoom()
    {
        $settings = Setting::where('group', 'zoom')->pluck('value', 'key');
        return view('settings.zoom', compact('settings'));
    }

    public function updateZoom(Request $request)
    {
        $request->validate([
            'zoom_account_id'    => 'nullable|string|max:255',
            'zoom_client_id'     => 'nullable|string|max:255',
            'zoom_client_secret' => 'nullable|string|max:255',
            'zoom_webhook_token' => 'nullable|string|max:255',
            'zoom_default_host'  => 'nullable|email|max:255',
        ]);

        $keys = [
            'zoom_account_id', 'zoom_client_id', 'zoom_client_secret',
            'zoom_webhook_token', 'zoom_default_host',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key), 'zoom');
        }

        return redirect()->route('settings.zoom')
            ->with('success', 'Налаштування Zoom збережено.');
    }

    public function wayforpay()
    {
        $settings = Setting::where('group', 'wayforpay')->pluck('value', 'key');
        return view('settings.wayforpay', compact('settings'));
    }

    public function updateWayforpay(Request $request)
    {
        $request->validate([
            'wfp_merchant_account' => 'nullable|string|max:255',
            'wfp_merchant_secret'  => 'nullable|string|max:255',
            'wfp_merchant_domain'  => 'nullable|string|max:255',
        ]);

        foreach (['wfp_merchant_account', 'wfp_merchant_secret', 'wfp_merchant_domain'] as $key) {
            Setting::set($key, $request->input($key), 'wayforpay');
        }

        return redirect()->route('settings.wayforpay')
            ->with('success', 'Налаштування WayForPay збережено.');
    }
}
