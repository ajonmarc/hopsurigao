<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    LogOut,
    LayoutGrid,
    Users,
    MapPin,
    ClipboardCheck,
    Sailboat,
    CalendarClock,
    ClipboardList,
    Package,
    Bell,
    Clock,
    QrCode,
} from '@lucide/vue';
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

// Admin: full control over every table in the ERD
const adminNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    { title: 'Users', href: '/admin/users', icon: Users },
    {
        title: 'Packages',
        href: '/admin/packages',
        icon: Package,
        isActive: true,
        items: [
            { title: 'Packages', href: '/admin/packages', icon: Package },
            { title: 'Inclusions', href: '/admin/inclusions', icon: ClipboardList },
            { title: 'Tour Dates', href: '/admin/tour-dates', icon: CalendarClock },
            { title: 'Times', href: '/admin/times', icon: Clock },
        ],
    },
    { title: 'Pickup Locations', href: '/admin/pickup-locations', icon: MapPin },
    {
        title: 'Pickup Schedules',
        href: '/admin/pickup-schedules',
        icon: CalendarClock
    },
    { title: 'Bookings', href: '/admin/bookings', icon: ClipboardCheck },
    { title: 'Reminders', href: '/admin/reminders', icon: Bell },
    { title: 'QR Codes', href: '/admin/bookings-scan', icon: QrCode },
];

// Operator: manages their own tour packages, schedules, and incoming bookings
const operatorNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard', icon: LayoutGrid },
    { title: 'Packages', href: '/admin/packages', icon: Sailboat },
    { title: 'Tour Dates', href: '/admin/tour-dates', icon: CalendarClock },
    { title: 'Bookings', href: '/admin/bookings', icon: ClipboardCheck },
    { title: 'Reminders', href: '/admin/reminders', icon: Bell },
    { title: 'QR Codes', href: '/admin/bookings-scan', icon: QrCode },
];

// Tourist: browses packages and manages their own bookings
const touristNavItems: NavItem[] = [
    { title: 'Dashboard', href: '/tourist/dashboard', icon: LayoutGrid },
    { title: 'Browse Packages', href: '/tourist/packages', icon: Sailboat },
    { title: 'My Bookings', href: '/tourist/bookings', icon: ClipboardCheck },
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