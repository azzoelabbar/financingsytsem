<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        amount: string | number | null | undefined;
        currency?: string;
        class?: string;
    }>(),
    { currency: 'LYD' },
);

const { locale } = useI18n();
const formatted = computed(() =>
    formatMoney(props.amount, props.currency, locale.value),
);
</script>

<template>
    <span
        :class="
            cn(
                'font-variant-numeric inline-block tabular-nums tracking-tight',
                props.class,
            )
        "
        dir="ltr"
    >
        {{ formatted }}
    </span>
</template>
