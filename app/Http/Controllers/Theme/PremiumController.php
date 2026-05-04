<?php

namespace App\Http\Controllers\Theme;

use App\Http\Controllers\Controller;
use App\Enums\BadgeAlias;
use App\Enums\TransactionType;
use App\Models\Badge;
use App\Models\Premium\PremiumPlan;
use App\Models\Financial\Transaction;
use App\Traits\HandlesValidation;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class PremiumController extends Controller
{
    use HandlesValidation;

    public function index(): View
    {
        $countPremiumPlans = PremiumPlan::active()->count();

        $weeklyPremiumPlans = PremiumPlan::weekly()->active()->get();
        $monthlyPremiumPlans = PremiumPlan::monthly()->active()->get();
        $yearlyPremiumPlans = PremiumPlan::yearly()->active()->get();
        $lifetimePremiumPlans = PremiumPlan::lifetime()->active()->get();

        return theme_view('premium.plans', [
            'countPremiumPlans' => $countPremiumPlans,
            'weeklyPremiumPlans' => $weeklyPremiumPlans,
            'monthlyPremiumPlans' => $monthlyPremiumPlans,
            'yearlyPremiumPlans' => $yearlyPremiumPlans,
            'lifetimePremiumPlans' => $lifetimePremiumPlans,
        ]);
    }

    public function subscribe(Request $request, $id): RedirectResponse
    {
        $premiumPlan = PremiumPlan::where('id', $id)->active()->firstOrFail();

        $user = authUser();

        try {
            $premium = $user->premium;

            if ($premium) {
                if ($premium->plan->isLifetime()) {
                    return $this->errorBack('You are in a lifetime premium membership it cannot be renewed');
                }

                if ($premium->plan->id == $premiumPlan->id) {
                    if (!$premium->isAboutToExpire() && !$premium->isExpired()) {
                        return $this->errorBack('You have joined this premium plan already');
                    }

                    if ($premium->plan->isFree()) {
                        if ($premium->isExpired()) {
                            return $this->errorBack('Your free premium plan has already expired and it cannot be renewed');
                        }
                        return back();
                    }
                } else {
                    if ($premiumPlan->isFree()) {
                        return $this->errorBack('You are not eligible for the free premium plan');
                    }
                }
            }

            if ($premiumPlan->isFree() && $user->hadPremiumMembership()) {
                return $this->errorBack('You are not eligible for the free premium plan');
            }

            if ($premiumPlan->isFree()) {
                $premium = self::handlePremium($user, $premiumPlan);
                if ($premium) {
                    return redirect()->route('user.settings.premium');
                }
            }

            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $premiumPlan->price;
            $transaction->total = $premiumPlan->price;
            $transaction->type = TransactionType::PREMIUM->value;
            $transaction->plan_id = $premiumPlan->id;
            $transaction->save();

            return redirect()->route('checkout.index', hash_encode($transaction->id));
        } catch (Exception $e) {
            return $this->errorBack($e->getMessage());
        }
    }

    public static function handlePremium($user, $premiumPlan)
    {
        $premium = $user->premium;

        $expiryDate = null;

        if (!$premiumPlan->isLifetime()) {
            if ($premium) {
                if ($premiumPlan->id == $premium->plan->id) {
                    $expiryDate = $premium->isExpired()
                        ? Carbon::now()->addDays($premiumPlan->interval_days)
                        : Carbon::parse($premium->expiry_at)->addDays($premiumPlan->interval_days);
                } else {
                    $expiryDate = Carbon::now()->addDays($premiumPlan->interval_days);
                }
            } else {
                $expiryDate = Carbon::now()->addDays($premiumPlan->interval_days);
            }
        }

        $premium = $user->premium()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $premiumPlan->id,
                'total_downloads' => 0,
                'expiry_at' => $expiryDate,
                'last_notification_at' => null,
            ]
        );

        $user->had_premium = true;
        $user->update();

        $badge = Badge::where('alias', BadgeAlias::PREMIUM_MEMBERSHIP)->first();
        if ($badge) {
            $user->addBadge($badge);
        }

        return $premium;
    }
}
