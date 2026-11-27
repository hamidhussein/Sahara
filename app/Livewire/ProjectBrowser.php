<?php

namespace App\Livewire;

use App\Enums\ProjectStatus;
use App\Models\Program;
use App\Models\Project;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Projects list with program, district and status filters that update without
 * reloading the page (plan 1.1, Projects). Filters live in the URL so a filtered
 * list can be shared.
 */
class ProjectBrowser extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $program = '';

    #[Url(except: '')]
    public string $district = '';

    #[Url(except: '')]
    public string $status = '';

    public function updated(string $property): void
    {
        if (in_array($property, ['program', 'district', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('program', 'district', 'status');
        $this->resetPage();
    }

    public function render(): View
    {
        $status = ProjectStatus::tryFrom($this->status);

        $projects = Project::published()
            ->whereHas('program', fn ($q) => $q->published())
            ->when($this->program !== '', fn ($q) => $q->whereHas('program', fn ($p) => $p->where('slug', $this->program)))
            ->when($this->district !== '', fn ($q) => $q->where('district', $this->district))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['program', 'media'])
            ->latest('completed_on')
            ->latest()
            ->paginate(12);

        return view('livewire.project-browser', [
            'projects' => $projects,
            'programs' => Program::published()->ordered()->pluck('name', 'slug'),
            'districts' => Project::published()->whereNotNull('district')->distinct()->orderBy('district')->pluck('district'),
            'statuses' => ProjectStatus::cases(),
            'filtered' => $this->program !== '' || $this->district !== '' || $status,
        ]);
    }
}
