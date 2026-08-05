<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();

// Choose layout based on user role
const LayoutComponent = computed(() => {
    if (!user.value) return AppSidebarLayout;
    
    // Admin and Operator use sidebar layout
    if (user.value.role === 'admin' || user.value.role === 'operator') {
        return AppSidebarLayout;
    }
    
    // User/Tourist uses header layout
    return AppHeaderLayout;
});
</script>

<template>
    <component :is="LayoutComponent" :breadcrumbs="breadcrumbs">
        <slot />
    </component>
</template>