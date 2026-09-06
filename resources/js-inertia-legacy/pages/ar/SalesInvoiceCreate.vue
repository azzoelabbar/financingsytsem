<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import ConfirmDialog from '@/components/erp/ConfirmDialog.vue';
import PageHeader from '@/components/erp/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { api, ApiError } from '@/lib/api';
import type { CustomerDto } from '@/types/api';

const { t } = useI18n();
const customers = ref<CustomerDto[]>([]);
const saving = ref(false);
const confirmOpen = ref(false);
const form = reactive({
    customer_id: 0,
    invoice_date: new Date().toISOString().slice(0, 10),
    currency: 'LYD',
    exchange_rate: 1,
    number: '',
    revenue_account: '410101',
    net: 0,
    tax: 0,
    post: false,
});

onMounted(async () => {
    const res = await api.get<CustomerDto[]>('/api/v1/ar/customers', {
        per_page: 100,
    });
    customers.value = res.data;
});

async function submit(post: boolean): Promise<void> {
    saving.value = true;
    try {
        await api.post('/api/v1/ar/sales-invoices', {
            customer_id: form.customer_id,
            invoice_date: form.invoice_date,
            currency: form.currency,
            exchange_rate: form.exchange_rate,
            number: form.number || undefined,
            post,
            lines: [
                {
                    revenue_account: form.revenue_account,
                    net: form.net,
                    tax: form.tax,
                },
            ],
        });
        toast.success(post ? t('ar.posted') : t('ar.created'));
        router.visit('/ar/sales-invoices');
    } catch (e) {
        toast.error(e instanceof ApiError ? e.message : t('toasts.failed'));
    } finally {
        saving.value = false;
        confirmOpen.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">
        <PageHeader :title="t('ar.invoiceCreate')" />
        <form class="space-y-4" @submit.prevent="submit(false)">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2 sm:col-span-2">
                    <Label>{{ t('nav.customers') }}</Label>
                    <select
                        v-model.number="form.customer_id"
                        required
                        class="border-input bg-background w-full rounded-md border px-3 py-2 text-sm"
                    >
                        <option :value="0" disabled>—</option>
                        <option
                            v-for="c in customers"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.code }} — {{ c.name_ar }}
                        </option>
                    </select>
                </div>
                <div class="space-y-2">
                    <Label>{{ t('app.date') }}</Label>
                    <Input v-model="form.invoice_date" type="date" required />
                </div>
                <div class="space-y-2">
                    <Label>{{ t('app.number') }}</Label>
                    <Input v-model="form.number" />
                </div>
                <div class="space-y-2">
                    <Label>{{ t('app.currency') }}</Label>
                    <Input v-model="form.currency" maxlength="3" />
                </div>
                <div class="space-y-2">
                    <Label>FX</Label>
                    <Input v-model.number="form.exchange_rate" type="number" step="0.000001" />
                </div>
                <div class="space-y-2">
                    <Label>{{ t('nav.accounts') }}</Label>
                    <Input v-model="form.revenue_account" required />
                </div>
                <div class="space-y-2">
                    <Label>{{ t('app.amount') }}</Label>
                    <Input v-model.number="form.net" type="number" step="0.001" required />
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button type="submit" :disabled="saving">{{
                    t('app.save')
                }}</Button>
                <Button
                    type="button"
                    variant="secondary"
                    :disabled="saving"
                    @click="confirmOpen = true"
                >
                    {{ t('ar.postInvoice') }}
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    @click="router.visit('/ar/sales-invoices')"
                    >{{ t('app.cancel') }}</Button
                >
            </div>
        </form>

        <ConfirmDialog
            v-model:open="confirmOpen"
            :title="t('ar.postInvoice')"
            :description="t('ar.postInvoiceConfirm')"
            :confirm-label="t('app.post')"
            :loading="saving"
            danger
            @confirm="submit(true)"
        />
    </div>
</template>
