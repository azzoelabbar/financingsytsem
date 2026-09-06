<div hidden data-command-source data-nav-group="{{ __('erp.dashboard_sections.quick_actions') }}">
    <a href="{{ route('ar.invoices.create') }}">{{ __('erp.sales_invoice.create') }}</a>
    <a href="{{ route('ap.invoices.create') }}">{{ __('erp.purchase_invoice.create') }}</a>
    <a href="{{ route('ar.customers.create') }}">{{ __('erp.customer.create') }}</a>
</div>
<dialog x-ref="commands" class="mizan-command-dialog" aria-labelledby="command-title" @click="if ($event.target === $el) $el.close()" @keydown.down.prevent="moveSelection(1)" @keydown.up.prevent="moveSelection(-1)">
    <div class="flex shrink-0 items-center gap-3 border-b border-border px-5 py-4">
        <x-ui.icon name="search" class="text-brand" />
        <label id="command-title" for="mizan-command-search" class="sr-only">{{ __('erp.mizan.navigate') }}</label>
        <input id="mizan-command-search" x-ref="commandSearch" type="search" x-model="query" @input="selected = 0" @keydown.enter.prevent="openSelected()" :aria-activedescendant="results.length ? 'command-result-' + selected : null" aria-controls="command-results" role="combobox" aria-expanded="true" autocomplete="off" placeholder="{{ __('erp.mizan.search_pages') }}" class="min-w-0 flex-1 border-0 bg-transparent py-1 text-base outline-none" />
        <button type="button" @click="$refs.commands.close()" class="mizan-key" aria-label="{{ __('erp.cancel') }}">ESC</button>
    </div>
    <p class="px-5 pb-2 pt-4 text-xs text-muted-foreground">{{ __('erp.mizan.destinations') }}</p>
    <div id="command-results" x-ref="commandResults" role="listbox" class="min-h-0 flex-1 overflow-y-auto px-3 pb-3">
        <template x-for="(item, index) in results" :key="item.href">
            <a :href="item.href" :id="'command-result-' + index" role="option" :aria-selected="selected === index" :data-active="selected === index" @mousemove="selected = index" class="mizan-command-result">
                <span x-text="item.label"></span>
                <span class="text-xs text-muted-foreground" x-text="item.group"></span>
            </a>
        </template>
    </div>
    <p x-show="!results.length" class="px-6 py-8 text-center text-sm text-muted-foreground">{{ __('erp.mizan.no_destinations') }}</p>
    <div class="shrink-0 border-t border-border bg-surface-sunken px-5 py-3 text-xs text-muted-foreground">{{ __('erp.mizan.keyboard_hint') }}</div>
</dialog>
