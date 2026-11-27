<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** The help cards on the Get Help page (plan 2.1). */
enum HelpType: string implements HasLabel
{
    case Blood = 'blood';
    case Medical = 'medical';
    case HospitalEssentials = 'hospital_essentials';
    case Ration = 'ration';
    case Education = 'education';
    case Wheelchair = 'wheelchair';

    public function getLabel(): string
    {
        return match ($this) {
            self::Blood => 'Need blood?',
            self::Medical => 'Medical help',
            self::HospitalEssentials => 'Hospital essentials at DHQ Gilgit',
            self::Ration => 'Ration help',
            self::Education => 'Education support',
            self::Wheelchair => 'Wheelchair request',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Blood => 'Tell us the blood group and hospital; we will contact registered donors near you.',
            self::Medical => 'Support with surgical items, treatment or medicines for patients who cannot afford them.',
            self::HospitalEssentials => 'With a patient at DHQ Hospital Gilgit? Borrow a blanket, hot pot, thermos, plates and cups free of cost. Bring your original CNIC and return the items at discharge.',
            self::Ration => 'Food support for families facing hardship.',
            self::Education => 'Scholarships, books and uniforms, or an interest-free loan for medical and engineering students.',
            self::Wheelchair => 'For a person with a disability who needs a wheelchair.',
        };
    }

    /** Placeholder for "Tell us what you need", so people know what helps us. */
    public function prompt(): string
    {
        return match ($this) {
            self::Blood => 'Blood group needed, number of bottles, hospital and ward, and by when.',
            self::Medical => 'The patient’s illness or operation, the hospital, and what is needed.',
            self::HospitalEssentials => 'Which items you need, the ward, and the expected stay.',
            self::Ration => 'How many people are in the family, and your situation.',
            self::Education => 'The student’s class or programme, institution, and what is needed.',
            self::Wheelchair => 'Who needs it, their age, and their situation.',
        };
    }
}
