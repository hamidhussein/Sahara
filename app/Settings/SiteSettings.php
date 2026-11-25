<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Contact details, registration and social links shown in the header,
 * emergency strip, sticky mobile bar and footer. Edited in Filament.
 */
class SiteSettings extends Settings
{
    public string $office_phone;

    /** Not yet confirmed; the site falls back to the office phone while empty. */
    public ?string $ambulance_phone;

    /** International format, e.g. +92 3xx xxxxxxx. Hidden while empty. */
    public ?string $whatsapp_number;

    public string $email;

    public string $address;

    public string $registration_number;

    public ?string $office_hours;

    public ?string $facebook_url;

    public ?string $instagram_url;

    public ?string $youtube_url;

    public ?string $tiktok_url;

    /** Approved by the chairman before it appears on the Leadership page. */
    public ?string $chairman_message;

    public static function group(): string
    {
        return 'site';
    }

    public function telLink(?string $number): ?string
    {
        return $number ? 'tel:'.preg_replace('/[^0-9+]/', '', $number) : null;
    }

    public function whatsappLink(): ?string
    {
        return $this->whatsapp_number
            ? 'https://wa.me/'.preg_replace('/\D/', '', $this->whatsapp_number)
            : null;
    }

    /** @return array<string, string> label => url, only the ones that are set */
    public function socialLinks(): array
    {
        return array_filter([
            'Facebook' => $this->facebook_url,
            'Instagram' => $this->instagram_url,
            'YouTube' => $this->youtube_url,
            'TikTok' => $this->tiktok_url,
        ]);
    }
}
