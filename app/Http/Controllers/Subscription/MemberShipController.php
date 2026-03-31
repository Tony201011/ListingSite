<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MemberShipController extends Controller
{
    public function membership(): View
    {
        return view('subscription.membership');
    }
}
