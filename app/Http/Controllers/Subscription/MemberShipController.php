<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\GetSubscriptionPlans;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MemberShipController extends Controller
{
    public function __construct(
        private GetSubscriptionPlans $getSubscriptionPlans
    ) {}

    public function membership(): View
    {
        return view('subscription.membership', $this->getSubscriptionPlans->execute());
    }
}
