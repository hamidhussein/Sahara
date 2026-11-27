<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var list<string> */
    protected array $roleNames = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->roleNames = $data['role_names'] ?? [];
        unset($data['role_names']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;
        $user->assignRole($this->roleNames); // recorded in the activity log
    }
}
