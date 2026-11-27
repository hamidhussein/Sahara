<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var list<string> */
    protected array $roleNames = [];

    protected function getHeaderActions(): array
    {
        return [UserResource::resetTwoFactorAction(), UserResource::removeAccessAction(), UserResource::deleteAction()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_names'] = $this->record->getRoleNames()->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleNames = $data['role_names'] ?? [];
        unset($data['role_names']);

        return $data;
    }

    /** Add and remove only what changed, so the activity log shows exactly that. */
    protected function afterSave(): void
    {
        /** @var User $user */
        $user = $this->record;
        $current = $user->getRoleNames()->all();

        foreach (array_diff($current, $this->roleNames) as $role) {
            $user->removeRole($role);
        }
        if ($added = array_values(array_diff($this->roleNames, $current))) {
            $user->assignRole($added);
        }
    }
}
