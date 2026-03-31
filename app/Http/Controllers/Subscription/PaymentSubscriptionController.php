<?php

namespace App\Http\Controllers\Subscription;

use App\Actions\Subscription\GetSubscriptionPlans;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PaymentSubscriptionController extends Controller
{
    public function __construct(
        private GetSubscriptionPlans $getSubscriptionPlans
    ) {
    }

    public function index(): View
    {
        return view(
            'subscription.payment-subscription',
            $this->getSubscriptionPlans->execute()
        );
    }
}
