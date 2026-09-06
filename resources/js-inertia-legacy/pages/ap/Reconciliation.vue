<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import ErrorState from '@/components/erp/ErrorState.vue';
import LoadingBlock from '@/components/erp/LoadingBlock.vue';
import PageHeader from '@/components/erp/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { useApiResource } from '@/composables/useApiResource';
import { useAccountingContextStore } from '@/stores/accountingContext';

const { t } = useI18n();
const context = useAccountingContextStore();
const { data, loading, error, refresh } = useApiResource<unknown>('/api/v1/ap/reconciliation');
const pretty = computed(() => JSON.stringify(data.value, null, 2));
</script>

<template>
    <div>
        <PageHeader :title="t('ap.reconciliation')">
            <template #actions>
                <div class="text-muted-foreground text-xs uppercase tracking-wide">
                    {{ t('context.basis') }}: {{ context.currentBook?.code ?? '—' }}
                </div>
                <Button variant="outline" size="sm" @click="refresh">{{ t('app.refresh') }}</Button>
            </template>
        </PageHeader>
        <ErrorState v-if="error" :message="String(error)" @retry="refresh" />
        <LoadingBlock v-else-if="loading" />
        <pre v-else class="border-border bg-muted/30 overflow-x-auto rounded-lg border p-4 text-xs leading-relaxed" dir="ltr">{{ pretty }}</pre>
    </div>
</template>
