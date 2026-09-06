<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import ErrorState from '@/components/erp/ErrorState.vue';
import LoadingBlock from '@/components/erp/LoadingBlock.vue';
import MoneyDisplay from '@/components/erp/MoneyDisplay.vue';
import PageHeader from '@/components/erp/PageHeader.vue';
import StatusBadge from '@/components/erp/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { api, ApiError } from '@/lib/api';
import { useAccountingContextStore } from '@/stores/accountingContext';

const { t } = useI18n();
const context = useAccountingContextStore();

const loading = ref(false);
const error = ref<string | null>(null);
const pack = ref<Record<string, unknown> | null>(null);
const arAging = ref<Record<string, unknown> | null>(null);
const apAging = ref<Record<string, unknown> | null>(null);
const arRecon = ref<Record<string, unknown> | null>(null);
const apRecon = ref<Record<string, unknown> | null>(null);

async function load(): Promise<void> {
    if (!context.companyId || !context.bookId) {
        return;
    }
    loading.value = true;
    error.value = null;
    try {
        const [packRes, arAgingRes, apAgingRes, arReconRes, apReconRes] =
            await Promise.all([
                api.get<Record<string, unknown>>(
                    '/api/v1/reports/management-pack',
                ),
                api.get<Record<string, unknown>>('/api/v1/ar/aging'),
                api.get<Record<string, unknown>>('/api/v1/ap/aging'),
                api.get<Record<string, unknown>>('/api/v1/ar/reconciliation'),
                api.get<Record<string, unknown>>('/api/v1/ap/reconciliation'),
            ]);
        pack.value = packRes.data;
        arAging.value = arAgingRes.data;
        apAging.value = apAgingRes.data;
        arRecon.value = arReconRes.data;
        apRecon.value = apReconRes.data;
    } catch (e) {
        error.value = e instanceof ApiError ? e.message : t('app.error');
    } finally {
        loading.value = false;
    }
}

watch(
    () => [context.companyId, context.bookId, context.revision],
    () => {
        void load();
    },
    { immediate: true },
);

const bs = computed(
    () => (pack.value?.balance_sheet as Record<string, unknown>) ?? {},
);
const pl = computed(
    () =>
        (pack.value?.income_statement as Record<string, unknown>) ??
        (pack.value?.profit_loss as Record<string, unknown>) ??
        {},
);

const kpis = computed(() => [
    {
        label: t('dashboard.assets'),
        value: bs.value.total_assets ?? bs.value.assets,
    },
    {
        label: t('dashboard.liabilities'),
        value: bs.value.total_liabilities ?? bs.value.liabilities,
    },
    {
        label: t('dashboard.equity'),
        value: bs.value.total_equity ?? bs.value.equity,
    },
    {
        label: t('dashboard.revenue'),
        value: pl.value.revenue ?? pl.value.total_revenue,
    },
    {
        label: t('dashboard.expenses'),
        value: pl.value.expenses ?? pl.value.total_expenses,
    },
    {
        label: t('dashboard.netProfit'),
        value: pl.value.net_profit ?? pl.value.net_income,
    },
    { label: t('dashboard.receivables'), value: arAging.value?.total },
    { label: t('dashboard.payables'), value: apAging.value?.total },
]);
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <div class="space-y-6">
        <PageHeader
            :title="t('dashboard.title')"
            :description="t('dashboard.subtitle')"
        >
            <template #actions>
                <div
                    class="text-muted-foreground text-xs font-medium tracking-wide uppercase"
                >
                    {{ t('context.basis') }}:
                    {{ context.currentBook?.code ?? '—' }}
                </div>
                <Button variant="outline" size="sm" @click="load">
                    {{ t('app.refresh') }}
                </Button>
            </template>
        </PageHeader>

        <p
            v-if="!context.companyId"
            class="text-muted-foreground text-sm"
        >
            {{ t('dashboard.selectContext') }}
        </p>

        <ErrorState v-else-if="error" :message="error" @retry="load" />
        <LoadingBlock v-else-if="loading" :rows="8" />

        <template v-else>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="kpi in kpis"
                    :key="kpi.label"
                    class="border-border rounded-lg border px-4 py-3"
                >
                    <p class="text-muted-foreground text-xs font-medium">
                        {{ kpi.label }}
                    </p>
                    <p class="mt-2 text-xl font-semibold tracking-tight">
                        <MoneyDisplay
                            :amount="kpi.value as string | number | null"
                            :currency="context.currency"
                        />
                    </p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <section class="border-border rounded-lg border p-4">
                    <h2 class="mb-3 text-sm font-semibold">
                        {{ t('dashboard.arAging') }}
                    </h2>
                    <pre
                        class="text-muted-foreground overflow-x-auto text-xs"
                        dir="ltr"
                        >{{ JSON.stringify(arAging, null, 2) }}</pre
                    >
                </section>
                <section class="border-border rounded-lg border p-4">
                    <h2 class="mb-3 text-sm font-semibold">
                        {{ t('dashboard.apAging') }}
                    </h2>
                    <pre
                        class="text-muted-foreground overflow-x-auto text-xs"
                        dir="ltr"
                        >{{ JSON.stringify(apAging, null, 2) }}</pre
                    >
                </section>
                <section class="border-border rounded-lg border p-4">
                    <h2 class="mb-3 text-sm font-semibold">
                        {{ t('dashboard.reconciliation') }} · AR
                    </h2>
                    <div class="flex items-center gap-2">
                        <StatusBadge
                            :status="
                                arRecon?.passed ? 'posted' : 'rejected'
                            "
                        />
                        <span class="text-sm">{{
                            String(arRecon?.message ?? '')
                        }}</span>
                    </div>
                </section>
                <section class="border-border rounded-lg border p-4">
                    <h2 class="mb-3 text-sm font-semibold">
                        {{ t('dashboard.reconciliation') }} · AP
                    </h2>
                    <div class="flex items-center gap-2">
                        <StatusBadge
                            :status="
                                apRecon?.passed ? 'posted' : 'rejected'
                            "
                        />
                        <span class="text-sm">{{
                            String(apRecon?.message ?? '')
                        }}</span>
                    </div>
                </section>
                <section class="border-border rounded-lg border p-4 lg:col-span-2">
                    <h2 class="mb-3 text-sm font-semibold">
                        {{ t('dashboard.periodStatus') }}
                    </h2>
                    <p class="text-sm">
                        <template v-if="context.currentPeriod">
                            #{{ context.currentPeriod.period_no }} —
                            <StatusBadge
                                :status="context.currentPeriod.status"
                            />
                        </template>
                        <template v-else>—</template>
                    </p>
                </section>
            </div>
        </template>
    </div>
</template>
