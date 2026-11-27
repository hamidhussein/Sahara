<?php

namespace App\Filament\Pages;

use App\Messaging\MessageTemplates;
use App\Settings\MessageSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;
use UnitEnum;

/**
 * Edit the words of donor and contact messages, with placeholders, a live
 * preview and checks that stop a message losing its receipt number or link.
 */
class ManageMessageSettings extends SettingsPage
{
    protected static string $settings = MessageSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static ?string $navigationLabel = 'Donor messages';

    protected static ?string $title = 'Donor and contact messages';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasRole('Super Admin');
    }

    /** Show the default wording where nothing has been edited yet. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach (array_keys(MessageTemplates::definitions()) as $key) {
            $data[$key] = filled($data[$key] ?? null) ? $data[$key] : MessageTemplates::default($key);
        }

        return $data;
    }

    /** Store unchanged wording as "default", so later improvements to the defaults still apply. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        foreach (array_keys(MessageTemplates::definitions()) as $key) {
            $text = trim((string) ($data[$key] ?? ''));
            $data[$key] = ($text === '' || $text === MessageTemplates::default($key)) ? null : $text;
        }

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        $sections = [
            'WhatsApp' => ['whatsapp_receipt'],
            'Receipt email (sent when a donation is confirmed)' => ['receipt_email_subject', 'receipt_email_body'],
            '“Receipt received” email (sent when a donor uploads a receipt)' => ['received_email_subject', 'received_email_body'],
            'Contact form reply' => ['contact_reply_subject', 'contact_reply_body'],
        ];

        return $schema->columns(1)->components([
            Text::make('Edit the words; placeholders in {curly brackets} are filled in for each donor. The email layout, the receipt details table, the PDF and the registration footer stay the same. Empty a box or use “Reset to default” to go back to the original wording.'),
            ...collect($sections)->map(fn (array $keys, string $heading) => Section::make($heading)
                ->schema(collect($keys)->flatMap(fn (string $key) => $this->templateFields($key))->all()))
                ->values()->all(),
        ]);
    }

    /** The editor, its help and a live preview for one template. */
    protected function templateFields(string $key): array
    {
        $definition = MessageTemplates::definitions()[$key];

        $help = $definition['help']
            .' Placeholders: '.MessageTemplates::list(array_keys($definition['placeholders'])).'.'
            .($definition['required'] ? ' Must keep: '.MessageTemplates::list($definition['required']).'.' : '');

        $field = ($definition['multiline']
            ? Textarea::make($key)->rows(9)->autosize()
            : TextInput::make($key)->maxLength(200))
            ->label($definition['label'])
            ->helperText($help)
            ->live(debounce: 500)
            ->rule(fn () => function (string $attribute, $value, \Closure $fail) use ($key) {
                foreach (MessageTemplates::problems($key, $value) as $problem) {
                    $fail($problem);
                }
            })
            ->hintAction(
                Action::make("reset_{$key}")
                    ->label('Reset to default')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->action(fn (Set $set) => $set($key, MessageTemplates::default($key)))
            );

        $preview = Text::make(function (Get $get) use ($key) {
            $text = filled($get($key)) ? (string) $get($key) : MessageTemplates::default($key);
            $filled = preg_replace_callback('/\{([a-z_]+)\}/', fn ($m) => MessageTemplates::sampleValues()[$m[1]] ?? $m[0], $text);

            // Inline styles: the admin's CSS does not include the public site's utility classes.
            return new HtmlString(
                '<span style="display:block;font-size:.8125rem;font-weight:600;opacity:.7">Preview with sample details</span>'
                .'<span style="display:block;margin-top:.25rem;padding:.75rem 1rem;border:1px dashed rgba(127,127,127,.45);border-radius:.5rem;line-height:1.6;white-space:normal">'
                .nl2br(e($filled))
                .'</span>'
            );
        });

        return [$field, $preview];
    }
}
