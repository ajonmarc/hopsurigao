<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MapPin, ClipboardList, Sailboat, CalendarClock, CheckCircle, XCircle, Clock, Package, ClipboardCheck } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle,  } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes/tourist';
import tourist from '@/routes/tourist';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                href: dashboard(),
                title: 'Dashboard',
            },
        ],
    },
});

const props = defineProps<{
    stats: {
        total_bookings: number;
        upcoming_bookings: number;
        completed_bookings: number;
        cancelled_bookings: number;
        recent_bookings: Array<{
            id: number;
            package_name: string;
            package_image: string | null;
            tour_date: string;
            booking_status: string;
            number_of_guests: number;
            pickup_location: string | null;
            created_at: string;
        }>;
    };
    user: {
        name: string;
        email: string;
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const statsCards = [
    {
        key: 'upcoming',
        title: 'Upcoming Bookings',
        value: props.stats.upcoming_bookings,
        icon: CalendarClock,
        color: 'text-blue-600',
        bgColor: 'bg-blue-50',
    },
    {
        key: 'completed',
        title: 'Completed Trips',
        value: props.stats.completed_bookings,
        icon: CheckCircle,
        color: 'text-green-600',
        bgColor: 'bg-green-50',
    },
    {
        key: 'cancelled',
        title: 'Cancelled',
        value: props.stats.cancelled_bookings,
        icon: XCircle,
        color: 'text-red-600',
        bgColor: 'bg-red-50',
    },
    {
        key: 'total',
        title: 'Total Bookings',
        value: props.stats.total_bookings,
        icon: ClipboardList,
        color: 'text-purple-600',
        bgColor: 'bg-purple-50',
    },
];

const quickActions = [
    {
        title: 'Browse Packages',
        description: 'Find your next adventure',
        icon: Sailboat,
        route: tourist.packages.index().url,
        color: 'bg-blue-50 text-blue-600',
    },
    {
        title: 'My Bookings',
        description: 'View your trip history',
        icon: ClipboardCheck,
        route: tourist.bookings.index().url,
        color: 'bg-green-50 text-green-600',
    },
];
</script>

<template>
    <Head title="Dashboard" />
    <div class="px-4 py-6">
        <!-- Hero Section -->
        <div class="relative overflow-hidden rounded-xl">
            <div
                class="h-48 bg-cover bg-center sm:h-56"
                style="background-image: url('/images/auth-bg.jpg')"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-sidebar/90 via-sidebar/40 to-sidebar/20" />
            <div class="absolute inset-0 flex flex-col justify-end p-6 text-sidebar-foreground">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Welcome back, {{ user.name }}!
                </h1>
                <p class="mt-1 text-sm text-sidebar-foreground/80">
                    Ready for your next island adventure?
                </p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card v-for="stat in statsCards" :key="stat.key">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ stat.title }}
                    </CardTitle>
                    <div :class="['rounded-full p-1.5', stat.bgColor]">
                        <component :is="stat.icon" :class="['h-4 w-4', stat.color]" />
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stat.value }}</div>
                </CardContent>
            </Card>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <Link
                v-for="action in quickActions"
                :key="action.title"
                :href="action.route"
                class="block transition-transform hover:scale-[1.02]"
            >
                <Card class="cursor-pointer hover:shadow-md">
                    <CardContent class="flex items-center gap-4 p-6">
                        <div :class="['rounded-full p-3', action.color]">
                            <component :is="action.icon" class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="font-medium">{{ action.title }}</h3>
                            <p class="text-sm text-muted-foreground">{{ action.description }}</p>
                        </div>
                    </CardContent>
                </Card>
            </Link>
        </div>

        <!-- Recent Bookings -->
        <div class="mt-6">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle class="text-sm font-medium">Recent Bookings</CardTitle>
                    <Button as-child variant="ghost" size="sm">
                        <Link :href="tourist.bookings.index().url">View All</Link>
                    </Button>
                </CardHeader>
                <CardContent>
                    <div v-if="stats.recent_bookings.length > 0" class="space-y-4">
                        <div
                            v-for="booking in stats.recent_bookings"
                            :key="booking.id"
                            class="flex flex-col space-y-2 rounded-lg border p-4 transition-colors hover:bg-muted/50 sm:flex-row sm:items-center sm:justify-between sm:space-y-0"
                        >
                            <div class="flex items-center gap-4">
                                <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md bg-muted">
                                    <img
                                        v-if="booking.package_image"
                                        :src="`/storage/${booking.package_image}`"
                                        :alt="booking.package_name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full items-center justify-center">
                                        <Package class="h-6 w-6 text-muted-foreground" />
                                    </div>
                                </div>
                                <div>
                                    <Link
                                        :href="tourist.packages.show(booking.id).url"
                                        class="font-medium hover:underline"
                                    >
                                        {{ booking.package_name }}
                                    </Link>
                                    <div class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                                        <span>{{ formatDate(booking.tour_date) }}</span>
                                        <span>•</span>
                                        <span>{{ booking.number_of_guests }} guest{{ booking.number_of_guests > 1 ? 's' : '' }}</span>
                                        <span v-if="booking.pickup_location" class="flex items-center gap-1">
                                            <MapPin class="h-3 w-3" />
                                            {{ booking.pickup_location }}
                                        </span>
                                    </div>
                                </div>
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
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-6 text-center text-muted-foreground">
                        <p>You haven't made any bookings yet.</p>
                        <Button as-child variant="link" class="mt-2">
                            <Link :href="tourist.packages.index().url">Browse Packages</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>