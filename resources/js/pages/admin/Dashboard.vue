<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { 
    Users, 
    Package, 
    ClipboardCheck, 
    CalendarClock, 
    MapPin, 
    Bell,
    Clock,
    CheckCircle,
    XCircle,
    Plus
} from '@lucide/vue';
import admin from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                href: admin.dashboard(),
                title: 'Dashboard',
            },
        ],
    },
});

defineProps<{
    stats: {
        total_users: number;
        total_packages: number;
        total_bookings: number;
        pending_bookings: number;
        confirmed_bookings: number;
        cancelled_bookings: number;
        completed_bookings: number;
        total_tour_dates: number;
        total_pickup_locations: number;
        total_reminders: number;
        recent_bookings: Array<{
            id: number;
            user: { name: string; email: string };
            tour_date: { 
                package: { package_name: string };
                tour_date: string;
            };
            booking_status: string;
            number_of_guests: number;
            created_at: string;
        }>;
    };
}>();

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-700',
        confirmed: 'bg-blue-100 text-blue-700',
        cancelled: 'bg-red-100 text-red-700',
        completed: 'bg-green-100 text-green-700',
    };
    return colors[status as keyof typeof colors] || 'bg-neutral-100 text-neutral-600';
};

const getStatusIcon = (status: string) => {
    const icons = {
        pending: Clock,
        confirmed: CheckCircle,
        cancelled: XCircle,
        completed: CheckCircle,
    };
    return icons[status as keyof typeof icons] || Clock;
};

const statCards = [
    {
        title: 'Total Users',
        value: (props: any) => props.stats.total_users,
        icon: Users,
        route: admin.users.index().url,
        color: 'bg-blue-50 text-blue-600',
    },
    {
        title: 'Total Packages',
        value: (props: any) => props.stats.total_packages,
        icon: Package,
        route: admin.packages.index().url,
        color: 'bg-purple-50 text-purple-600',
    },
    {
        title: 'Total Bookings',
        value: (props: any) => props.stats.total_bookings,
        icon: ClipboardCheck,
        route: admin.bookings.index().url,
        color: 'bg-green-50 text-green-600',
    },
    {
        title: 'Pending Bookings',
        value: (props: any) => props.stats.pending_bookings,
        icon: Clock,
        route: admin.bookings.index().url + '?booking_status=pending',
        color: 'bg-yellow-50 text-yellow-600',
    },
    {
        title: 'Tour Dates',
        value: (props: any) => props.stats.total_tour_dates,
        icon: CalendarClock,
        route: admin.tourDates.index().url,
        color: 'bg-indigo-50 text-indigo-600',
    },
    {
        title: 'Pickup Locations',
        value: (props: any) => props.stats.total_pickup_locations,
        icon: MapPin,
        route: admin.pickupLocations.index().url,
        color: 'bg-red-50 text-red-600',
    },
    {
        title: 'Reminders',
        value: (props: any) => props.stats.total_reminders,
        icon: Bell,
        route: admin.reminders.index().url,
        color: 'bg-orange-50 text-orange-600',
    },
];

const bookingStatusStats = [
    {
        label: 'Pending',
        count: (props: any) => props.stats.pending_bookings,
        color: 'bg-yellow-100 text-yellow-700',
        route: admin.bookings.index().url + '?booking_status=pending',
    },
    {
        label: 'Confirmed',
        count: (props: any) => props.stats.confirmed_bookings,
        color: 'bg-blue-100 text-blue-700',
        route: admin.bookings.index().url + '?booking_status=confirmed',
    },
    {
        label: 'Cancelled',
        count: (props: any) => props.stats.cancelled_bookings,
        color: 'bg-red-100 text-red-700',
        route: admin.bookings.index().url + '?booking_status=cancelled',
    },
    {
        label: 'Completed',
        count: (props: any) => props.stats.completed_bookings,
        color: 'bg-green-100 text-green-700',
        route: admin.bookings.index().url + '?booking_status=completed',
    },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};
</script>

<template>
    <Head title="Dashboard" />
    <div class="px-4 py-6">
        <Heading title="Admin Dashboard" description="Overview of your tour booking system" />

        <!-- Stats Grid -->
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Link 
                v-for="card in statCards" 
                :key="card.title"
                :href="card.route"
                class="block transition-transform hover:scale-[1.02]"
            >
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">
                            {{ card.title }}
                        </CardTitle>
                        <component :is="card.icon" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ card.value($props) }}
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Booking Status Distribution -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-1">
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Booking Status</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <Link 
                        v-for="status in bookingStatusStats" 
                        :key="status.label"
                        :href="status.route"
                        class="flex items-center justify-between rounded-lg p-2 transition-colors hover:bg-muted/50"
                    >
                        <span class="flex items-center gap-2">
                            <span :class="['inline-block h-2 w-2 rounded-full', status.color.split(' ')[0]]"></span>
                            <span class="text-sm">{{ status.label }}</span>
                        </span>
                        <span class="text-sm font-medium">{{ status.count($props) }}</span>
                    </Link>
                </CardContent>
            </Card>

            <!-- Recent Bookings -->
            <Card class="lg:col-span-2">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-medium">Recent Bookings</CardTitle>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="admin.bookings.index().url">View All</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="stats.recent_bookings.length > 0" class="space-y-4">
                        <div 
                            v-for="booking in stats.recent_bookings" 
                            :key="booking.id"
                            class="flex flex-col space-y-2 rounded-lg border p-3 transition-colors hover:bg-muted/50 sm:flex-row sm:items-center sm:justify-between sm:space-y-0"
                        >
                            <div class="flex flex-col">
                                <span class="font-medium">{{ booking.user.name }}</span>
                                <span class="text-sm text-muted-foreground">
                                    {{ booking.tour_date.package.package_name }}
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatDate(booking.tour_date.tour_date) }} • {{ booking.number_of_guests }} guests
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span 
                                    :class="[
                                        'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                                        getStatusColor(booking.booking_status)
                                    ]"
                                >
                                    {{ booking.booking_status.charAt(0).toUpperCase() + booking.booking_status.slice(1) }}
                                </span>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatTime(booking.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-6 text-center text-muted-foreground">
                        No recent bookings
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6">
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm font-medium">Quick Actions</CardTitle>
                </CardHeader>
                <CardContent class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    <Button as-child variant="outline" class="flex h-auto flex-col items-center gap-2 py-4">
                        <Link :href="admin.packages.create().url" class="flex flex-col items-center gap-2">
                            <Package class="h-5 w-5" />
                            <span class="text-xs">New Package</span>
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="flex h-auto flex-col items-center gap-2 py-4">
                        <Link :href="admin.bookings.create().url" class="flex flex-col items-center gap-2">
                            <ClipboardCheck class="h-5 w-5" />
                            <span class="text-xs">New Booking</span>
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="flex h-auto flex-col items-center gap-2 py-4">
                        <Link :href="admin.tourDates.create().url" class="flex flex-col items-center gap-2">
                            <CalendarClock class="h-5 w-5" />
                            <span class="text-xs">Add Tour Date</span>
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="flex h-auto flex-col items-center gap-2 py-4">
                        <Link :href="admin.users.create().url" class="flex flex-col items-center gap-2">
                            <Users class="h-5 w-5" />
                            <span class="text-xs">Add User</span>
                        </Link>
                    </Button>
                    <Button as-child variant="outline" class="flex h-auto flex-col items-center gap-2 py-4">
                        <Link :href="admin.pickupLocations.create().url" class="flex flex-col items-center gap-2">
                            <MapPin class="h-5 w-5" />
                            <span class="text-xs">Add Location</span>
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>