import { storeToRefs } from 'pinia';
import { onMounted, ref, watch, type Ref } from 'vue';
import { api, ApiError } from '@/lib/api';
import { useAccountingContextStore } from '@/stores/accountingContext';
import type { PaginationMeta } from '@/types/api';

type Options = {
    immediate?: boolean;
    query?: Ref<Record<string, string | number | boolean | undefined | null>>;
};

export function useApiResource<T>(path: string, options: Options = {}) {
    const data = ref<T | null>(null) as Ref<T | null>;
    const meta = ref<{ pagination?: PaginationMeta } | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);
    const context = useAccountingContextStore();
    const { companyId, bookId, revision } = storeToRefs(context);

    async function refresh(): Promise<void> {
        if (!companyId.value || !bookId.value) {
            data.value = null;
            return;
        }
        loading.value = true;
        error.value = null;
        try {
            const res = await api.get<T>(path, options.query?.value);
            data.value = res.data;
            meta.value = res.meta ?? null;
        } catch (e) {
            error.value =
                e instanceof ApiError ? e.message : 'Request failed';
            data.value = null;
        } finally {
            loading.value = false;
        }
    }

    onMounted(() => {
        if (options.immediate !== false) {
            void refresh();
        }
    });

    watch([companyId, bookId, revision], () => {
        void refresh();
    });

    if (options.query) {
        watch(options.query, () => {
            void refresh();
        }, { deep: true });
    }

    return { data, meta, loading, error, refresh };
}
