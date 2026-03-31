<?php

namespace App\Http\Controllers\SiteAccess;

use App\Actions\SiteAccess\VerifySitePassword;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SitePasswordController extends Controller
{
    public function __construct(
        private VerifySitePassword $verifySitePassword
    ) {
    }

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
            $request->session()->regenerate();
            $request->session()->put('site_access', true);

            return redirect()->intended('/');
        }

        return back()->with('error', 'Wrong password');
    }
}
