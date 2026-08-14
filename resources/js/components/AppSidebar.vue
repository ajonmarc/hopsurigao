<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, LayoutGrid, Users, Anchor, MapPin, ClipboardCheck, CreditCard, FileBarChart, Activity, Sailboat, CalendarClock, CloudSun, ClipboardList } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { logout } from '@/routes';
import { useRole } from '@/composables/useRole';
import type { NavItem } from '@/types';

const { isAdmin, isOperator, isTourist } = useRole();

const homeHref = computed(() => {
    if (isAdmin.value) return '/admin/dashboard';
    if (isOperator.value) return '/operator/dashboard';
    if (isTourist.value) return '/tourist/dashboard';
    return '/';
});

// Operator-scoped nav (boats, schedules, bookings, weather)
const operatorNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/operator/dashboard', icon: LayoutGrid },
    { title: 'Boats', href: '/operator/boats', icon: Sailboat },
    { title: 'Schedules', href: '/operator/schedules', icon: CalendarClock },
    { title: 'Bookings', href: '/operator/bookings', icon: ClipboardList },
    { title: 'Weather', href: '/operator/weather', icon: CloudSun },
];

// Tourist-scoped nav (trips, destinations, payments)
const touristNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/tourist/dashboard', icon: LayoutGrid },
    { title: 'Destinations', href: '/tourist/destinations', icon: MapPin },
    { title: 'My Bookings', href: '/tourist/bookings', icon: ClipboardList },
    { title: 'Payments', href: '/tourist/payments', icon: CreditCard },
];

// Admin nav = own tools + full access to Operator & Tourist links
const adminNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    {
        title: 'User Management',
        href: '/admin/users',
        icon: Users,
        isActive: true,
        items: [
            { title: 'Users', href: '/admin/users', icon: Users },
            { title: 'Roles', href: '/admin/roles', icon: Anchor },
            { title: 'Permissions', href: '/admin/permissions', icon: ClipboardCheck },
        ],
    },
    { title: 'Reports', href: '/admin/reports', icon: FileBarChart },
    { title: 'Activity Logs', href: '/admin/activity', icon: Activity },
];

const handleLogout = () => {
    router.flushAll();
};
</script>

<template>
    <Sidebar collapsible="offcanvas" variant="sidebar">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="homeHref">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>
        <SidebarContent>
            <!-- Admin: full access to Admin, Operator, and Tourist sections -->
            <template v-if="isAdmin">
                <NavMain label="Administrator" :items="adminNavItems" />
                <NavMain label="Operator" :items="operatorNavItems" />
                <NavMain label="Tourist" :items="touristNavItems" />
            </template>

            <!-- Operator -->
            <template v-else-if="isOperator">
                <NavMain label="Operator" :items="operatorNavItems" />
            </template>

            <!-- Tourist -->
            <template v-else-if="isTourist">
                <NavMain label="Tourist" :items="touristNavItems" />
            </template>
        </SidebarContent>

        <SidebarFooter>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton as-child>
                        <Link class="block w-full cursor-pointer" :href="logout()" @click="handleLogout" as="button"
                            data-test="logout-button">
                            <LogOut class="mr-2 h-4 w-4" />
                            <span>Log out</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>