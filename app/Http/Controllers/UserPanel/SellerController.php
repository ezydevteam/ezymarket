<?php

namespace App\Http\Controllers\UserPanel;

use App\Http\Controllers\Controller;
use App\Models\SellerLevel;
use App\Traits\HandlesValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class SellerController extends Controller
{
    use HandlesValidation;

    public function index(): View
    {
        return theme_view('userpanel.seller');
    }

    public function store(Request $request): JsonResponse
    {
        $rules = [];

        if (@settings('links')->seller_terms_link) {
            $rules['seller_terms'] = ['required'];
        }

        if (@settings('referral')->status && @settings('links')->referral_terms_link) {
            $rules['referral_terms'] = ['required'];
        }

        $validator = $this->validateRequestJson($request, $rules);

        if ($validator instanceof JsonResponse) {
            return $validator;
        }

        $level = SellerLevel::default()->with('badge')->first();

        if ($level) {
            $seller = authUser();

            $seller->level_id = $level->id;
            $seller->is_seller = true;
            $seller->update();

            // Refresh the user in the session to reflect the changes
            Auth::login($seller->fresh());

            if ($level->badge) {
                $seller->addBadge($level->badge);
            }

            return $this->successJson('Congratulations! You are now a Seller', [
                'redirect' => route('user.dashboard')
            ]);
        }

        return $this->errorJson('Failed to become a seller. Please contact support.');
    }
}
