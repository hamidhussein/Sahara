<?php

namespace App\Livewire;

use App\Settings\DonationSettings;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Plan 1.1, Zakat calculator: 2.5% of zakatable wealth if it is above the nisab.
 * The nisab is set by Finance in Admin → Settings, since it follows metal prices.
 */
class ZakatCalculator extends Component
{
    public ?string $cash = null;

    public ?string $gold = null;

    public ?string $silver = null;

    public ?string $business = null;

    public ?string $debts = null;

    protected static function rupees(?string $value): int
    {
        return (int) preg_replace('/\D/', '', (string) $value);
    }

    #[Computed]
    public function nisab(): ?int
    {
        return app(DonationSettings::class)->nisab_amount;
    }

    #[Computed]
    public function wealth(): int
    {
        $assets = static::rupees($this->cash) + static::rupees($this->gold) + static::rupees($this->silver) + static::rupees($this->business);

        return max(0, $assets - static::rupees($this->debts));
    }

    /** null while nothing is entered; 0 when below the nisab. */
    #[Computed]
    public function zakat(): ?int
    {
        if ($this->wealth === 0) {
            return null;
        }

        if ($this->nisab !== null && $this->wealth < $this->nisab) {
            return 0;
        }

        return (int) ceil($this->wealth * 0.025);
    }

    public function render(): View
    {
        return view('livewire.zakat-calculator');
    }
}
