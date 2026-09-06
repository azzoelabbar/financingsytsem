<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import DataTable from '@/components/erp/DataTable.vue';
import ErrorState from '@/components/erp/ErrorState.vue';
import PageHeader from '@/components/erp/PageHeader.vue';
import StatusBadge from '@/components/erp/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { useApiResource } from '@/composables/useApiResource';

const { t } = useI18n();
const { data, meta, loading, error, refresh } = useApiResource<Record<string, unknown>[]>('/api/v1/gl/general-ledger');
const rows = computed(() => (Array.isArray(data.value) ? data.value : []));
const columns = computed(() => [
    { key: 'id', label: t('app.number') },
    { key: 'account_id', label: t('app.code') },
    { key: 'debit', label: t('app.debit'), align: 'end' as const },
    { key: 'credit', label: t('app.credit'), align: 'end' as const }
]);
</script>

<template>
    <div>
        <PageHeader :title="t('gl.generalLedger')">
            <template #actions>
                <Button variant="outline" size="sm" @click="refresh">{{ t('app.refresh') }}</Button>
                
            </template>
        </PageHeader>
        <ErrorState v-if="error" :message="error" @retry="refresh" />
        <DataTable v-else :columns="columns" :rows="rows" :loading="loading" :pagination="meta?.pagination">
            <template #cell-status="{ row }">
                <StatusBadge :status="String(row.status ?? '')" />
            </template>
        </DataTable>
    </div>
</template>
