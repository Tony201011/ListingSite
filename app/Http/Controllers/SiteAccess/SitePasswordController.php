<?php

namespace App\Http\Controllers\SiteAccess;

use App\Actions\SiteAccess\VerifySitePassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SitePasswordController extends Controller
{
    public function __construct(
        private VerifySitePassword $verifySitePassword
    ) {}

    public function showForm()
    {
        return view('site-access.site-password');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if ($this->verifySitePassword->execute($request->password)) {
            $siteAccessTabToken = (string) Str::uuid();

            $request->session()->regenerate();
            $request->session()->put('site_access', true);
            $request->session()->put('site_access_password_fingerprint', $this->verifySitePassword->getPasswordFingerprint());
            $request->session()->put('site_access_tab_token', $siteAccessTabToken);
            $request->session()->flash('site_access_tab_bootstrap', true);

            return redirect()->intended('/');
        }

        $request->session()->forget(['site_access_password_fingerprint', 'site_access_tab_token', 'site_access_tab_bootstrap']);

        return back()->with('error', 'Wrong password');
    }
}
