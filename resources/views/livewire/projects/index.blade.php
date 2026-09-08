<div>
    <x-ui.page-header :breadcrumbs="[['label' => __('erp.nav.projects')]]" :title="__('erp.project.title')" :description="__('erp.project.hint')"><x-slot:actions><x-ui.export-button /><x-ui.button :href="route('projects.create')">{{ __('erp.project.create') }}</x-ui.button></x-slot:actions></x-ui.page-header>

    <x-ui.toolbar :summary="$projects ? trans_choice('erp.pagination.result_count', $projects->total(), ['count' => number_format($projects->total())]) : null" />

    @if ($projects === null || $projects->isEmpty())
        <x-ui.empty-state :title="__('erp.project.empty_title')" :message="__('erp.project.empty_hint')" />
    @else
        <x-ui.table>
            <thead><tr>
                <th>{{ __('erp.code') }}</th>
                <th>{{ __('erp.project.name') }}</th>
                <th class="!text-end">{{ __('erp.project.budget') }}</th>
                <th class="!text-end">{{ __('erp.project.charged') }}</th>
                <th class="!text-end">{{ __('erp.project.capitalized') }}</th>
                <th>{{ __('erp.status') }}</th>
            </tr></thead>
            <tbody>
                @foreach ($projects as $project)
                    <tr wire:key="prj-{{ $project->id }}">
                        <td><a href="{{ route('projects.show', $project->id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">{{ $project->code }}</a></td>
                        <td>{{ $project->name }}</td>
                        <td class="text-end"><x-ui.money :amount="$project->budget ?? '0'" /></td>
                        <td class="text-end"><x-ui.money :amount="$project->charged ?? '0'" /></td>
                        <td class="text-end"><x-ui.money :amount="$project->capitalized ?? '0'" muted /></td>
                        <td><x-ui.status-badge :status="$project->status?->value ?? $project->status" /></td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <x-ui.pagination :paginator="$projects" />
    @endif
</div>
