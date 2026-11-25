<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

#[Signature('sswf:create-admin {--email=} {--name=} {--role=Super Admin}')]
#[Description('Create an admin user (or give an existing user a role) for the SSWF admin panel')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = $this->option('email') ?: text('Email', required: true, validate: ['email' => 'email']);
        $role = $this->option('role');

        if (! in_array($role, Role::names(), true)) {
            $role = select('Role', Role::names(), default: Role::SuperAdmin->value);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $name = $this->option('name') ?: text('Name', required: true);
            $password = password('Password (at least 12 characters)', required: true, validate: ['password' => 'min:12']);

            Validator::make(['email' => $email], ['email' => 'email'])->validate();

            $user = User::create(['name' => $name, 'email' => $email, 'password' => $password]);
            $this->components->info("Created {$user->email}.");
        }

        $user->assignRole($role);
        $this->components->info("{$user->email} now has the {$role} role and can sign in at ".url('/admin'));

        return self::SUCCESS;
    }
}
