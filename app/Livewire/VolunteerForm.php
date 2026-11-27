<?php

namespace App\Livewire;

use App\Livewire\Concerns\GuardsPublicForm;
use App\Models\Volunteer;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

/** Get Involved → Volunteer (plan 2.1). Signing up again updates the same entry. */
class VolunteerForm extends Component
{
    use GuardsPublicForm;

    public string $name = '';

    public string $phone = '';

    public string $area = '';

    /** @var list<string> */
    public array $skills = [];

    public string $availability = '';

    public bool $sent = false;

    public function join(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'area' => ['required', 'string', 'max:120'],
            'skills' => ['array'],
            'skills.*' => [Rule::in(Volunteer::SKILLS)],
            'availability' => ['nullable', 'string', 'max:160'],
        ], [
            'name.required' => 'Enter your full name.',
            'phone.required' => 'Enter a mobile number so our coordinator can call you.',
            'area.required' => 'Tell us where you live, so we can invite you to work nearby.',
        ]);

        $phone = $this->phoneOrFail($this->phone);

        if (! $this->isBot()) {
            $this->limitSubmissions('volunteer', 'name');

            Volunteer::updateOrCreate(['phone' => $phone], [
                'name' => trim($this->name),
                'area' => trim($this->area),
                'skills' => array_values(array_intersect(Volunteer::SKILLS, $this->skills)),
                'availability' => trim($this->availability) ?: null,
                'ip_address' => request()->ip(),
            ]);
        }

        $this->reset('name', 'phone', 'area', 'skills', 'availability');
        $this->sent = true;
    }

    public function render(): View
    {
        return view('livewire.volunteer-form', ['allSkills' => Volunteer::SKILLS]);
    }
}
