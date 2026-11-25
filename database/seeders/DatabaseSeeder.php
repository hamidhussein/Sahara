<?php

namespace Database\Seeders;

use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as RoleModel;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the admin roles. Create the first admin with: php artisan sswf:create-admin
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Role::names() as $name) {
            RoleModel::findOrCreate($name, 'web');
        }

        $this->call([ContentSeeder::class, DonationSeeder::class, LeadershipSeeder::class]);
    }
}
