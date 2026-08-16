<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle, XCircle, Clock, CreditCard, Calendar, Users, Package, MapPin } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import tourist from '@/routes/tourist';

const props = defineProps<{
    payment: {
        id: number;
        amount: number;
        payment_method: string;
        payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
        transaction_reference: string | null;
        proof_of_payment: string | null;
        notes: string | null;
        paid_at: string | null;
        created_at: string;
        booking: {
            id: number;
            number_of_guests: number;
            tour_date: {
                tour_date: string;
                package: {
                    id: number;
                    package_name: string;
                    price: number;
                    description: string;
                    image: string | null;
                };
            };
            // CHANGED: pickup_location -> pickup_schedule
            pickup_schedule: {
                pickup_time: string;
                pickup_location: {
                    name: string;
                    address: string | null;
                };
            };
        };
    };
}>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(price);
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

const getPaymentStatusColor = (status: string) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        paid: 'bg-green-100 text-green-700 border-green-200',
        failed: 'bg-red-100 text-red-700 border-red-200',
        refunded: 'bg-gray-100 text-gray-700 border-gray-200',
    };
    return colors[status as keyof typeof colors] || 'bg-neutral-100 text-neutral-600';
};

const getPaymentStatusLabel = (status: string) => {
    return status.charAt(0).toUpperCase() + status.slice(1);
};

const getPaymentMethodLabel = (method: string) => {
    const methods = {
        gcash: 'GCash',
        bank_transfer: 'Bank Transfer',
        cash: 'Cash',
        credit_card: 'Credit Card',
    };
    return methods[method as keyof typeof methods] || method;
};
</script>

<template>

    <Head title="Payment Details" />
    <div class="px-4 py-6">
        <div class="mx-auto max-w-3xl">
            <!-- Back Button -->
            <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
                <Link :href="tourist.bookings.index().url">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to My Bookings
                </Link>
            </Button>

            <!-- Payment Header -->
            <Card>
                <CardHeader class="border-b">
                    <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                        <div>
                            <CardTitle class="text-2xl">
                                Payment #{{ payment.id }}
                            </CardTitle>
                            <p class="text-sm text-muted-foreground">
                                {{ formatDateTime(payment.created_at) }}
                            </p>
                        </div>
                        <Badge :class="getPaymentStatusColor(payment.payment_status)" class="text-sm">
                            {{ getPaymentStatusLabel(payment.payment_status) }}
                        </Badge>
                    </div>
                </CardHeader>
            </Card>

            <!-- Payment Details -->
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5" />
                            Payment Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Amount</span>
                            <span class="font-bold text-primary">{{ formatPrice(payment.amount) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Payment Method</span>
                            <span>{{ getPaymentMethodLabel(payment.payment_method) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :class="getPaymentStatusColor(payment.payment_status)">
                                {{ getPaymentStatusLabel(payment.payment_status) }}
                            </Badge>
                        </div>
                        <div v-if="payment.transaction_reference" class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Transaction Reference</span>
                            <span class="font-mono text-sm">{{ payment.transaction_reference }}</span>
                        </div>
                        <div v-if="payment.paid_at" class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Paid At</span>
                            <span>{{ formatDateTime(payment.paid_at) }}</span>
                        </div>
                        <div v-if="payment.notes" class="flex justify-between pb-2">
                            <span class="text-muted-foreground">Notes</span>
                            <span class="text-sm">{{ payment.notes }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Booking Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Package class="h-5 w-5" />
                            Booking Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Booking ID</span>
                            <Link :href="`/tourist/bookings/${payment.booking.id}`"
                                class="text-blue-600 hover:underline">
                                #{{ payment.booking.id }}
                            </Link>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Package</span>
                            <span>{{ payment.booking.tour_date.package.package_name }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Tour Date</span>
                            <span>{{ formatDate(payment.booking.tour_date.tour_date) }}</span>
                        </div>
                        <div class="flex justify-between border-b pb-2">
                            <span class="text-muted-foreground">Guests</span>
                            <span>{{ payment.booking.number_of_guests }} guests</span>
                        </div>
                        <div class="flex justify-between pb-2">
                            <span class="text-muted-foreground">Pickup Location</span>
                            <span>{{ payment.booking.pickup_schedule?.pickup_location?.name || 'N/A' }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Proof of Payment -->
            <Card v-if="payment.proof_of_payment" class="mt-6">
                <CardHeader>
                    <CardTitle>Proof of Payment</CardTitle>
                </CardHeader>
                <CardContent>
                    <a :href="`/storage/${payment.proof_of_payment}`" target="_blank"
                        class="text-blue-600 hover:underline">
                        View Proof of Payment
                    </a>
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <Button as-child variant="outline">
                    <Link :href="tourist.bookings.index().url">
                        Back to Bookings
                    </Link>
                </Button>
            </div>
        </div>
    </div>
</template>