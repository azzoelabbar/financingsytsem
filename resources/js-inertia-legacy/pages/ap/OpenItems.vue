<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/erp/EmptyState.vue';
import PageHeader from '@/components/erp/PageHeader.vue';

const { t } = useI18n();
</script>

<template>
    <div>
        <PageHeader :title="t('nav.apOpenItems')" />
        <EmptyState :title="t('nav.apOpenItems')" :description="t('app.emptyHint')" />
    </div>
</template>
