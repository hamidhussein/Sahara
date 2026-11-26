<?php

namespace App\Filament\Pages;

use App\Backups\BackupManager;
use App\Jobs\RunBackup;
use App\Jobs\RunRestoreCheck;
use App\Settings\BackupSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use UnitEnum;

/**
 * Backups: make one now, check one can be restored, download them, and set
 * the automatic schedule and how long they are kept. Super Admin only.
 */
class ManageBackupSettings extends SettingsPage
{
    protected static string $settings = BackupSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxArrowDown;

    protected static ?string $navigationLabel = 'Backups';

    protected static ?string $title = 'Backups';

    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('Super Admin');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backupNow')
                ->label('Back up now')
                ->icon(Heroicon::OutlinedArchiveBoxArrowDown)
                ->requiresConfirmation()
                ->modalDescription('Backs up the database and all uploaded files now, in the background. It usually takes a minute or two.')
                ->action(function () {
                    RunBackup::dispatch();
                    Notification::make()->success()->title('Backup started')
                        ->body('It runs in the background. Refresh this page in a minute or two to see it in the list.')
                        ->send();
                }),
            Action::make('checkNow')
                ->label('Run restore check')
                ->icon(Heroicon::OutlinedShieldCheck)
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Restores the newest backup into a temporary copy of the database and checks nothing is missing. The live site is not touched.')
                ->action(function () {
                    RunRestoreCheck::dispatch();
                    Notification::make()->success()->title('Restore check started')
                        ->body('Refresh this page in a few minutes to see the result.')
                        ->send();
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return $schema->columns(1)->components([
            Section::make('Status')->schema([
                Text::make(fn () => $this->statusHtml()),
            ]),

            Section::make('Automatic backups')
                ->description('Backs up the database and all uploaded files: photos, documents, QR codes and payment receipts.')
                ->columns(3)
                ->schema([
                    Toggle::make('schedule_enabled')->label('Back up automatically')->live()->columnSpanFull()
                        ->helperText('Needs the server’s scheduler running (Forge: “Scheduler” switched on).'),
                    Select::make('frequency')->options(['daily' => 'Every day', 'weekly' => 'Once a week'])
                        ->required()->native(false)->live()
                        ->visible(fn (Get $get) => $get('schedule_enabled')),
                    Select::make('weekly_day')->label('On')->options($days)->required()->native(false)
                        ->visible(fn (Get $get) => $get('schedule_enabled') && $get('frequency') === 'weekly'),
                    TimePicker::make('time')->label('At (Pakistan time)')->seconds(false)->format('H:i')->required()->native(false)
                        ->visible(fn (Get $get) => $get('schedule_enabled'))
                        ->helperText('Pick a quiet hour, e.g. 01:30.'),
                ]),

            Section::make('Keeping backups')
                ->columns(2)
                ->schema([
                    TextInput::make('keep_days')->label('Keep every backup for')->numeric()->integer()
                        ->minValue(7)->maxValue(365)->suffix('days')->required()
                        ->helperText('After that, one a week is kept for 8 weeks, one a month for a year and one a year for 2 years.'),
                    TextInput::make('notify_email')->label('Alert email')->email()
                        ->placeholder(config('backup.notifications.mail.to'))
                        ->helperText('Gets an email only when a backup fails, goes stale or a restore check fails.'),
                ]),

            Section::make('Restore check')
                ->schema([
                    Toggle::make('restore_check_enabled')->label('Check every Sunday that the newest backup can be restored'),
                ]),

            Section::make('Backups')
                ->description('Newest first. Downloads contain donors’ details and receipts: keep them safe and delete local copies after use.')
                ->schema([
                    Text::make(fn () => $this->listHtml()),
                ]),
        ]);
    }

    protected function statusHtml(): HtmlString
    {
        $settings = app(BackupSettings::class);
        $manager = app(BackupManager::class);
        $latest = $manager->list()->first();
        $disks = implode(', ', config('backup.backup.destination.disks'));
        $encrypted = filled(config('backup.backup.password'));
        $offsite = collect(config('backup.backup.destination.disks'))->contains(fn ($d) => $d !== 'backups' && $d !== 'local');

        $row = fn (string $label, string $value) => '<tr><th style="text-align:left;font-weight:500;opacity:.7;padding:.25rem 1.5rem .25rem 0;vertical-align:top">'.e($label).'</th><td style="padding:.25rem 0">'.$value.'</td></tr>';
        $warn = fn (string $text) => '<span style="color:#b42318;font-weight:600">'.e($text).'</span>';

        $check = match ($settings->last_check_passed) {
            true => '<span style="color:#1e6b3a;font-weight:600">Passed</span>',
            false => $warn('Failed'),
            null => 'Not run yet',
        };

        return new HtmlString('<table style="font-size:.875rem">'
            .$row('Latest backup', $latest ? e($latest['date']->format('j M Y, g:i a')).' ('.e(Number::fileSize($latest['size'])).', '.e($latest['date']->diffForHumans()).')' : $warn('None yet: press “Back up now”'))
            .$row('Automatic backups', $settings->schedule_enabled ? e($settings->cronDescription()).' (Pakistan time)' : $warn('Off'))
            .$row('Last restore check', $check.($settings->last_check_at ? ' · '.e(Carbon::parse($settings->last_check_at)->timezone('Asia/Karachi')->format('j M Y, g:i a')) : '')
                .($settings->last_check_summary ? '<div style="opacity:.75;white-space:pre-line">'.e($settings->last_check_summary).'</div>' : ''))
            .($settings->last_manual_at ? $row('Last “Back up now”', e($settings->last_manual_result).' · '.e(Carbon::parse($settings->last_manual_at)->timezone('Asia/Karachi')->format('j M Y, g:i a'))) : '')
            .$row('Saved to', e($disks).($offsite ? '' : ' · '.$warn('no off-site copy: add one in .env (BACKUP_DISKS) before launch')))
            .$row('Archive password', $encrypted ? 'Set' : $warn('Not set: set BACKUP_ARCHIVE_PASSWORD in .env before launch'))
            .'</table>');
    }

    protected function listHtml(): HtmlString
    {
        $backups = app(BackupManager::class)->list();

        if ($backups->isEmpty()) {
            return new HtmlString('<p style="font-size:.875rem;opacity:.75">No backups yet.</p>');
        }

        $rows = $backups->take(50)->map(function (array $b) {
            $url = URL::temporarySignedRoute('backups.download', now()->addMinutes(30), ['path' => $b['path']]);

            return '<tr style="border-top:1px solid rgba(127,127,127,.2)">'
                .'<td style="padding:.5rem 1rem .5rem 0">'.e($b['date']->format('j M Y, g:i a')).'</td>'
                .'<td style="padding:.5rem 1rem;opacity:.75">'.e(Number::fileSize($b['size'])).'</td>'
                .'<td style="padding:.5rem 0;text-align:right"><a href="'.e($url).'" style="color:#1e6b3a;font-weight:600;text-decoration:underline">Download</a></td>'
                .'</tr>';
        })->implode('');

        return new HtmlString('<table style="width:100%;font-size:.875rem"><thead><tr>'
            .'<th style="text-align:left;font-weight:500;opacity:.7;padding-bottom:.25rem">Made</th>'
            .'<th style="text-align:left;font-weight:500;opacity:.7;padding:0 1rem .25rem">Size</th><th></th></tr></thead><tbody>'
            .$rows.'</tbody></table>'
            .($backups->count() > 50 ? '<p style="font-size:.8rem;opacity:.7;margin-top:.5rem">Showing the newest 50 of '.$backups->count().'.</p>' : ''));
    }
}
