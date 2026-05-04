<?php

namespace App\Notifications;

use App\Enums\IdVerificationStatus;
use App\Models\IdVerification;
use App\Models\User;

class IdStatusNotification extends BaseNotification
{
    public $idVerification;
    public $user;

    public function __construct(IdVerification $idVerification, User $user)
    {
        $this->idVerification = $idVerification;
        $this->user = $user;
    }

    public function getNotificationPreference(): string
    {
        return 'id_verification_status_update';
    }

    public function toArray($notifiable)
    {
        $idData = $this->getIdVerificationData();

        return [
            'type' => 'id_verification_status_update',
            'title' => 'ID Verification Update',
            'message' => $idData['message'],
            'id_verification_id' => $this->idVerification->id,
            'document_type' => $this->idVerification->getDocumentType(),
            'status' => $this->idVerification->status_name,
            'rejection_reason' => $this->idVerification->rejection_reason,
            'action_url' => route('user.settings.id-verification'),
            'timestamp' => now()->toISOString(),
            'icon' => $idData['icon'],
            'color' => $idData['color']
        ];
    }


    public function getEmailData()
    {
        $idData = $this->getIdVerificationData();
        $reason = $this->idVerification->rejection_reason ?? '';

        return [
            'template' => $idData['mail_template'],
            'shortcodes' => [
                'username' => $this->user->full_name,
                'rejection_reason' => $reason,
                'website_name' => @settings('general')->site_name,
            ]
        ];
    }

    private function getIdVerificationData()
	{
		return match ($this->idVerification->status) {
			IdVerificationStatus::APPROVED => [
				'message'      => "Congratulations! Your ID Verification has been approved",
				'color'        => 'success',
				'icon'         => 'check-circle',
				'mail_template'=> 'id_verification_approved',
			],
			IdVerificationStatus::REJECTED => [
				'message'      => "Your ID Verification has been rejected",
				'color'        => 'error',
				'icon'         => 'x-circle',
				'mail_template'=> 'id_verification_rejected',
			],
			default => [
				'message'      => "Your ID Verification status is unknown",
				'color'        => 'info',
				'icon'         => 'info',
				'mail_template'=> null,
			],
		};
	}
}

















