<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    ArrowLeft, 
    Calendar, 
    Users, 
    Package, 
    MapPin,
    CreditCard,
    Upload,
    AlertCircle,
    CheckCircle
} from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';

const props = defineProps<{
    booking: {
        id: number;
        number_of_guests: number;
        phone_number: string;
        nationality: string;
        special_request: string | null;
        tour_date: {
            id: number;
            tour_date: string;
            package: {
                id: number;
                package_name: string;
                price: number;
                description: string;
                image: string | null;
            };
        };
        pickup_location: {
            id: number;
            name: string;
            address: string | null;
        };
        payments: Array<{
            id: number;
            amount: number;
            payment_status: string;
            payment_method: string;
            transaction_reference: string | null;
            proof_of_payment: string | null;
            notes: string | null;
        }>;
    };
    existingPayment?: {
        id: number;
        amount: number;
        payment_method: string;
        transaction_reference: string | null;
        proof_of_payment: string | null;
        notes: string | null;
        payment_status: string;
    } | null;
}>();

const form = useForm({
    booking_id: props.booking.id,
    amount: props.existingPayment?.amount || props.booking.tour_date.package.price * props.booking.number_of_guests,
    payment_method: props.existingPayment?.payment_method || '',
    transaction_reference: props.existingPayment?.transaction_reference || '',
    proof_of_payment: null as File | null,
    notes: props.existingPayment?.notes || '',
});

const proofPreview = ref<string | null>(
    props.existingPayment?.proof_of_payment ? `/storage/${props.existingPayment.proof_of_payment}` : null
);

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

const paymentMethods = [
    { value: 'gcash', label: 'GCash' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'cash', label: 'Cash' },
    { value: 'credit_card', label: 'Credit Card' },
];

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        form.proof_of_payment = file;
        proofPreview.value = URL.createObjectURL(file);
    }
};

const submitPayment = () => {
    form.post('/tourist/payments', {
        onSuccess: () => {
            // After successful payment, redirect to bookings index
            router.get('/tourist/bookings');
        },
    });
};

const isEditing = computed(() => !!props.existingPayment);
</script>

<template>
    <Head :title="isEditing ? 'Update Payment' : 'Complete Payment'" />
    <div class="px-4 py-6">
        <div class="mx-auto max-w-3xl">
            <!-- Back Button -->
            <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
                <Link :href="`/tourist/bookings/${booking.id}`">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Booking
                </Link>
            </Button>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Payment Form -->
                <div class="lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <CreditCard class="h-5 w-5" />
                                {{ isEditing ? 'Update Payment' : 'Complete Payment' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="submitPayment" class="space-y-6">
                                <!-- Booking Summary -->
                                <div class="rounded-lg bg-muted/50 p-4">
                                    <div class="flex items-center gap-4">
                                        <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md bg-muted">
                                            <img
                                                v-if="booking.tour_date.package.image"
                                                :src="`/storage/${booking.tour_date.package.image}`"
                                                :alt="booking.tour_date.package.package_name"
                                                class="h-full w-full object-cover"
                                            />
                                            <div v-else class="flex h-full items-center justify-center">
                                                <Package class="h-6 w-6 text-muted-foreground" />
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold">{{ booking.tour_date.package.package_name }}</h3>
                                            <div class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                                <span class="flex items-center gap-1">
                                                    <Calendar class="h-3 w-3" />
                                                    {{ formatDate(booking.tour_date.tour_date) }}
                                                </span>
                                                <span class="flex items-center gap-1">
                                                    <Users class="h-3 w-3" />
                                                    {{ booking.number_of_guests }} guests
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Amount -->
                                <div>
                                    <Label for="amount">Amount to Pay</Label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">₱</span>
                                        <Input
                                            id="amount"
                                            v-model.number="form.amount"
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            class="pl-8"
                                            required
                                        />
                                    </div>
                                    <p v-if="form.errors.amount" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.amount }}
                                    </p>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <Label for="payment_method">Payment Method</Label>
                                    <Select v-model="form.payment_method">
                                        <SelectTrigger id="payment_method">
                                            <SelectValue placeholder="Select payment method" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="method in paymentMethods"
                                                :key="method.value"
                                                :value="method.value"
                                            >
                                                {{ method.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <p v-if="form.errors.payment_method" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.payment_method }}
                                    </p>
                                </div>

                                <!-- Transaction Reference -->
                                <div>
                                    <Label for="transaction_reference">Transaction Reference (Optional)</Label>
                                    <Input
                                        id="transaction_reference"
                                        v-model="form.transaction_reference"
                                        placeholder="e.g. GCash Ref #1234567890"
                                    />
                                    <p v-if="form.errors.transaction_reference" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.transaction_reference }}
                                    </p>
                                </div>

                                <!-- Proof of Payment -->
                                <div>
                                    <Label for="proof_of_payment">Proof of Payment (Optional)</Label>
                                    <div class="mt-1 flex items-center gap-4">
                                        <Input
                                            id="proof_of_payment"
                                            type="file"
                                            accept="image/*"
                                            @change="handleFileChange"
                                            class="flex-1"
                                        />
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Upload a screenshot or photo of your payment receipt.
                                    </p>
                                    <div v-if="proofPreview" class="mt-2">
                                        <img :src="proofPreview" alt="Proof preview" class="h-32 w-32 rounded-md object-cover" />
                                    </div>
                                    <p v-if="form.errors.proof_of_payment" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.proof_of_payment }}
                                    </p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <Label for="notes">Additional Notes (Optional)</Label>
                                    <Textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="3"
                                        placeholder="Any additional information about your payment..."
                                    />
                                    <p v-if="form.errors.notes" class="mt-1 text-sm text-red-500">
                                        {{ form.errors.notes }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800">
                                    <AlertCircle class="h-4 w-4 flex-shrink-0" />
                                    <span>Your booking will be confirmed once your payment is verified.</span>
                                </div>

                                <!-- Success Message -->
                                <div v-if="form.recentlySuccessful" class="rounded-lg bg-green-50 p-4 text-green-700">
                                    <CheckCircle class="inline h-5 w-5 mr-2" />
                                    Payment submitted successfully! Redirecting to bookings...
                                </div>

                                <Button type="submit" class="w-full" :disabled="form.processing">
                                    <span v-if="form.processing">Processing...</span>
                                    <span v-else>{{ isEditing ? 'Update Payment' : 'Submit Payment' }}</span>
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <Card>
                            <CardHeader>
                                <CardTitle>Payment Summary</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-muted-foreground">Package</span>
                                    <span class="font-medium">{{ booking.tour_date.package.package_name }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-muted-foreground">Guests</span>
                                    <span class="font-medium">{{ booking.number_of_guests }}</span>
                                </div>
                                <div class="flex justify-between border-b pb-2">
                                    <span class="text-muted-foreground">Price per person</span>
                                    <span class="font-medium">{{ formatPrice(booking.tour_date.package.price) }}</span>
                                </div>
                                <div class="flex justify-between pt-2 text-lg font-bold">
                                    <span>Total Amount</span>
                                    <span class="text-primary">{{ formatPrice(booking.tour_date.package.price * booking.number_of_guests) }}</span>
                                </div>
                                <div v-if="existingPayment" class="mt-2 rounded-lg bg-yellow-50 p-2 text-xs text-yellow-700">
                                    <p>You have a pending payment for this booking.</p>
                                    <p>Update your payment details or submit a new one.</p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>