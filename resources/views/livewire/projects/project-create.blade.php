<div>
    <x-ui.page-header :title="__('erp.project.create')" :description="__('erp.project.create_hint')" :breadcrumbs="[['label'=>__('erp.nav.projects'),'href'=>route('projects.index')],['label'=>__('erp.project.create')]]" />
    <form wire:submit="save" class="mx-auto max-w-5xl overflow-hidden rounded-lg border border-border bg-card">
        @error('form') <div class="m-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
        <x-ui.form-section :title="__('erp.project.identity')">
            <x-ui.field :label="__('erp.code')" for="project-code" required :error="$errors->first('code')"><input id="project-code" wire:model="code" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.project.name')" for="project-name" required :error="$errors->first('name')"><input id="project-name" wire:model="name" class="erp-control" /></x-ui.field>
            <x-ui.field :label="__('erp.project.start_date')" for="project-start" required :error="$errors->first('start_date')"><input id="project-start" type="date" wire:model="start_date" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.project.end_date')" for="project-end" :error="$errors->first('end_date')"><input id="project-end" type="date" wire:model="end_date" class="erp-control" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.project.budget')" for="project-budget" required :error="$errors->first('budget')"><input id="project-budget" type="number" min="0" step="0.000001" wire:model="budget" class="erp-control text-end tabular-nums" dir="ltr" /></x-ui.field>
            <x-ui.field :label="__('erp.currency')" for="project-currency" required :error="$errors->first('currency')"><input id="project-currency" wire:model="currency" maxlength="3" class="erp-control uppercase" dir="ltr" /></x-ui.field>
        </x-ui.form-section>
        <x-ui.form-actions><x-ui.button variant="secondary" :href="route('projects.index')">{{ __('erp.cancel') }}</x-ui.button><x-ui.button type="submit">{{ __('erp.create') }}</x-ui.button></x-ui.form-actions>
    </form>
</div>
