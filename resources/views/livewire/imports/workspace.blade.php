<div class="space-y-5">
    <x-ui.page-header :title="__('imports.title').' — '.__('imports.kinds.'.$kind)" :description="__('imports.description')">
        <x-slot:actions><x-ui.button variant="secondary" :href="route('imports.inventory')">{{ __('imports.kinds.items') }}</x-ui.button></x-slot:actions>
    </x-ui.page-header>

    <nav class="flex flex-wrap gap-2" aria-label="{{ __('imports.title') }}">
        @foreach (\App\Support\Import\ImportCatalog::schemas() as $type => $headers)
            @if (app(\App\Support\Accounting\AccountingContext::class)->can(\App\Support\Import\ImportCatalog::permission($type)))
                <x-ui.button size="sm" :variant="$kind === $type ? 'primary' : 'secondary'" :href="route('imports.create', $type)">{{ __('imports.kinds.'.$type) }}</x-ui.button>
            @endif
        @endforeach
    </nav>

    <x-ui.alert variant="warning">{{ __('imports.safety') }} {{ __('imports.functional_currency', ['currency' => $this->company()?->functional_currency]) }}</x-ui.alert>
    <x-ui.alert variant="info">{{ __('imports.hints.'.$kind) }}</x-ui.alert>

    <form wire:submit="preview" class="rounded-lg border border-border bg-card">
        <x-ui.form-section :title="__('imports.upload')" :description="__('imports.file_hint')">
            <x-ui.field for="import-file" :label="__('imports.file')" :error="$errors->first('file')" required>
                <input id="import-file" type="file" wire:model="file" accept=".xlsx" class="erp-control" />
                <p wire:loading wire:target="file" role="status" class="mt-2 text-sm">{{ __('imports.uploading') }}</p>
            </x-ui.field>
            @if (in_array($kind, ['sales', 'purchases', 'expenses', 'receipts', 'payments', 'items']))
                @php $fields = match ($kind) { 'receipts', 'payments' => ['cash_account', 'bank_account'], 'items' => ['inventory_account', 'cogs_account'], default => ['account'] }; @endphp
                @foreach ($fields as $field)
                    <x-ui.field :for="'import-'.$field" :label="__('imports.'.$field)">
                        <select id="import-{{ $field }}" wire:model.live="options.{{ $field }}" class="erp-control">
                            <option value="">{{ __('imports.select_account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->code }}">{{ $account->code }} — {{ app()->getLocale() === 'ar' ? $account->name_ar : ($account->name_en ?? $account->name_ar) }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                @endforeach
            @endif
            @if ($kind === 'expenses')
                <x-ui.field for="import-employee" :label="__('imports.employee_ref')"><input id="import-employee" wire:model.live="options.employee_ref" class="erp-control" maxlength="100" /></x-ui.field>
            @endif
        </x-ui.form-section>
        <div class="border-t border-border p-4">
            <details class="mb-4 text-sm"><summary class="cursor-pointer font-medium">{{ __('imports.columns') }}</summary><p class="mt-2 leading-7 text-muted-foreground">{{ implode(' • ', \App\Support\Import\ImportCatalog::schemas()[$kind]) }}</p></details>
            <x-ui.button type="submit" wire:loading.attr="disabled" wire:target="file,preview">{{ __('imports.preview') }}</x-ui.button>
        </div>
    </form>

    @if ($errors->any())
        <x-ui.alert variant="danger"><div role="alert"><p class="mb-2 font-semibold">{{ __('imports.fix_errors') }}</p><ul class="list-inside list-disc space-y-1">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div></x-ui.alert>
    @endif

    @if ($batch)
        @php
            $rows = $batch->payload['rows'];
            $page = max(1, min($previewPage, max(1, (int) ceil(count($rows) / 25))));
            $pages = max(1, (int) ceil(count($rows) / 25));
        @endphp
        <section class="min-w-0 space-y-3" aria-label="{{ __('imports.preview') }}">
            <div class="flex flex-wrap items-center justify-between gap-3"><h2 class="erp-section-title">{{ $batch->filename }}</h2><p class="text-sm text-muted-foreground">{{ __('imports.row_count', ['count' => count($rows)]) }}</p></div>
            @if ($batch->payload['formulas'])<x-ui.alert variant="warning">{{ __('imports.formulas') }}</x-ui.alert>@endif
            <x-ui.table>
                <thead><tr><th>#</th>@foreach ($batch->payload['headers'] as $header)<th>{{ $header }}</th>@endforeach</tr></thead>
                <tbody>@foreach (array_slice($rows, ($page - 1) * 25, 25) as $row)<tr><td>{{ $row['row'] }}</td>@foreach ($batch->payload['headers'] as $index => $header)<td class="tabular-nums"><bdi>{{ $row['cells'][$index] ?? '' }}</bdi></td>@endforeach</tr>@endforeach</tbody>
            </x-ui.table>
            <div class="flex items-center justify-between gap-2">
                <x-ui.button variant="secondary" wire:click="$set('previewPage', {{ max(1, $page - 1) }})" :disabled="$page <= 1">{{ __('imports.previous') }}</x-ui.button>
                <span class="text-sm">{{ $page }} / {{ $pages }}</span>
                <x-ui.button variant="secondary" wire:click="$set('previewPage', {{ min($pages, $page + 1) }})" :disabled="$page >= $pages">{{ __('imports.next') }}</x-ui.button>
            </div>
            @if ($valid && ! $batch->completed_at)
                <form wire:submit="confirm" class="space-y-4 rounded-lg border border-border bg-card p-5">
                    <p class="font-medium">{{ __('imports.ready', ['count' => $documentCount]) }}</p>
                    <label class="flex items-start gap-3 text-sm"><input type="checkbox" wire:model="acknowledged" class="mt-1" />{{ __('imports.acknowledge') }}</label>
                    <x-ui.button type="submit" wire:loading.attr="disabled">{{ \App\Support\Import\ImportCatalog::reference($kind) ? __('imports.save_reference') : __('imports.confirm') }}</x-ui.button>
                </form>
            @endif
            @if ($batch->completed_at)
                <x-ui.alert variant="success">{{ __('imports.saved') }}</x-ui.alert>
                @if ($batch->results)
                    <div class="flex flex-wrap gap-2">@foreach ($batch->results as $result)<x-ui.button variant="secondary" :href="$result['url']">{{ $result['reference'] }}</x-ui.button>@endforeach</div>
                @endif
            @endif
        </section>
    @endif
    @if ($history->isNotEmpty())
        <x-ui.card :title="__('imports.history')"><div class="space-y-2">@foreach ($history as $import)<button type="button" wire:click="inspect({{ $import->id }})" class="flex w-full flex-wrap justify-between gap-2 rounded-md p-2 text-start text-sm hover:bg-surface-sunken"><span>{{ $import->filename }}</span><span>{{ $import->completed_at->format('Y-m-d H:i') }}</span></button>@endforeach</div></x-ui.card>
    @endif
</div>
