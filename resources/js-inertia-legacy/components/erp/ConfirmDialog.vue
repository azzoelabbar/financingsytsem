<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const open = defineModel<boolean>('open', { default: false });

withDefaults(
    defineProps<{
        title: string;
        description: string;
        confirmLabel?: string;
        danger?: boolean;
        loading?: boolean;
    }>(),
    {
        danger: false,
        loading: false,
    },
);

const emit = defineEmits<{ confirm: [] }>();
const { t } = useI18n();
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="outline" @click="open = false">
                    {{ t('app.cancel') }}
                </Button>
                <Button
                    :variant="danger ? 'destructive' : 'default'"
                    :disabled="loading"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel || t('app.confirm') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
