<?php

use App\Enums\Role;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(fn () => $this->seed(DatabaseSeeder::class));

function userWithRole(?Role $role): User
{
    $user = User::factory()->create();
    if ($role) {
        $user->assignRole($role->value);
    }

    return $user;
}

it('seeds the four admin roles from the plan', function () {
    expect(Spatie\Permission\Models\Role::pluck('name')->sort()->values()->all())
        ->toBe(['Editor', 'Finance', 'Services', 'Super Admin']);
});

it('sends guests to the admin login page', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('blocks signed-in users without an admin role', function () {
    $this->actingAs(userWithRole(null))->get('/admin')->assertForbidden();
});

it('lets every admin role into the panel', function (Role $role) {
    $this->actingAs(userWithRole($role))->get('/admin')->assertOk();
})->with(Role::cases());

it('lets only Editors and Super Admin manage content', function (Role $role, int $status) {
    $user = userWithRole($role);

    foreach (['posts', 'projects', 'programs', 'partners'] as $resource) {
        $this->actingAs($user)->get("/admin/$resource")->assertStatus($status);
        $this->actingAs($user)->get("/admin/$resource/create")->assertStatus($status);
    }

    $project = Project::first();
    $this->actingAs($user)->get("/admin/projects/{$project->getRouteKey()}/edit")->assertStatus($status);
})->with([
    'Super Admin' => [Role::SuperAdmin, 200],
    'Editor' => [Role::Editor, 200],
    'Finance' => [Role::Finance, 403],
    'Services' => [Role::Services, 403],
]);

it('lets only Super Admin open the settings pages', function (Role $role, int $status) {
    $user = userWithRole($role);

    foreach (['manage-site-settings', 'manage-donation-settings', 'manage-impact-settings'] as $page) {
        $this->actingAs($user)->get("/admin/$page")->assertStatus($status);
    }
})->with([
    'Super Admin' => [Role::SuperAdmin, 200],
    'Editor' => [Role::Editor, 403],
    'Finance' => [Role::Finance, 403],
    'Services' => [Role::Services, 403],
]);
