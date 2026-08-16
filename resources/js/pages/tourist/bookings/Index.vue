<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    CalendarClock,
    MapPin,
    Users,
    Package,
    Clock,
    CheckCircle,
    XCircle,
    Phone,
    Globe,
    MessageSquare,
    Calendar,
    User,
    Mail,
    Trash2,
    X,
    Eye,
    CreditCard,
    QrCode,
    Wallet
} from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import DeleteDialog from '@/components/crud/DeleteDialog.vue';
import QrCodeDisplay from '@/components/QrCodeDisplay.vue';
import tourist from '@/routes/tourist';

const props = defineProps<{
    bookings: {
        data: Array<{
            id: number;
            user_id: number;
            tour_date_id: number;
            pickup_schedule_id: number;
            number_of_guests: number;
            phone_number: string;
            nationality: string;
            special_request: string | null;
            booking_status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
            qr_token: string | null;
            created_at: string;
            tour_date: {
                id: number;
                tour_date: string;
                capacity: number;
                package: {
                    id: number;
                    package_name: string;
                    image: string | null;
                    description: string;
                    price: number | null;
                };
            };
            pickup_schedule: {
                id: number;
                pickup_time: string;
                pickup_location: {
                    id: number;
                    name: string;
                    address: string | null;
                };
            };
            payments: Array<{
                id: number;
                amount: number;
                payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
                payment_method: string;
            }>;
        }>;
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    filters?: {
        status?: string;
        search?: string;
        per_page?: number | string;
    };
}>();

const statusFilter = ref(props.filters?.status ?? '');
const perPage = ref(String(props.filters?.per_page ?? 10));

// Dialog state
const showDeleteDialog = ref(false);
const showCancelDialog = ref(false);
const selectedBookingId = ref<number | null>(null);
const selectedBookingName = ref<string>('');

// QR code dialog state
const showQrDialog = ref(false);
const qrBookingToken = ref<string | null>(null);
const qrBookingId = ref<number | null>(null);
const qrDisplayRef = ref<InstanceType<typeof QrCodeDisplay> | null>(null);

// Tracks which booking's "Pay Now" was just clicked, so only that
// button shows a loading state instead of the whole page.
const payingBookingId = ref<number | null>(null);

const getStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        confirmed: 'bg-blue-100 text-blue-700 border-blue-200',
        cancelled: 'bg-red-100 text-red-700 border-red-200',
        completed: 'bg-green-100 text-green-700 border-green-200',
    };
    return colors[status as keyof typeof colors] || 'bg-neutral-100 text-neutral-600 border-neutral-200';
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

const getStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const getPaymentStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-700',
        paid: 'bg-green-100 text-green-700',
        failed: 'bg-red-100 text-red-700',
        refunded: 'bg-gray-100 text-gray-700',
    };
    return colors[status as keyof typeof colors] || 'bg-neutral-100 text-neutral-600';
};

const getPaymentStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const formatPrice = (price: number | null) => {
    if (price === null || price === undefined || isNaN(price)) {
        return '₱0';
    }
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};


