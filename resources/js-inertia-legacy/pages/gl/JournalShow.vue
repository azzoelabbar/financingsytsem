<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import ErrorState from '@/components/erp/ErrorState.vue';
import LoadingBlock from '@/components/erp/LoadingBlock.vue';
import MoneyDisplay from '@/components/erp/MoneyDisplay.vue';
import PageHeader from '@/components/erp/PageHeader.vue';
import StatusBadge from '@/components/erp/StatusBadge.vue';
import { api, ApiError } from '@/lib/api';
import { useAccountingContextStore } from '@/stores/accountingContext';
import type { JournalDto } from '@/types/api';

const props = defineProps<{ id: string }>();
const { t } = useI18n();
const context = useAccountingContextStore();
const journal = ref<JournalDto | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

onMounted(async () => {
    try {
        const res = await api.get<JournalDto>(`/api/v1/gl/journals/${props.id}`);
        journal.value = res.data;
    } catch (e) {
        error.value = e instanceof ApiError ? e.message : t('app.error');
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="space-y-4">
        <PageHeader :title="`${t('gl.journals')} #${props.id}`">
            <template #actions>
                <StatusBadge v-if="journal" :status="journal.status" />
            </template>
        </PageHeader>
        <ErrorState v-if="error" :message="error" />
        <LoadingBlock v-else-if="loading" />
        <template v-else-if="journal">
            <div class="border-border grid gap-3 rounded-lg border p-4 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-muted-foreground">{{ t('app.number') }}</p>
                    <p class="font-medium">{{ journal.number || '—' }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('app.date') }}</p>
                    <p class="font-medium">{{ journal.journal_date }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('app.reference') }}</p>
                    <p class="font-medium">{{ journal.reference || journal.source }}</p>
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('app.debit') }}</p>
                    <MoneyDisplay :amount="journal.total_debit" :currency="context.currency" />
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('app.credit') }}</p>
                    <MoneyDisplay :amount="journal.total_credit" :currency="context.currency" />
                </div>
                <div>
                    <p class="text-muted-foreground">{{ t('app.status') }}</p>
                    <p class="font-semibold text-emerald-700 dark:text-emerald-400">
                        {{
                            Number(journal.total_debit) === Number(journal.total_credit)
                                ? t('app.balanced')
                                : t('app.unbalanced')
                        }}
                    </p>
                </div>
            </div>
            <div class="border-border overflow-x-auto rounded-lg border">
                <table class="w-full text-sm">
                    <thead class="bg-muted/40">
                        <tr>
                            <th class="px-3 py-2 text-start">{{ t('nav.accounts') }}</th>
                            <th class="px-3 py-2 text-start">{{ t('app.description') }}</th>
                            <th class="px-3 py-2 text-end">{{ t('app.debit') }}</th>
                            <th class="px-3 py-2 text-end">{{ t('app.credit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="line in journal.lines ?? []"
                            :key="line.id"
                            class="border-border border-t"
                        >
                            <td class="px-3 py-2">{{ line.account?.code || line.account_id }}</td>
                            <td class="px-3 py-2">{{ line.description }}</td>
                            <td class="px-3 py-2 text-end">
                                <MoneyDisplay :amount="line.debit" :currency="context.currency" />
                            </td>
                            <td class="px-3 py-2 text-end">
                                <MoneyDisplay :amount="line.credit" :currency="context.currency" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>
