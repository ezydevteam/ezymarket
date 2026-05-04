<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Premium;

use App\Enums\{BadgeAlias, PremiumStatus};
use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\Premium\Premium;
use App\Traits\HandlesValidation;
use Illuminate\Http\{RedirectResponse, Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Subscriber Controller
 *
 * Manages premium members, including viewing, searching,
 * and performing actions like hold, unhold, and cancel.
 *
 * @package App\Http\Controllers\Admin\Premium
 */
class PremiumController extends Controller
{
    use HandlesValidation;

    /**
     * Display a listing of premium memberships with filters
     */
    public function index(): View
    {
        $counters = $this->getPremiumCounters();
        $query = Premium::query()->with('user');

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('member')) {
            $query->where('user_id', request('member'));
        }

        $premiums = $query->get();

        return view('admin.premium.members.index', compact('counters', 'premiums'));
    }

    /**
     * Put a premium membership on hold
     */
    public function hold(Premium $premium): RedirectResponse
    {
        if ($premium->isOnHold()) {
            return $this->warningBack('Premium membership is already on hold');
        }

        $premium->update(['status' => PremiumStatus::ON_HOLD]);

        return $this->successBack('Premium membership has been put on hold successfully');
    }

    /**
     * Remove hold status from a premium membership
     */
    public function unhold(Premium $premium): RedirectResponse
    {
        if (!$premium->isOnHold()) {
            return $this->warningBack('Premium membership is not on hold');
        }

        $premium->update(['status' => PremiumStatus::ACTIVE]);

        return $this->successBack('Premium membership has been activated successfully');
    }

    /**
     * Cancel and delete a premium membership
     */
    public function cancel(Request $request, Premium $premium): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $user = $premium->user;

            // Delete premium membership
            $premium->delete();

            // Remove premium badge if exists
            $this->removePremiumBadge($user);

            DB::commit();
            return $this->successRedirect(
                'admin.premium.members.index',
                'Premium membership has been cancelled successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorBack('Failed to cancel premium membership: ' . $e->getMessage());
        }
    }

    /**
     * Put multiple premium memberships on hold
     */
    public function bulkHold(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $premiums = Premium::whereIn('id', $ids)->get();

                $premiumsToHold = $premiums->filter(function ($premium) {
                    return $premium->status !== PremiumStatus::ON_HOLD;
                });

                if ($premiumsToHold->isEmpty()) {
                    throw new \Exception(translate('No active premium memberships found to put on hold'));
                }

                foreach ($premiumsToHold as $premium) {
                    $premium->update(['status' => PremiumStatus::ON_HOLD]);
                }

                return $premiumsToHold->count();
            },
            Premium::class,
            ':count premium membership(s) have been put on hold successfully',
            'Failed to put premium memberships on hold'
        );
    }

    /**
     * Resume multiple premium memberships from hold
     */
    public function bulkResume(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $premiums = Premium::whereIn('id', $ids)->get();

                $premiumsToResume = $premiums->filter(function ($premium) {
                    return $premium->status === PremiumStatus::ON_HOLD;
                });

                if ($premiumsToResume->isEmpty()) {
                    throw new \Exception(translate('No hold premium memberships found to resume'));
                }

                foreach ($premiumsToResume as $premium) {
                    $premium->update(['status' => PremiumStatus::ACTIVE]);
                }

                return $premiumsToResume->count();
            },
            Premium::class,
            ':count premium membership(s) have been resumed successfully',
            'Failed to resume premium memberships'
        );
    }

    /**
     * Delete multiple premium memberships
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleBulkAction(
            $request,
            function ($ids) {
                $premiums = Premium::with('user')->whereIn('id', $ids)->get();

                if ($premiums->isEmpty()) {
                    throw new \Exception(translate('No premium memberships found to delete'));
                }

                $count = 0;
                foreach ($premiums as $premium) {
                    $user = $premium->user;
                    $premium->delete();

                    // Remove premium badge if user has no other active premium memberships
                    if (!$user->premiums()->exists()) {
                        $this->removePremiumBadge($user);
                    }
                    $count++;
                }
                return $count;
            },
            Premium::class,
            ':count premium membership(s) have been deleted successfully',
            'Failed to delete premium memberships'
        );
    }

    /**
     * Get premium membership statistics counters with percentage changes.
     *
     * @return array<string, mixed>
     */
    private function getPremiumCounters(): array
    {
        // Current counts
        $counters['total_members'] = Premium::count();
        $counters['active_members'] = Premium::active()->count();
        $counters['on_hold_members'] = Premium::onHold()->count();
        $counters['expiring_soon'] = Premium::aboutToExpire()->count();

        // Previous week counts for percentage calculation (one week before current week)
        $lastWeekStart = now()->subWeek()->startOfWeek();

        $previousWeekTotal = Premium::where('created_at', '<', $lastWeekStart)->count();
        $previousWeekActive = Premium::active()->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekOnHold = Premium::onHold()->where('created_at', '<', $lastWeekStart)->count();
        $previousWeekExpiring = Premium::aboutToExpire()->where('created_at', '<', $lastWeekStart)->count();

        // Calculate percentages (comparing current with previous week)
        $counters['total_members_percent'] = $previousWeekTotal > 0
            ? round((($counters['total_members'] - $previousWeekTotal) / $previousWeekTotal) * 100)
            : ($counters['total_members'] > 0 ? 100 : 0);

        $counters['active_members_percent'] = $previousWeekActive > 0
            ? round((($counters['active_members'] - $previousWeekActive) / $previousWeekActive) * 100)
            : ($counters['active_members'] > 0 ? 100 : 0);

        $counters['on_hold_members_percent'] = $previousWeekOnHold > 0
            ? round((($counters['on_hold_members'] - $previousWeekOnHold) / $previousWeekOnHold) * 100)
            : ($counters['on_hold_members'] > 0 ? 100 : 0);

        $counters['expiring_soon_percent'] = $previousWeekExpiring > 0
            ? round((($counters['expiring_soon'] - $previousWeekExpiring) / $previousWeekExpiring) * 100)
            : ($counters['expiring_soon'] > 0 ? 100 : 0);

        return $counters;
    }

    /**
     * Remove premium membership badge from user
     */
    private function removePremiumBadge($user): void
    {
        $badge = Badge::where('alias', BadgeAlias::PREMIUM_MEMBERSHIP)->first();

        if ($badge) {
            $user->removeBadge($badge);
        }
    }
}
