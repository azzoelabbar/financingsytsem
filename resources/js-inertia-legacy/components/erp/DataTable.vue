<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/erp/EmptyState.vue';
import LoadingBlock from '@/components/erp/LoadingBlock.vue';
import { Button } from '@/components/ui/button';
import type { PaginationMeta } from '@/types/api';

export type DataColumn = {
    key: string;
    label: string;
    align?: 'start' | 'end' | 'center';
    class?: string;
};

const props = withDefaults(
    defineProps<{
        columns: DataColumn[];
        rows: Record<string, unknown>[];
        loading?: boolean;
        emptyTitle?: string;
        emptyDescription?: string;
        pagination?: PaginationMeta | null;
    }>(),
    {
        loading: false,
        pagination: null,
    },
);

const emit = defineEmits<{
    page: [number];
    rowClick: [Record<string, unknown>];
}>();

const { t } = useI18n();

function alignClass(align?: DataColumn['align']): string {
    if (align === 'end') {
        return 'text-end';
    }
    if (align === 'center') {
        return 'text-center';
    }
    return 'text-start';
}
</script>

<template>
    <div class="border-border overflow-hidden rounded-lg border">
        <LoadingBlock v-if="loading" class="p-4" />
        <EmptyState
            v-else-if="rows.length === 0"
            class="border-0"
            :title="emptyTitle"
            :description="emptyDescription"
        />
        <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[640px] border-collapse text-sm">
                <thead class="bg-muted/40 border-border border-b">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'text-muted-foreground px-3 py-2.5 text-xs font-medium tracking-wide uppercase',
                                alignClass(col.align),
                                col.class,
                            ]"
                        >
                            {{ col.label }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, idx) in rows"
                        :key="(row.id as string | number) ?? idx"
                        class="border-border/70 hover:bg-muted/30 border-b last:border-b-0"
                        @click="emit('rowClick', row)"
                    >
                        <td
                            v-for="col in columns"
                            :key="col.key"
                            :class="[
                                'text-foreground px-3 py-2.5',
                                alignClass(col.align),
                                col.class,
                            ]"
                        >
                            <slot :name="`cell-${col.key}`" :row="row">
                                {{ row[col.key] }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            v-if="pagination && pagination.last_page > 1"
            class="border-border flex items-center justify-between border-t px-3 py-2"
        >
            <p class="text-muted-foreground text-xs">
                {{ pagination.total }} · {{ pagination.current_page }}/{{
                    pagination.last_page
                }}
            </p>
            <div class="flex gap-2">
                <Button
                    size="sm"
                    variant="outline"
                    :disabled="pagination.current_page <= 1"
                    @click="emit('page', pagination.current_page - 1)"
                >
                    {{ t('app.back') }}
                </Button>
                <Button
                    size="sm"
                    variant="outline"
                    :disabled="
                        pagination.current_page >= pagination.last_page
                    "
                    @click="emit('page', pagination.current_page + 1)"
                >
                    {{ t('app.view') }}
                </Button>
            </div>
        </div>
    </div>
</template>
