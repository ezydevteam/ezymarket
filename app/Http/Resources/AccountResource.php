<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->getNameData(),
            'username' => $this->username,
            'email' => $this->email,
            'balance' => $this->balance,
            'currency' => defaultCurrency()->code,
            'profile' => $this->getProfileData(),
            'registered_at' => $this->created_at,
        ];
    }

    private function getNameData(): array
    {
        return [
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'full_name' => $this->full_name,
        ];
    }

    private function getProfileData(): array
    {
        return [
            'heading' => $this->basic_info['heading'] ?? null,
            'description' => $this->basic_info['bio'] ?? null,
            'social_links' => array_intersect_key(
                $this->basic_info ?? [],
                array_flip(['facebook', 'x', 'youtube', 'linkedin', 'instagram', 'pinterest'])
            ),
            'media' => [
                'avatar' => $this->avatar_url,
                'cover' => $this->profile_cover_url,
            ],
        ];
    }
}
