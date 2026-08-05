<!-- resources/js/components/AppSidebar.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    Package, 
    Calendar, 
    Users, 
    Ship,
    CreditCard,
    TrendingUp,
    Settings,
} from '@lucide/vue';
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
    } else {
        return '/operator/dashboard';
    }
};

// Admin & Operator navigation items
const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: getDashboardRoute(),
            icon: LayoutGrid,
        },
    ];

    // Admin specific links
    if (user.value?.role === 'admin') {
        items.push(
            { title: 'Tours', href: '/admin/tours', icon: Package },
            { title: 'Bookings', href: '/admin/bookings', icon: Calendar },
            { title: 'Users', href: '/admin/users', icon: Users },
            { title: 'Payments', href: '/admin/payments', icon: CreditCard },
            { title: 'Analytics', href: '/admin/analytics', icon: TrendingUp },
        );
    }

    // Operator specific links
    if (user.value?.role === 'operator') {
        items.push(
            { title: 'My Tours', href: '/operator/tours', icon: Package },
            { title: 'Bookings', href: '/operator/bookings', icon: Calendar },
            { title: 'My Boats', href: '/operator/boats', icon: Ship },
        );
    }

    // Settings for both admin and operator
    items.push(
        { title: 'Settings', href: '/settings', icon: Settings },
    );

    return items;
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