const formatTime = (timeString: string | undefined | null) => {
    if (!timeString) return '';
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const canCancel = (status: string) => {
    return ['pending', 'confirmed'].includes(status);
};

const canDelete = (status: string) => {
    return ['pending', 'cancelled'].includes(status);
};

// Matches the backend's EDITABLE_STATUSES — only pending bookings can
// be edited by the tourist directly.
const canEdit = (status: string) => {
    return status === 'pending';
};

// QR codes only make sense for bookings the operator will actually be
// checking someone in for — a cancelled booking shouldn't be scannable.
const canShowQr = (status: string) => {
    return ['pending', 'confirmed', 'completed'].includes(status);
};

// Payment only makes sense while the booking is still active and there's
// no successful payment on it yet (no payment record, or the last
// attempt failed). Cancelled bookings shouldn't be payable.
const canPay = (booking: typeof props.bookings.data[0]) => {
    if (!['pending', 'confirmed'].includes(booking.booking_status)) {
        return false;
    }
    if (!booking.payments || booking.payments.length === 0) {
        return true;
    }
    return booking.payments[0].payment_status === 'failed';
};

const openCancelDialog = (booking: typeof props.bookings.data[0]) => {
    selectedBookingId.value = booking.id;
    selectedBookingName.value = booking.tour_date.package.package_name;
    showCancelDialog.value = true;
};

const openDeleteDialog = (booking: typeof props.bookings.data[0]) => {
    selectedBookingId.value = booking.id;
    selectedBookingName.value = booking.tour_date.package.package_name;
    showDeleteDialog.value = true;
};

const openQrDialog = (booking: typeof props.bookings.data[0]) => {
    if (!booking.qr_token) return;
    qrBookingToken.value = booking.qr_token;
    qrBookingId.value = booking.id;
    showQrDialog.value = true;
};

const downloadQr = () => {
    qrDisplayRef.value?.download();
};

// Sends the tourist to the payment creation page, passing the booking
// id as a query param so PaymentController@create can look it up and
// pre-fill the amount/booking details.
const payNow = (booking: typeof props.bookings.data[0]) => {
    if (payingBookingId.value) return;
    payingBookingId.value = booking.id;

    router.get(tourist.payments.create().url, {
        booking_id: booking.id,
    }, {
        onFinish: () => {
            payingBookingId.value = null;
        },
    });
};

const handleCancelSuccess = () => {
    showCancelDialog.value = false;
    router.reload();
};

const handleDeleteSuccess = () => {
    showDeleteDialog.value = false;
    router.reload();
};

const applyFilters = () => {
    const params: Record<string, any> = {
        status: statusFilter.value || undefined,
        per_page: perPage.value || undefined,
    };

    Object.keys(params).forEach(key => {
        if (params[key] === undefined || params[key] === '') {
            delete params[key];
        }
    });

    router.get(tourist.bookings.index().url, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const onFilterChange = () => {
    applyFilters();
};
</script>

<template>

    <Head title="My Bookings" />
    <div class="px-4 py-6">
        <Heading title="My Bookings" description="View and manage your tour bookings" />

        <!-- Filters -->
        <div class="mt-6 flex flex-wrap items-center gap-4 rounded-lg border p-4">
            <Select :model-value="statusFilter || 'all'"
                @update:model-value="(v) => { statusFilter = v === 'all' ? '' : v as string; onFilterChange(); }">
                <SelectTrigger class="w-[150px]">
                    <SelectValue placeholder="All Status" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">All Status</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="confirmed">Confirmed</SelectItem>
                    <SelectItem value="completed">Completed</SelectItem>
                    <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
            </Select>

            <Select :model-value="perPage" @update:model-value="(v) => { perPage = v as string; onFilterChange(); }">
                <SelectTrigger class="w-[100px]">
                    <SelectValue placeholder="Per page" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="10">10</SelectItem>
                    <SelectItem value="25">25</SelectItem>
                    <SelectItem value="50">50</SelectItem>
                </SelectContent>
            </Select>

            <Button as-child variant="default" size="sm">
                <Link :href="tourist.packages.index().url">
                    Book New Tour
                </Link>
            </Button>
        </div>

        <!-- Bookings List -->
        <div v-if="bookings.data.length > 0" class="mt-6 space-y-6">
            <Card v-for="booking in bookings.data" :key="booking.id" class="overflow-hidden">
                <!-- Header with Package Name and Status -->
                <div class="border-b bg-muted/30 px-6 py-4">
                    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-md bg-muted">
                                <img v-if="booking.tour_date.package.image"
                                    :src="`/storage/${booking.tour_date.package.image}`"
                                    :alt="booking.tour_date.package.package_name" class="h-full w-full object-cover" />
                                <div v-else class="flex h-full items-center justify-center">
                                    <Package class="h-6 w-6 text-muted-foreground" />
                                </div>
                            </div>
                            <div class="min-w-0">
                                <Link :href="`/tourist/packages/${booking.tour_date.package.id}`"
                                    class="text-lg font-semibold hover:underline">
                                    {{ booking.tour_date.package.package_name }}
                                </Link>
                                <p class="text-sm text-muted-foreground line-clamp-1">
                                    {{ booking.tour_date.package.description }}
                                </p>
                            </div>
                        </div>
                        <div class="flex w-full flex-wrap items-center gap-2 sm:w-auto sm:justify-end">
                            <Badge :class="getStatusColor(booking.booking_status)">
                                <component :is="getStatusIcon(booking.booking_status)" class="mr-1 h-3 w-3" />
                                {{ getStatusLabel(booking.booking_status) }}
                            </Badge>
                            <div class="flex flex-wrap gap-2">
                                <Button v-if="canShowQr(booking.booking_status) && booking.qr_token" variant="secondary"
                                    size="sm" @click="openQrDialog(booking)">
                                    <QrCode class="mr-1.5 h-4 w-4 shrink-0" />
                                    <span>QR Code</span>
                                </Button>
                                <Button v-if="canEdit(booking.booking_status)" as-child variant="outline" size="sm">
                                    <Link :href="`/tourist/bookings/${booking.id}/edit`">Edit</Link>
                                </Button>
                                <Button v-if="canCancel(booking.booking_status)" variant="destructive" size="sm"
                                    @click="openCancelDialog(booking)">
                                    Cancel
                                </Button>
                                <Button v-if="canDelete(booking.booking_status)" variant="outline" size="sm"
                                    @click="openDeleteDialog(booking)">
                                    Delete
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details Grid -->
                <CardContent class="p-6">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Tour Date</p>
                            <p class="flex items-center gap-1 text-sm">
                                <Calendar class="h-3 w-3 shrink-0 text-muted-foreground" />
                                <span class="truncate">{{ formatDate(booking.tour_date.tour_date) }}</span>
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Guests</p>
                            <p class="flex items-center gap-1 text-sm">
                                <Users class="h-3 w-3 shrink-0 text-muted-foreground" />
                                <span class="truncate">{{ booking.number_of_guests }} guests</span>
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Pickup</p>
                            <p class="flex items-center gap-1 text-sm">
                                <MapPin class="h-3 w-3 shrink-0 text-muted-foreground" />
                                <span class="truncate">{{ booking.pickup_schedule?.pickup_location?.name || 'N/A'
                                    }}</span>
                            </p>
                            <p v-if="booking.pickup_schedule?.pickup_time"
                                class="flex items-center gap-1 text-xs text-muted-foreground">
                                <Clock class="h-3 w-3 shrink-0" />
                                <span >{{ formatTime(booking.pickup_schedule.pickup_time) }}</span>
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Price</p>
                            <p class="flex items-center gap-1 text-sm">
                                <span class="truncate">{{ formatPrice(booking.tour_date.package.price) }}</span>
                                <span class="shrink-0 text-xs text-muted-foreground">per person</span>
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Phone</p>
                            <p class="flex items-center gap-1 text-sm">
                                <Phone class="h-3 w-3 shrink-0 text-muted-foreground" />
                                <span class="truncate">{{ booking.phone_number }}</span>
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Nationality</p>
                            <p class="flex items-center gap-1 text-sm">
                                <Globe class="h-3 w-3 shrink-0 text-muted-foreground" />
                                <span class="truncate">{{ booking.nationality }}</span>
                            </p>
                        </div>
                        <div class="min-w-0 col-span-2 sm:col-span-1">
                            <p class="text-xs font-medium text-muted-foreground">Booked On</p>
                            <p class="flex items-center gap-1 text-sm">
                                <Clock class="h-3 w-3 shrink-0 text-muted-foreground" />
                                <span class="truncate">{{ formatDateTime(booking.created_at) }}</span>
                            </p>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-muted-foreground">Booking ID</p>
                            <p class="flex items-center gap-1 text-sm">
                                #{{ booking.id }}
                            </p>
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="mt-4 border-t pt-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2 text-sm font-medium">
                                <CreditCard class="h-4 w-4" />
                                Payment Status
                            </span>
                            <div class="flex items-center gap-2">
                                <div v-if="booking.payments && booking.payments.length > 0">
                                    <Badge :class="getPaymentStatusColor(booking.payments[0].payment_status)">
                                        {{ getPaymentStatusLabel(booking.payments[0].payment_status) }}
                                    </Badge>
                                </div>
                                <div v-else>
                                    <Badge class="bg-gray-100 text-gray-600">
                                        No Payment
                                    </Badge>
                                </div>
                                <Button v-if="canPay(booking)" variant="default" size="sm"
                                    :disabled="payingBookingId === booking.id" @click="payNow(booking)">
                                    <Wallet class="mr-1.5 h-3.5 w-3.5 shrink-0" />
                                    <span>{{ payingBookingId === booking.id ? 'Redirecting...' : 'Pay Now' }}</span>
                                </Button>
                            </div>
                        </div>
                        <div v-if="booking.payments && booking.payments.length > 0"
                            class="mt-2 text-sm text-muted-foreground">
                            <span>Amount: {{ formatPrice(booking.payments[0].amount) }}</span>
                            <span class="mx-2">|</span>
                            <span>Method: {{ booking.payments[0].payment_method.toUpperCase().replace('_', ' ')
                            }}</span>
                        </div>
                    </div>

                    <!-- Special Request - Full Width -->
                    <div v-if="booking.special_request" class="mt-4 rounded-lg bg-muted/30 p-3">
                        <p class="text-xs font-medium text-muted-foreground">Special Request</p>
                        <p class="mt-1 flex items-start gap-2 text-sm">
                            <MessageSquare class="mt-0.5 h-3 w-3 flex-shrink-0 text-muted-foreground" />
                            {{ booking.special_request }}
                        </p>
                    </div>

                    <!-- Total Price -->
                    <div class="mt-4 border-t pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Total Amount</span>
                            <span class="text-lg font-bold text-primary">
                                {{ formatPrice(booking.tour_date.package.price ? booking.tour_date.package.price *
                                    booking.number_of_guests : 0) }}
                            </span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div v-else class="mt-12 text-center">
            <p class="text-muted-foreground">You haven't made any bookings yet.</p>
            <Button as-child variant="link" class="mt-2">
                <Link :href="tourist.packages.index().url">Browse Packages</Link>
            </Button>
        </div>

        <!-- Pagination -->
        <div v-if="bookings.links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-1">
            <Link v-for="(link, i) in bookings.links" :key="i" :href="link.url ?? '#'" :class="[
                'rounded px-3 py-1 text-sm',
                link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                !link.url && 'pointer-events-none opacity-50',
            ]" v-html="link.label" />
        </div>

        <!-- Cancel Confirmation Dialog -->
        <DeleteDialog :open="showCancelDialog" :action="{
            url: `/tourist/bookings/${selectedBookingId}/cancel`,
            method: 'put'
        }" :description="`This will cancel your booking for ${selectedBookingName}. This action cannot be undone.`"
            @update:open="(val) => { showCancelDialog = val; if (!val) { selectedBookingId = null; selectedBookingName = ''; } }"
            @deleted="handleCancelSuccess" />

        <!-- Delete Confirmation Dialog -->
        <DeleteDialog :open="showDeleteDialog" :action="{
            url: `/tourist/bookings/${selectedBookingId}`,
            method: 'delete'
        }" :description="`This will permanently delete your booking for ${selectedBookingName}. This action cannot be undone.`"
            @update:open="(val) => { showDeleteDialog = val; if (!val) { selectedBookingId = null; selectedBookingName = ''; } }"
            @deleted="handleDeleteSuccess" />

        <!-- QR Code Dialog -->
        <Dialog :open="showQrDialog"
            @update:open="(v) => { showQrDialog = v; if (!v) { qrBookingToken = null; qrBookingId = null; } }">
            <DialogContent class="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Booking QR Code</DialogTitle>
                    <DialogDescription>
                        Show this code to the tour operator at pickup. Booking #{{ qrBookingId }}
                    </DialogDescription>
                </DialogHeader>

                <div class="flex flex-col items-center gap-4 py-2">
                    <QrCodeDisplay v-if="qrBookingToken" ref="qrDisplayRef" :value="qrBookingToken" :size="240" />
                    <Button variant="outline" size="sm" class="w-full" @click="downloadQr">
                        Download QR Code
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>