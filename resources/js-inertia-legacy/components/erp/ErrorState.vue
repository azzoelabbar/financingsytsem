<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';

defineProps<{ message?: string }>();
const emit = defineEmits<{ retry: [] }>();
const { t } = useI18n();
</script>

<template>
    <div
        class="border-destructive/30 bg-destructive/5 text-destructive flex flex-col items-start gap-3 rounded-lg border px-4 py-5"
        role="alert"
    >
        <p class="text-sm font-medium">{{ message || t('app.error') }}</p>
        <Button variant="outline" size="sm" @click="emit('retry')">
            {{ t('app.retry') }}
        </Button>
    </div>
</template>
