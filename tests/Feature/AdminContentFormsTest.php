<?php

use App\Enums\PostCategory;
use App\Enums\ProjectStatus;
use App\Enums\Role;
use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Programs\Pages\EditProgram;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\RelationManagers\UpdatesRelationManager;
use App\Models\Post;
use App\Models\Program;
use App\Models\Project;
use App\Models\User;
use App\Settings\SiteSettings;
use Database\Seeders\DatabaseSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->editor = User::factory()->create();
    $this->editor->assignRole(Role::Editor->value);
    $this->actingAs($this->editor);
});

it('creates a project from the admin form', function () {
    Livewire::test(CreateProject::class)
        ->fillForm([
            'name' => 'Minapin Water Filtration Plant',
            'program_id' => Program::where('slug', 'clean-water')->value('id'),
            'status' => ProjectStatus::Ongoing->value,
            'village' => 'Minapin',
            'district' => 'Nagar',
            'summary' => 'A new plant for Minapin.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $project = Project::where('name', 'Minapin Water Filtration Plant')->sole();
    expect($project->slug)->toBe('minapin-water-filtration-plant')
        ->and($project->status)->toBe(ProjectStatus::Ongoing);

    $this->get('/projects/minapin-water-filtration-plant')->assertOk()->assertSee('Minapin, Nagar');
});

it('requires the project name, program and summary', function () {
    Livewire::test(CreateProject::class)
        ->fillForm(['name' => '', 'program_id' => null, 'summary' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required', 'program_id' => 'required', 'summary' => 'required']);
});

it('adds a dated update to a project', function () {
    $project = Project::where('slug', 'miachar-water-plant')->sole();

    Livewire::test(UpdatesRelationManager::class, ['ownerRecord' => $project, 'pageClass' => EditProject::class])
        ->callAction(TestAction::make('create')->table(), data: ['happened_on' => '2026-03-01', 'title' => 'Plant inaugurated'])
        ->assertHasNoFormErrors();

    $this->get('/projects/miachar-water-plant')->assertSee('Plant inaugurated')->assertSee('1 March 2026');
});

it('creates a news post as a draft and records the author', function () {
    Livewire::test(CreatePost::class)
        ->fillForm([
            'title' => 'Winter Package 2026 distributed',
            'category' => PostCategory::SocialServices->value,
            'body' => '<p>Blankets reached families from Yasin to Sherqila.</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::where('title', 'Winter Package 2026 distributed')->sole();
    expect($post->published_at)->toBeNull()
        ->and($post->author_id)->toBe($this->editor->id);

    $this->get('/news/'.$post->slug)->assertNotFound();
});

it('edits a program', function () {
    $program = Program::where('slug', 'education')->sole();

    Livewire::test(EditProgram::class, ['record' => $program->getRouteKey()])
        ->fillForm(['tagline' => 'Every talented student deserves a chance.'])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->get('/programs/education')->assertSee('Every talented student deserves a chance.');
});

it('saves the ambulance number from the settings page', function () {
    $admin = User::factory()->create();
    $admin->assignRole(Role::SuperAdmin->value);

    Livewire::actingAs($admin)->test(ManageSiteSettings::class)
        ->fillForm(['ambulance_phone' => '+92 355 0000000'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(app(SiteSettings::class)->ambulance_phone)->toBe('+92 355 0000000');
});
