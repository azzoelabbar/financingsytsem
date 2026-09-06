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
const { data, meta, loading, error, refresh } = useApiResource<Record<string, unknown>[]>('/api/v1/ap/suppliers');
const rows = computed(() => (Array.isArray(data.value) ? data.value : []));
const columns = computed(() => [
    { key: 'code', label: t('app.code') },
    { key: 'legal_name', label: t('app.name') },
    { key: 'currency', label: t('app.currency') }
]);
</script>

<template>
    <div>
        <PageHeader :title="t('ap.suppliers')">
            <template #actions>
                <Button variant="outline" size="sm" @click="refresh">{{ t('app.refresh') }}</Button>
                <Button size="sm" as-child><Link href="/ap/suppliers/create">{{ t('app.create') }}</Link></Button>
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
