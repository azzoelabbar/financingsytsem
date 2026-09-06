<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        actionLabel?: string;
    }>(),
    {},
);

const emit = defineEmits<{ action: [] }>();
const { t } = useI18n();
</script>

<template>
    <div
        class="border-border/80 bg-card flex flex-col items-center justify-center rounded-lg border border-dashed px-6 py-16 text-center"
    >
        <p class="text-foreground text-base font-medium">
            {{ title || t('app.empty') }}
        </p>
        <p class="text-muted-foreground mt-1 max-w-md text-sm">
            {{ description || t('app.emptyHint') }}
        </p>
        <Button
            v-if="actionLabel"
            class="mt-4"
            size="sm"
            @click="emit('action')"
        >
            {{ actionLabel }}
        </Button>
        <slot />
    </div>
</template>
