<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import ErrorState from '@/components/erp/ErrorState.vue';
import PageHeader from '@/components/erp/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { api, ApiError } from '@/lib/api';

const { t } = useI18n();
const saving = ref(false);
const error = ref<string | null>(null);
const form = reactive({
    code: '',
    name_ar: '',
    currency: 'LYD',
});

async function submit(): Promise<void> {
    saving.value = true;
    error.value = null;
    try {
        await api.post('/api/v1/ar/customers', form);
        toast.success(t('ar.created'));
        router.visit('/ar/customers');
    } catch (e) {
        error.value = e instanceof ApiError ? e.message : t('toasts.failed');
        toast.error(error.value);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="mx-auto max-w-xl">
        <PageHeader :title="t('ar.customerCreate')" />
        <ErrorState v-if="error" :message="error" @retry="submit" />
        <form class="space-y-4" @submit.prevent="submit">
            <div class="space-y-2">
                <Label for="code">{{ t('app.code') }}</Label>
                <Input id="code" v-model="form.code" required />
            </div>
            <div class="space-y-2">
                <Label for="name_ar">{{ t('app.name') }}</Label>
                <Input id="name_ar" v-model="form.name_ar" required />
            </div>
            <div class="space-y-2">
                <Label for="currency">{{ t('app.currency') }}</Label>
                <Input id="currency" v-model="form.currency" maxlength="3" />
            </div>
            <div class="flex gap-2">
                <Button type="submit" :disabled="saving">{{
                    t('app.save')
                }}</Button>
                <Button
                    type="button"
                    variant="outline"
                    @click="router.visit('/ar/customers')"
                    >{{ t('app.cancel') }}</Button
                >
            </div>
        </form>
    </div>
</template>
