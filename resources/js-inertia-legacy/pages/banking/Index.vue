<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/erp/EmptyState.vue';
import PageHeader from '@/components/erp/PageHeader.vue';

const { t } = useI18n();
</script>

<template>
    <div>
        <PageHeader :title="t('banking.title')" />
        <EmptyState :title="t('banking.title')" :description="t('banking.apiPending')" />
    </div>
</template>
