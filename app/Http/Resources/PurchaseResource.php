<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'purchase_code' => $this->code,
            'license_type' => $this->getLicenseTypeLabel(),
            'price' => $this->sale->price,
            'currency' => defaultCurrency()->code,
            'product' => new ProductResource($this->product),
            'support' => $this->getSupportData(),
            'downloaded' => $this->isDownloaded(),
            'date' => $this->created_at,
        ];
    }

    private function getLicenseTypeLabel(): string
    {
        return $this->isRegularLicense()
            ? translate('Regular')
            : translate('Extended');
    }

    private function getSupportData(): ?array
    {
        if (!data_get(settings('product'), 'support_status') || !$this->support_expiry_at) {
            return null;
        }

        return [
            'enabled' => true,
            'expires_at' => $this->support_expiry_at,
        ];
    }
}
