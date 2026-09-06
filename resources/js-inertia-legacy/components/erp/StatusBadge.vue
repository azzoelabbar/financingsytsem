<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{ status: string }>();
const { t } = useI18n();

const label = computed(() => {
    const key = `status.${props.status}`;
    const translated = t(key);
    return translated === key ? props.status : translated;
});

const variant = computed(() => {
    switch (props.status) {
        case 'posted':
        case 'approved':
        case 'active':
        case 'reimbursed':
            return 'default' as const;
        case 'draft':
        case 'pending':
        case 'submitted':
        case 'open':
            return 'secondary' as const;
        case 'reversed':
        case 'void':
        case 'rejected':
        case 'hard_closed':
        case 'locked':
            return 'destructive' as const;
        default:
            return 'outline' as const;
    }
});
</script>

<template>
    <Badge :variant="variant" class="font-medium">{{ label }}</Badge>
</template>
