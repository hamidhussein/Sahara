<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use App\Enums\ContentLanguage;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Model;

/** An editable text page such as the Privacy Policy (plan 1.1, Legal). */
class Page extends Model
{
    use HasSlug, RecordsActivity;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'language' => ContentLanguage::class,
            'is_published' => 'boolean',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }
}
