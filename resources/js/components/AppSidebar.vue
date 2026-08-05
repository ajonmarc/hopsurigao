<!-- resources/js/components/AppSidebar.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LayoutGrid } from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import SidebarLogout from '@/components/SidebarLogout.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

// Get the correct dashboard route based on user role
const getDashboardRoute = () => {
    if (!user.value) return dashboard();
    
    if (user.value.role === 'admin') {
        return '/admin/dashboard';
    } else if (user.value.role === 'operator') {
        return '/operator/dashboard';
    } else {
        return '/user/dashboard';
    }
};

const mainNavItems = computed<NavItem[]>(() => {
    return [
        {
            title: 'Dashboard',
            href: getDashboardRoute(),
            icon: LayoutGrid,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="offcanvas" variant="sidebar">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="getDashboardRoute()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <SidebarLogout />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>