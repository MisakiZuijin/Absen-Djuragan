<?php

namespace App\Observers;

use App\Models\Outsider;
use App\Models\WhatsappNumber;

class OutsiderObserver
{
    /**
     * Handle the Outsider "created" event.
     */
    public function created(Outsider $outsider): void
    {
        $this->handleWhatsappNumber($outsider);
    }

    /**
     * Handle the Outsider "updated" event.
     */
    public function updated(Outsider $outsider): void
    {
        $this->handleWhatsappNumber($outsider);
    }

    /**
     * Handle the Outsider "deleted" event.
     */
    public function deleted(Outsider $outsider): void
    {
        // Hapus data whatsapp_number yang terkait dengan outsider ini
        foreach ($outsider->interns as $intern) {
            WhatsappNumber::where('intern_id', $intern->id)
                ->where('phone_number', $outsider->phone_number)
                ->delete();
        }
    }

    /**
     * Handle WhatsApp number management for outsider
     */
    private function handleWhatsappNumber(Outsider $outsider): void
    {
        // Hanya proses untuk outsider type "ortu"
        if ($outsider->type !== 'ortu') {
            return;
        }

        foreach ($outsider->interns as $intern) {
            if (!empty($outsider->phone_number)) {
                // Update atau create whatsapp_number record
                WhatsappNumber::updateOrCreate(
                    [
                        'intern_id' => $intern->id,
                        'phone_number' => $outsider->phone_number
                    ],
                    [
                        'is_notification_active' => $outsider->notif_enabled
                    ]
                );
            } else {
                // Jika phone_number kosong, hapus record yang ada
                WhatsappNumber::where('intern_id', $intern->id)
                    ->where('phone_number', $outsider->phone_number)
                    ->delete();
            }
        }
    }
}
