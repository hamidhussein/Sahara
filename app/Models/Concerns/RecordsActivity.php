<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Writes "who changed what, and when" to the activity log (Admin → Users →
 * Activity log). Models can narrow what is recorded with the hooks below, so
 * that donors' personal details and staff secrets never reach the log.
 */
trait RecordsActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName($this->activityArea())
            ->setDescriptionForEvent(fn (string $event) => $this->activityDescription($event));

        return $this->activityOnly() !== null
            ? $options->logOnly($this->activityOnly())
            : $options->logUnguarded()->logExcept(['created_at', 'updated_at']);
    }

    /** Area shown in the log: content, donations, messages or users. */
    protected function activityArea(): string
    {
        return 'content';
    }

    /** Only these attributes are recorded; null records every editable attribute. */
    protected function activityOnly(): ?array
    {
        return null;
    }

    protected function activityDescription(string $event): string
    {
        return ucfirst($event).' '.$this->activityLabel();
    }

    /** "project update", "team member" … */
    public function activityLabel(): string
    {
        return Str::of(class_basename($this))->snake(' ')->lower()->value();
    }

    /** A human name for the item, shown next to the entry. */
    public function activityTitle(): string
    {
        foreach (['name', 'title', 'reference', 'subject'] as $attribute) {
            if (filled($this->getAttribute($attribute))) {
                return (string) $this->getAttribute($attribute);
            }
        }

        return '#'.$this->getKey();
    }
}
