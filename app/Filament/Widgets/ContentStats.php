<?php

namespace App\Filament\Widgets;

use App\Enums\Role;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\ContactMessage;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** Dashboard: what Editors need to act on. */
class ContentStats extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Website & messages';

    protected ?string $description = 'Keep your community informed and respond to enquiries.';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->hasAnyRole([Role::Editor->value, Role::SuperAdmin->value]);
    }

    protected function getStats(): array
    {
        $messages = ContactMessage::open()->count();
        $drafts = Post::whereNull('published_at')->count();
        $published = Post::published()->where('published_at', '>=', now()->subDays(30))->count();

        return [
            Stat::make('Messages to answer', $messages)
                ->icon('heroicon-o-inbox')
                ->description($messages ? 'From the Contact page' : 'All answered')
                ->color($messages ? 'warning' : 'success')
                ->url(ContactMessageResource::getUrl('index')),
            Stat::make('News drafts', $drafts)
                ->icon('heroicon-o-pencil-square')
                ->description($drafts ? 'Written but not published' : 'No drafts waiting')
                ->url(PostResource::getUrl('index')),
            Stat::make('News in the last 30 days', $published)
                ->icon('heroicon-o-newspaper')
                ->description($published ? 'Keep it up' : 'Share a recent camp or project')
                ->color($published ? 'success' : 'gray')
                ->url(PostResource::getUrl('index')),
        ];
    }
}
