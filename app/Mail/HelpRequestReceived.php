<?php

namespace App\Mail;

use App\Filament\Resources\HelpRequests\HelpRequestResource;
use App\Models\HelpRequest;
use App\Models\User;
use App\Enums\Role;
use App\Settings\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * To the Services team (the duty officer) when someone asks for help.
 * Plan 2: "a test help request reaches the duty officer within minutes".
 */
class HelpRequestReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public HelpRequest $helpRequest)
    {
        $this->afterCommit();
    }

    /**
     * Every Services user; Super Admins if there are none; the site email as a last resort.
     *
     * @return list<string>
     */
    public static function recipients(): array
    {
        foreach ([Role::Services, Role::SuperAdmin] as $role) {
            $emails = User::whereHas('roles', fn ($query) => $query->where('name', $role->value))
                ->pluck('email')->filter()->unique()->values()->all();
            if ($emails) {
                return $emails;
            }
        }

        return [app(SiteSettings::class)->email];
    }

    public function envelope(): Envelope
    {
        $urgent = $this->helpRequest->type === \App\Enums\HelpType::Blood ? 'URGENT: ' : '';

        return new Envelope(subject: "{$urgent}Help request {$this->helpRequest->reference}: {$this->helpRequest->type->getLabel()}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.help-request-received', with: [
            'adminUrl' => HelpRequestResource::getUrl('view', ['record' => $this->helpRequest], panel: 'admin'),
        ]);
    }
}
