<?php

namespace App\Livewire;

use App\Enums\BloodGroup;
use App\Livewire\Concerns\GuardsPublicForm;
use App\Models\BloodDonor;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/** Get Involved → Become a blood donor (plan 2.1). Registering again updates the same entry. */
class BloodDonorForm extends Component
{
    use GuardsPublicForm;

    public string $name = '';

    public string $bloodGroup = '';

    public string $district = '';

    public string $phone = '';

    public string $lastDonatedOn = '';

    public bool $consent = false;

    public bool $sent = false;

    public function register(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'bloodGroup' => ['required', Rule::enum(BloodGroup::class)],
            'district' => ['required', Rule::in(config('sswf.districts'))],
            'phone' => ['required', 'string', 'max:32'],
            'lastDonatedOn' => ['nullable', 'date', 'before_or_equal:today', 'after:1950-01-01'],
            'consent' => ['accepted'],
        ], [
            'name.required' => 'Enter your full name.',
            'bloodGroup.required' => 'Choose your blood group.',
            'district.required' => 'Choose your district.',
            'phone.required' => 'Enter a mobile number so we can call you when a patient needs your blood group.',
            'lastDonatedOn.before_or_equal' => 'The last donation date cannot be in the future.',
            'consent.accepted' => 'Please agree to be contacted in emergencies. That is how the registry works.',
        ]);

        $phone = $this->phoneOrFail($this->phone);

        if (! $this->isBot()) {
            $this->limitSubmissions('blood-donor', 'name');

            BloodDonor::updateOrCreate(['phone' => $phone], [
                'name' => trim($this->name),
                'blood_group' => $this->bloodGroup,
                'district' => $this->district,
                'last_donated_on' => $this->lastDonatedOn ?: null,
                'is_available' => true,
                'consent_at' => now(),
                'ip_address' => request()->ip(),
            ]);
        }

        $this->reset('name', 'bloodGroup', 'district', 'phone', 'lastDonatedOn', 'consent');
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.blood-donor-form', ['groups' => BloodGroup::cases()]);
    }
}
