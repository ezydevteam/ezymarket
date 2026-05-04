<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Ably\AblyRest;

class AblyAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $ably = new AblyRest(env('ABLY_KEY'));

            $capabilities = [
                "private:user-{$user->username}" => ['subscribe'],
                "user-{$user->id}" => ['subscribe', 'publish'],
                "conversation:*" => ['subscribe', 'publish'],
                "presence-users" => ['subscribe', 'presence']
            ];

            $tokenRequest = $ably->auth->createTokenRequest([
                'clientId' => (string) $user->id,
                'capability' => json_encode($capabilities),
                'ttl' => 7200000 // 2 hours
            ]);

            return response()->json($tokenRequest);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Token creation failed'], 500);
        }
    }
}

















