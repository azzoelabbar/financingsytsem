<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/erp/EmptyState.vue';
import PageHeader from '@/components/erp/PageHeader.vue';

const { t } = useI18n();
</script>

<template>
    <div>
        <PageHeader :title="t('app.create')" />
        <EmptyState :description="t('app.emptyHint')" />
    </div>
</template>
