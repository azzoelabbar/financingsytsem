<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/erp/EmptyState.vue';
import PageHeader from '@/components/erp/PageHeader.vue';

const props = defineProps<{ id: string }>();
const { t } = useI18n();
</script>

<template>
    <div>
        <PageHeader :title="`${t('app.view')} #${props.id}`" />
        <EmptyState :description="t('app.emptyHint')" />
    </div>
</template>
