<div>
    @php
        $lifecycle = ['draft', 'submitted', 'approved', 'posted', 'reimbursed'];
        $order = [
            'draft' => 0, 'submitted' => 1, 'rejected' => 1, 'approved' => 2,
            'posted' => 3, 'partially_reimbursed' => 3, 'reimbursed' => 4,
        ];
        $current = $order[$status] ?? 0;
        $posted = in_array($status, ['posted', 'partially_reimbursed', 'reimbursed'], true);
    @endphp

    <x-ui.entity-header
        :title="$claim->number ?? __('erp.expense.draft')"
        :eyebrow="__('erp.expense.title')"
        :subtitle="$claim->employee_ref ?? null"
        code="EXP"
        :status="$status"
        :tone="$status === 'rejected' ? 'danger' : ($posted ? 'brand' : 'neutral')"
        :breadcrumbs="[
            ['label' => __('erp.nav.expenses'), 'href' => route('expenses.index')],
            ['label' => $claim->number ?? __('erp.expense.draft')],
        ]"
    >
        <x-slot:actions>
            <x-ui.button variant="ghost" :href="route('expenses.index')">{{ __('erp.action.back') }}</x-ui.button>
            @if ($claim->journal_id)
                <x-ui.button variant="secondary" :href="route('gl.journals.show', $claim->journal_id)">{{ __('erp.document.view_journal') }}</x-ui.button>
            @endif

            @if ($status === 'draft')
                <x-ui.button wire:click="submit">{{ __('erp.expense.submit') }}</x-ui.button>
            @elseif ($status === 'submitted')
                <x-ui.confirm-action
                    action="reject"
                    variant="danger-ghost"
                    :label="__('erp.expense.reject')"
                    :title="__('erp.expense.reject_confirm_title')"
                    :message="__('erp.expense.reject_confirm_body')"
                />
                <x-ui.button wire:click="approve">{{ __('erp.expense.approve') }}</x-ui.button>
            @elseif ($status === 'approved')
                <x-ui.confirm-action
                    action="post"
                    :label="__('erp.action.post')"
                    :title="__('erp.expense.post_confirm_title')"
                    :message="__('erp.expense.post_confirm_body')"
                    :confirm-label="__('erp.action.confirm_post')"
                />
            @elseif (in_array($status, ['posted', 'partially_reimbursed'], true))
                <x-ui.button wire:click="reimburse">{{ __('erp.expense.reimburse') }}</x-ui.button>
            @endif
        </x-slot:actions>

        <x-slot:metrics>
            <x-ui.metric :label="__('erp.amount')">
                <x-ui.money :amount="$claim->amount" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.expense.reimbursed_amount')">
                <x-ui.money :amount="$claim->reimbursed_amount ?? '0'" :muted="(float) ($claim->reimbursed_amount ?? 0) == 0" />
            </x-ui.metric>
            <x-ui.metric :label="__('erp.date')">
                <span class="tabular-nums" dir="ltr">{{ $claim->claim_date?->format('Y-m-d') }}</span>
            </x-ui.metric>
            <x-ui.metric :label="__('erp.expense.employee')">
                <span class="text-[0.9375rem]">{{ $claim->employee_ref ?? '-' }}</span>
            </x-ui.metric>
        </x-slot:metrics>
    </x-ui.entity-header>

    <x-ui.workflow-steps
        :steps="collect($lifecycle)->map(fn (string $step) => ['key' => $step, 'label' => __('erp.doc_status.'.$step)])->all()"
        :current="$current"
        :failed="$status === 'rejected' ? __('erp.doc_status.rejected') : null"
    />

    @if ($posted)
        <x-ui.posted-notice />
    @endif

    @error('action') <div class="mb-5"><x-ui.alert variant="danger">{{ $message }}</x-ui.alert></div> @enderror
    @if ($status === 'submitted')
        <x-ui.card :title="__('erp.expense.rejection')" class="mb-5"><x-ui.field :label="__('erp.expense.rejection_reason')" for="rejection-reason" required :error="$errors->first('rejectionReason')"><textarea id="rejection-reason" wire:model="rejectionReason" class="erp-control"></textarea></x-ui.field></x-ui.card>
    @elseif (in_array($status, ['posted','partially_reimbursed'], true))
        <x-ui.card :title="__('erp.expense.reimbursement')" class="mb-5"><div class="grid gap-4 sm:grid-cols-2"><x-ui.field :label="__('erp.date')" for="reimbursement-date" required :error="$errors->first('reimbursementDate')"><input id="reimbursement-date" type="date" wire:model="reimbursementDate" class="erp-control" dir="ltr" /></x-ui.field><x-ui.field :label="__('erp.expense.reimbursement_amount')" for="reimbursement-amount" :error="$errors->first('reimbursementAmount')"><input id="reimbursement-amount" type="number" min="0.000001" step="0.000001" wire:model="reimbursementAmount" class="erp-control text-end tabular-nums" dir="ltr" placeholder="{{ __('erp.expense.full_balance') }}" /></x-ui.field></div></x-ui.card>
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <x-ui.card :title="__('erp.expense.claim_details')">
                <x-ui.detail-list :rows="[
                    ['label' => __('erp.expense.employee'), 'value' => $claim->employee_ref],
                    ['label' => __('erp.date'), 'value' => $claim->claim_date?->format('Y-m-d'), 'dir' => 'ltr'],
                ]" />
            </x-ui.card>

            <x-ui.card :title="__('erp.document.lines')" flush>
                <x-ui.table flush>
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>{{ __('erp.sales_invoice.line_description') }}</th>
                            <th>{{ __('erp.expense.account') }}</th>
                            <th class="!text-end">{{ __('erp.document.tax') }}</th>
                            <th class="!text-end">{{ __('erp.document.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($claim->lines as $line)
                            <tr wire:key="exp-line-{{ $line->id }}">
                                <td class="tabular-nums text-muted-foreground">{{ $line->line_no }}</td>
                                <td class="text-foreground">{{ $line->description ?? '-' }}</td>
                                <td class="font-mono text-xs text-muted-foreground" dir="ltr">{{ $line->expense_account_code }}</td>
                                <td class="text-end"><x-ui.money :amount="$line->tax_amount" muted /></td>
                                <td class="text-end"><x-ui.money :amount="$line->amount" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="!py-6 text-center text-muted-foreground">{{ __('erp.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="!text-end">{{ __('erp.total') }}</td>
                            <td class="text-end"><x-ui.money :amount="$claim->amount" /></td>
                        </tr>
                    </tfoot>
                </x-ui.table>
            </x-ui.card>
        </div>

        <div class="space-y-5">
            <x-ui.card :title="__('erp.document.accounting')" :tone="$claim->journal_id ? 'brand' : null">
                @if ($claim->journal_id)
                    <a href="{{ route('gl.journals.show', $claim->journal_id) }}" wire:navigate class="text-[0.8125rem] font-medium text-[var(--brand-600)] hover:underline">
                        {{ __('erp.document.journal') }} #{{ $claim->journal_id }}
                    </a>
                @else
                    <p class="text-[0.8125rem] leading-relaxed text-muted-foreground">{{ __('erp.document.not_posted_yet') }}</p>
                @endif
            </x-ui.card>

            <x-ui.card :title="__('erp.expense.reimbursement')" :description="__('erp.expense.reimbursement_hint')">
                <dl class="divide-y divide-border">
                    <div class="flex items-baseline justify-between gap-3 py-2">
                        <dt class="text-[0.8125rem] text-muted-foreground">{{ __('erp.amount') }}</dt>
                        <dd><x-ui.money :amount="$claim->amount" /></dd>
                    </div>
                    <div class="flex items-baseline justify-between gap-3 py-2">
                        <dt class="text-[0.8125rem] text-muted-foreground">{{ __('erp.expense.reimbursed_amount') }}</dt>
                        <dd><x-ui.money :amount="$claim->reimbursed_amount ?? '0'" :muted="(float) ($claim->reimbursed_amount ?? 0) == 0" /></dd>
                    </div>
                </dl>
                @if ($claim->reimbursements->isNotEmpty())
                    <div class="mt-3 divide-y divide-border border-t border-border pt-1">
                        @foreach ($claim->reimbursements as $reimbursement)
                            <div class="flex items-center justify-between gap-3 py-2 text-xs">
                                <span class="tabular-nums text-muted-foreground" dir="ltr">{{ $reimbursement->paid_on?->format('Y-m-d') }}</span>
                                <x-ui.money :amount="$reimbursement->amount" />
                                @if ($reimbursement->journal_id)
                                    <a href="{{ route('gl.journals.show', $reimbursement->journal_id) }}" wire:navigate class="font-medium text-[var(--brand-600)] hover:underline">#{{ $reimbursement->journal_id }}</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
