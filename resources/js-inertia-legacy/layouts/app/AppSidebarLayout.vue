<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ContextBar from '@/components/erp/ContextBar.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="min-w-0 overflow-x-clip">
            <ContextBar />
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <div class="flex-1 p-4 md:p-6">
                <slot />
            </div>
        </AppContent>
        <Toaster position="top-center" rich-colors />
    </AppShell>
</template>
