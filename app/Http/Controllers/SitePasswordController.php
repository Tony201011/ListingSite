<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\SiteSetting;

class SitePasswordController extends Controller
{
    public function showForm()
    {
        return view('site-access.site-password');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $dbPassword = null;

        if (Schema::hasTable('site_settings')) {
            $setting = SiteSetting::query()->latest('updated_at')->first();

            if ($setting && $setting->site_password) {
                $dbPassword = $setting->site_password;
            }
        }

        $expected = $dbPassword ?? env('SITE_PASSWORD');

        if ($expected && hash_equals((string) $expected, (string) $request->password)) {
            $request->session()->regenerate();
            $request->session()->put('site_access', true);

            return redirect()->intended('/');
        }

        return back()->with('error', 'Wrong password');
    }
}
