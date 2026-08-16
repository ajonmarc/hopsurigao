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
    CalendarClock,
    CreditCard,
    ReceiptText
} from '@lucide/vue';

export interface BookingPayment {
    id: number;
    amount: number;
    payment_method: string;
    payment_status: 'pending' | 'paid' | 'failed' | 'refunded' | string;
    paid_at?: string | null;
    created_at?: string | null;
    reference_number?: string | null;
    proof_of_payment?: string | null;
}

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
    // CHANGED: pickup_location_id/pickup_location -> pickup_schedule
    pickup_schedule_id: number;
    pickup_schedule: {
        id: number;
        tour_date_id: number;
        pickup_location_id: number;
        pickup_time: string;
        pickup_location: {
            id: number;
            name: string;
            address: string | null;
        };
    };
    number_of_guests: number;
    phone_number: string;
    nationality: string;
    special_request: string | null;
    booking_status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
    payments?: BookingPayment[];
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

const getPaymentStatusColor = (status: string) => {
    const colors = {
        paid: 'bg-green-100 text-green-700 border-green-200',
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        failed: 'bg-red-100 text-red-700 border-red-200',
        refunded: 'bg-neutral-100 text-neutral-600 border-neutral-200',
    };
    return colors[status as keyof typeof colors] || 'bg-neutral-100 text-neutral-600 border-neutral-200';
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

const formatMethod = (method: string) => {
    if (!method) return 'N/A';
    return method
        .split('_')
        .map((w) => w.charAt(0).toUpperCase() + w.slice(1))
        .join(' ');
};


const formatPickupTime = (time?: string) => {
    if (!time) return 'N/A';
    // handles both "HH:mm:ss" and full ISO datetime strings
    const d = time.includes('T') || time.includes(' ') ? new Date(time) : new Date(`1970-01-01T${time}`);
    if (isNaN(d.getTime())) return time;
    return d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
};

// Latest payment first, so the most recent attempt/confirmation is on top.
// Falls back to 0 (treated as oldest) if created_at isn't present.
const sortedPayments = () => {
    return [...(props.booking?.payments ?? [])].sort((a, b) => {
        const aTime = a.created_at ? new Date(a.created_at).getTime() : 0;
        const bTime = b.created_at ? new Date(b.created_at).getTime() : 0;
        return bTime - aTime;
    });
};

const totalPaid = () => {
    return (props.booking?.payments ?? [])
        .filter((p) => p.payment_status === 'paid')
        .reduce((sum, p) => sum + Number(p.amount || 0), 0);
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
                <p class="text-xs text-muted-foreground">Destination: {{ booking.tour_date?.package?.destination ||
                    'N/A' }}</p>
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

            <!-- Pickup Schedule (CHANGED: was Pickup Location) -->
            <div class="rounded-lg border p-3">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <MapPin class="h-3.5 w-3.5" /> Pickup
                </p>
                <p class="text-sm font-semibold mt-1">{{ booking.pickup_schedule?.pickup_location?.name || 'N/A' }}</p>
                <p v-if="booking.pickup_schedule?.pickup_location?.address"
                    class="text-xs text-muted-foreground truncate">
                    {{ booking.pickup_schedule.pickup_location.address }}
                </p>
                <p class="text-xs text-muted-foreground flex items-center gap-1 mt-0.5">
                    <Clock class="h-3 w-3" /> {{ formatPickupTime(booking.pickup_schedule?.pickup_time) }}
                </p>
                <p class="text-xs text-muted-foreground">Schedule ID: #{{ booking.pickup_schedule_id }}</p>
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

        <!-- Payments - Full Width -->
        <div class="rounded-lg border p-3">
            <div class="flex items-center justify-between">
                <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                    <CreditCard class="h-3.5 w-3.5" /> Payments
                </p>
                <p v-if="booking.payments && booking.payments.length > 0" class="text-xs text-muted-foreground">
                    Total Paid: <span class="font-semibold text-foreground">{{ formatPrice(totalPaid()) }}</span>
                </p>
            </div>

            <div v-if="booking.payments && booking.payments.length > 0" class="mt-2 space-y-2">
                <div v-for="payment in sortedPayments()" :key="payment.id"
                    class="flex flex-col gap-3 rounded-md border p-2.5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex items-start gap-2">
                        <ReceiptText class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold">{{ formatPrice(payment.amount) }}</p>
                                <Badge :class="getPaymentStatusColor(payment.payment_status)" class="text-[10px]">
                                    {{ getStatusLabel(payment.payment_status) }}
                                </Badge>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ formatMethod(payment.payment_method) }}
                                <span v-if="payment.reference_number"> &middot; Ref: {{ payment.reference_number
                                    }}</span>
                            </p>
                            <div class="text-left text-xs text-muted-foreground mt-1 sm:hidden">
                                <p v-if="payment.paid_at">Paid: {{ formatDateTime(payment.paid_at) }}</p>
                                <p v-if="payment.created_at">Submitted: {{ formatDateTime(payment.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <!-- Proof of payment thumbnail, click to view full size -->
                        <a v-if="payment.proof_of_payment" :href="`/storage/${payment.proof_of_payment}`"
                            target="_blank" rel="noopener noreferrer" title="Click to view full size proof of payment"
                            class="block shrink-0 overflow-hidden rounded-md border transition-opacity hover:opacity-90">
                            <img :src="`/storage/${payment.proof_of_payment}`" alt="Proof of payment"
                                class="h-16 w-16 object-cover" />
                        </a>

                        <div class="hidden text-left text-xs text-muted-foreground sm:block sm:text-right">
                            <p v-if="payment.paid_at">Paid: {{ formatDateTime(payment.paid_at) }}</p>
                            <p v-if="payment.created_at">Submitted: {{ formatDateTime(payment.created_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="mt-1 text-sm text-muted-foreground">No payment records for this booking.</p>
        </div>

        <!-- Package Description - Full Width -->
        <div v-if="booking.tour_date?.package?.description" class="rounded-lg border p-3">
            <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                <MessageSquare class="h-3.5 w-3.5" /> Package Description
            </p>
            <p class="text-sm mt-1">{{ booking.tour_date.package.description }}</p>
        </div>

        <!-- Package Image - Full Width, larger + click to view full size -->
        <div v-if="booking.tour_date?.package?.image" class="rounded-lg border p-3">
            <p class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                <ImageIcon class="h-3.5 w-3.5" /> Package Image
            </p>
            <a :href="`/storage/${booking.tour_date.package.image}`" target="_blank" rel="noopener noreferrer"
                class="mt-2 block w-full max-w-md overflow-hidden rounded-md border transition-opacity hover:opacity-90"
                title="Click to view full size">
                <img :src="`/storage/${booking.tour_date.package.image}`" :alt="booking.tour_date.package.package_name"
                    class="h-56 w-full object-cover" />
            </a>
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