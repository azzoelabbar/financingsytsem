<script setup lang="ts">
withDefaults(defineProps<{ rows?: number }>(), { rows: 5 });
</script>

<template>
    <div class="space-y-3" aria-hidden="true">
        <div
            v-for="i in rows"
            :key="i"
            class="bg-muted/70 h-10 animate-pulse rounded-md"
        />
    </div>
</template>
