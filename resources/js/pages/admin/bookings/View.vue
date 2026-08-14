<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    User, 
    Mail, 
    Package, 
    Calendar, 
    Users, 
    MapPin, 
    Phone, 
    Globe, 
    Clock, 
    MessageSquare,
    DollarSign,
    Hash,
    Image as ImageIcon,
    CalendarClock
} from '@lucide/vue';

export interface BookingViewData {
    id: number;
    user_id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    tour_date_id: number;
    tour_date: {
        id: number;
        tour_date: string;
        package: {
            id: number;
            package_name: string;
            price: number;
            description: string;
            image: string | null;
            destination: string;
            status: string;
        };
    };
    pickup_location_id: number;
    pickup_location: {
        id: number;
        name: string;
        address: string | null;
    };
    number_of_guests: number;
    phone_number: string;
    nationality: string;
    special_request: string | null;
    booking_status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    booking: BookingViewData;
}>();

const emit = defineEmits<{
    close: [];
    edit: [];
}>();

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        confirmed: 'bg-blue-100 text-blue-700 border-blue-200',
        cancelled: 'bg-red-100 text-red-700 border-red-200',
        completed: 'bg-green-100 text-green-700 border-green-200',
    };
    return colors[status as keyof typeof colors] || 'bg-neutral-100 text-neutral-600 border-neutral-200';
};

const getStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const formatPrice = (price: number) => {
    if (!price || isNaN(price)) {
        return '₱0.00';
    }
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(price);
};

const getPackagePrice = () => {
    return props.booking?.tour_date?.package?.price || 0;
};

const getTotalPrice = () => {
    return getPackagePrice() * (props.booking?.number_of_guests || 0);
};
</script>

<template>
    <div v-if="booking" class="space-y-4">
        <!-- Header -->
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="text-lg font-semibold">Booking #{{ booking.id }}</h3>
                <p class="text-xs text-muted-foreground">{{ formatDateTime(booking.created_at) }}</p>
            </div>
            <Badge :class="getStatusColor(booking.booking_status)" class="text-sm">
                {{ getStatusLabel(booking.booking_status) }}
            </Badge>
        </div>

        <!-- 3 Column Grid -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <!-- Guest -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <User class="h-3.5 w-3.5" /> Guest
                </p>
                <p class="text-sm font-semibold mt-1">{{ booking.user?.name || 'N/A' }}</p>
                <p class="text-xs text-muted-foreground truncate">{{ booking.user?.email || 'N/A' }}</p>
                <p class="text-xs text-muted-foreground mt-0.5">User ID: #{{ booking.user_id }}</p>
            </div>

            <!-- Package -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <Package class="h-3.5 w-3.5" /> Package
                </p>
                <p class="text-sm font-semibold mt-1">{{ booking.tour_date?.package?.package_name || 'N/A' }}</p>
                <p class="text-xs text-muted-foreground">Destination: {{ booking.tour_date?.package?.destination || 'N/A' }}</p>
                <p class="text-xs text-muted-foreground">Package ID: #{{ booking.tour_date?.package?.id }}</p>
                <p class="text-xs text-muted-foreground">Status: {{ booking.tour_date?.package?.status || 'N/A' }}</p>
            </div>

            <!-- Tour Date -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <Calendar class="h-3.5 w-3.5" /> Tour Date
                </p>
                <p class="text-sm font-semibold mt-1">{{ formatDate(booking.tour_date?.tour_date) }}</p>
                <p class="text-xs text-muted-foreground">Tour Date ID: #{{ booking.tour_date_id }}</p>
            </div>

            <!-- Guests -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <Users class="h-3.5 w-3.5" /> Guests
                </p>
                <p class="text-sm font-semibold mt-1">{{ booking.number_of_guests }} guests</p>
            </div>

            <!-- Pickup Location -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <MapPin class="h-3.5 w-3.5" /> Pickup Location
                </p>
                <p class="text-sm font-semibold mt-1">{{ booking.pickup_location?.name || 'N/A' }}</p>
                <p v-if="booking.pickup_location?.address" class="text-xs text-muted-foreground truncate">
                    {{ booking.pickup_location.address }}
                </p>
                <p class="text-xs text-muted-foreground">Pickup ID: #{{ booking.pickup_location_id }}</p>
            </div>

            <!-- Phone -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <Phone class="h-3.5 w-3.5" /> Phone
                </p>
                <p class="text-sm font-semibold mt-1">{{ booking.phone_number || 'N/A' }}</p>
            </div>

            <!-- Nationality -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <Globe class="h-3.5 w-3.5" /> Nationality
                </p>
                <p class="text-sm font-semibold mt-1">{{ booking.nationality || 'N/A' }}</p>
            </div>

            <!-- Price Per Person -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <DollarSign class="h-3.5 w-3.5" /> Price Per Person
                </p>
                <p class="text-sm font-semibold mt-1">{{ formatPrice(getPackagePrice()) }}</p>
            </div>

            <!-- Total Amount -->
            <div class="rounded-lg border p-3 bg-primary/5">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <DollarSign class="h-3.5 w-3.5" /> Total Amount
                </p>
                <p class="text-sm font-bold text-primary mt-1">{{ formatPrice(getTotalPrice()) }}</p>
                <p class="text-xs text-muted-foreground">
                    {{ formatPrice(getPackagePrice()) }} × {{ booking.number_of_guests }} guests
                </p>
            </div>
        </div>

        <!-- Package Description - Full Width -->
        <div v-if="booking.tour_date?.package?.description" class="rounded-lg border p-3">
            <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                <MessageSquare class="h-3.5 w-3.5" /> Package Description
            </p>
            <p class="text-sm mt-1">{{ booking.tour_date.package.description }}</p>
        </div>

        <!-- Package Image - Full Width -->
        <div v-if="booking.tour_date?.package?.image" class="rounded-lg border p-3">
            <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                <ImageIcon class="h-3.5 w-3.5" /> Package Image
            </p>
            <div class="mt-1">
                <img 
                    :src="`/storage/${booking.tour_date.package.image}`" 
                    :alt="booking.tour_date.package.package_name"
                    class="h-20 w-20 rounded-md object-cover"
                />
            </div>
        </div>

        <!-- Special Request - Full Width -->
        <div v-if="booking.special_request" class="rounded-lg border p-3">
            <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                <MessageSquare class="h-3.5 w-3.5" /> Special Request
            </p>
            <p class="text-sm mt-1">{{ booking.special_request }}</p>
        </div>

        <!-- Timeline - 2 Columns -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <Clock class="h-3.5 w-3.5" /> Created At
                </p>
                <p class="text-sm font-semibold mt-1">{{ formatDateTime(booking.created_at) }}</p>
            </div>
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <Clock class="h-3.5 w-3.5" /> Last Updated
                </p>
                <p class="text-sm font-semibold mt-1">{{ formatDateTime(booking.updated_at || booking.created_at) }}</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-2 border-t pt-3">
            <Button variant="outline" size="sm" @click="emit('close')">Close</Button>
            <Button size="sm" @click="emit('edit')">Edit Booking</Button>
        </div>
    </div>
</template>