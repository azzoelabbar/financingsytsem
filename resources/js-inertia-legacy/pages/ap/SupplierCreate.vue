<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import PageHeader from '@/components/erp/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { api, ApiError } from '@/lib/api';

const { t } = useI18n();
const saving = ref(false);
const form = reactive({ code: '', legal_name: '', currency: 'LYD' });

async function submit(): Promise<void> {
    saving.value = true;
    try {
        await api.post('/api/v1/ap/suppliers', form);
        toast.success(t('toasts.saved'));
        router.visit('/ap/suppliers');
    } catch (e) {
        toast.error(e instanceof ApiError ? e.message : t('toasts.failed'));
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-xl space-y-4">
        <PageHeader :title="t('ap.supplierCreate')" />
        <form class="space-y-4" @submit.prevent="submit">
            <div class="space-y-2">
                <Label>{{ t('app.code') }}</Label>
                <Input v-model="form.code" required />
            </div>
            <div class="space-y-2">
                <Label>{{ t('app.name') }}</Label>
                <Input v-model="form.legal_name" required />
            </div>
            <div class="space-y-2">
                <Label>{{ t('app.currency') }}</Label>
                <Input v-model="form.currency" maxlength="3" />
            </div>
            <Button type="submit" :disabled="saving">{{ t('app.save') }}</Button>
        </form>
    </div>
</template>
