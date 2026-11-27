<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Documents on the Transparency page (plan 1.1). */
enum ReportType: string implements HasLabel
{
    case AnnualReport = 'annual_report';
    case AuditedAccounts = 'audited_accounts';
    case Certificate = 'certificate';

    public function getLabel(): string
    {
        return match ($this) {
            self::AnnualReport => 'Annual report',
            self::AuditedAccounts => 'Audited accounts',
            self::Certificate => 'Registration certificate',
        };
    }
}
