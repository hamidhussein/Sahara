<?php

namespace App\Livewire;

use App\Enums\HelpType;
use App\Livewire\Concerns\GuardsPublicForm;
use App\Mail\HelpRequestReceived;
use App\Models\HelpRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/** Get Help page (plan 2.1): one form; the help card chosen sets the type. */
class HelpRequestForm extends Component
{
    use GuardsPublicForm, WithFileUploads;

    #[Url(as: 'type', except: '')]
    public string $type = '';

    public string $name = '';

    public string $phone = '';

    public string $district = '';

    public string $village = '';

    public string $personInNeed = '';

    public string $details = '';

    public $document = null;

    public bool $consent = false;

    public ?string $reference = null;

    public function choose(string $type): void
    {
        $this->type = HelpType::tryFrom($type)?->value ?? '';
        $this->resetErrorBag('type');
    }

    public function send(): void
    {
        $this->validate([
            'type' => ['required', Rule::enum(HelpType::class)],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'district' => ['required', Rule::in(config('sswf.districts'))],
            'village' => ['nullable', 'string', 'max:120'],
            'personInNeed' => ['nullable', 'string', 'max:120'],
            'details' => ['required', 'string', 'min:10', 'max:3000'],
            'document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'consent' => ['accepted'],
        ], [
            'type.required' => 'Choose the kind of help you need.',
            'name.required' => 'Enter your name.',
            'phone.required' => 'Enter a mobile number so we can call you.',
            'district.required' => 'Choose your district.',
            'details.required' => 'Tell us what you need.',
            'details.min' => 'Add a few more words so we understand what you need.',
            'document.mimes' => 'Upload a photo (JPG, PNG) or a PDF.',
            'document.max' => 'The document must be smaller than 5 MB.',
            'consent.accepted' => 'Please agree that we may contact you and check this request.',
        ]);

        $phone = $this->phoneOrFail($this->phone);

        if ($this->isBot()) {
            $this->reference = 'HR-'.now()->year.'-000000';

            return;
        }

        $this->limitSubmissions('help-request', 'details');

        $request = DB::transaction(function () use ($phone) {
            $request = HelpRequest::create([
                'type' => $this->type,
                'name' => trim($this->name),
                'phone' => $phone,
                'district' => $this->district,
                'village' => trim($this->village) ?: null,
                'person_in_need' => trim($this->personInNeed) ?: null,
                'details' => trim($this->details),
                'consent_at' => now(),
                'ip_address' => request()->ip(),
            ]);

            if ($this->document) {
                $request->addMedia($this->document)
                    ->usingFileName($request->reference.'.'.$this->document->guessExtension())
                    ->toMediaCollection('document');
            }

            Mail::to(HelpRequestReceived::recipients())->queue(new HelpRequestReceived($request));

            return $request;
        });

        $this->reference = $request->reference;
        $this->reset('name', 'phone', 'district', 'village', 'personInNeed', 'details', 'document', 'consent');
    }

    public function render(): View
    {
        return view('livewire.help-request-form', [
            'types' => HelpType::cases(),
            'chosen' => HelpType::tryFrom($this->type),
        ]);
    }
}
