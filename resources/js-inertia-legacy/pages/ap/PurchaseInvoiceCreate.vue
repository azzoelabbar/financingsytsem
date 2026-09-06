<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import PageHeader from '@/components/erp/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { api, ApiError } from '@/lib/api';
import type { SupplierDto } from '@/types/api';

const { t } = useI18n();
const suppliers = ref<SupplierDto[]>([]);
const saving = ref(false);
const form = reactive({
    supplier_id: 0,
    invoice_date: new Date().toISOString().slice(0, 10),
    supplier_invoice_number: '',
    expense_account: '620201',
    net: 0,
    tax: 0,
});

onMounted(async () => {
    const res = await api.get<SupplierDto[]>('/api/v1/ap/suppliers', {
        per_page: 100,
    });
    suppliers.value = res.data;
});

async function submit(): Promise<void> {
    saving.value = true;
    try {
        await api.post('/api/v1/ap/purchase-invoices', {
            supplier_id: form.supplier_id,
            invoice_date: form.invoice_date,
            supplier_invoice_number: form.supplier_invoice_number || undefined,
            post: true,
            lines: [
                {
                    expense_account: form.expense_account,
                    net: form.net,
                    tax: form.tax,
                },
            ],
        });
        toast.success(t('toasts.posted'));
        router.visit('/ap/purchase-invoices');
    } catch (e) {
        toast.error(e instanceof ApiError ? e.message : t('toasts.failed'));
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-4">
        <PageHeader :title="t('ap.invoiceCreate')" />
        <form class="grid gap-4 sm:grid-cols-2" @submit.prevent="submit">
            <div class="space-y-2 sm:col-span-2">
                <Label>{{ t('nav.suppliers') }}</Label>
                <select
                    v-model.number="form.supplier_id"
                    required
                    class="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                >
                    <option :value="0" disabled>—</option>
                    <option v-for="s in suppliers" :key="s.id" :value="s.id">
                        {{ s.code }} — {{ s.legal_name }}
                    </option>
                </select>
            </div>
            <div class="space-y-2">
                <Label>{{ t('app.date') }}</Label>
                <Input v-model="form.invoice_date" type="date" required />
            </div>
            <div class="space-y-2">
                <Label>{{ t('app.reference') }}</Label>
                <Input v-model="form.supplier_invoice_number" />
            </div>
            <div class="space-y-2">
                <Label>{{ t('nav.accounts') }}</Label>
                <Input v-model="form.expense_account" required />
            </div>
            <div class="space-y-2">
                <Label>{{ t('app.amount') }}</Label>
                <Input v-model.number="form.net" type="number" step="0.001" required />
            </div>
            <div class="sm:col-span-2">
                <Button type="submit" :disabled="saving">{{ t('app.post') }}</Button>
            </div>
        </form>
    </div>
</template>
