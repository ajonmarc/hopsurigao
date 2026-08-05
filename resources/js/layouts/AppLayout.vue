<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue'; // i can chnange it to AppHeaderLayout.vue if i want to use the header layout instead of the sidebar layout
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
