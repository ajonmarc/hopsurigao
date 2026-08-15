<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { ArrowLeft, Calendar, MapPin, Users, Phone, Globe } from '@lucide/vue';
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
import { useForm } from '@inertiajs/vue3';
import tourist from '@/routes/tourist';

const props = defineProps<{
    tourDate: {
        id: number;
        tour_date: string;
        package: {
            id: number;
            package_name: string;
            price: number;
            image: string | null;
        };
        available_spots: number;
        capacity: number;
    };
    pickupLocations: Array<{
        id: number;
        name: string;
        address: string | null;
    }>;
    selectedPickupLocationId?: number | null;
    guests?: number;
}>();

// Pre-fill form with URL parameters
const form = useForm({
    tour_date_id: props.tourDate.id,
    pickup_location_id: props.selectedPickupLocationId ? String(props.selectedPickupLocationId) : '',
    number_of_guests: props.guests || 1,
    phone_number: '',
    nationality: '',
    special_request: '',
});

// Validate that number of guests doesn't exceed available spots
const maxGuests = computed(() => {
    return Math.min(props.tourDate.available_spots, 20);
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const totalPrice = computed(() => {
    return props.tourDate.package.price * form.number_of_guests;
});

// FIX: Removed the onSuccess override that was forcing a redirect back to
// /tourist/bookings. The server (BookingController::store) already redirects
// to tourist.payments.create on success, and Inertia follows that redirect
// automatically. The old onSuccess callback was firing right after and
// navigating the user away from the payment page back to the bookings list.
const submitBooking = () => {
    form.post('/tourist/bookings');
};
</script>

<template>
    <Head title="Book Tour" />
    <div class="px-4 py-6">
        <!-- Back Button -->
        <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
            <Link :href="`/tourist/packages/${tourDate.package.id}`">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Package
            </Link>
        </Button>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Booking Form -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Complete Your Booking</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitBooking" class="space-y-6">
                            <!-- Package Summary -->
                            <div class="rounded-lg bg-muted/50 p-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md bg-muted">
                                        <img
                                            v-if="tourDate.package.image"
                                            :src="`/storage/${tourDate.package.image}`"
                                            :alt="tourDate.package.package_name"
                                            class="h-full w-full object-cover"
                                        />
                                        <div v-else class="flex h-full items-center justify-center">
                                            <Calendar class="h-6 w-6 text-muted-foreground" />
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold">{{ tourDate.package.package_name }}</h3>
                                        <div class="flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                            <span class="flex items-center gap-1">
                                                <Calendar class="h-3 w-3" />
                                                {{ formatDate(tourDate.tour_date) }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <Users class="h-3 w-3" />
                                                {{ tourDate.available_spots }} spots left
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pickup Location (pre-filled, editable) -->
                            <div>
                                <Label for="pickup_location_id">Pickup Location</Label>
                                <Select v-model="form.pickup_location_id">
                                    <SelectTrigger id="pickup_location_id">
                                        <SelectValue placeholder="Select pickup location" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="location in pickupLocations"
                                            :key="location.id"
                                            :value="String(location.id)"
                                        >
                                            {{ location.name }}
                                            <span v-if="location.address" class="ml-2 text-muted-foreground">
                                                ({{ location.address }})
                                            </span>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.pickup_location_id" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.pickup_location_id }}
                                </p>
                            </div>

                            <!-- Number of Guests (pre-filled, editable) -->
                            <div>
                                <Label for="number_of_guests">Number of Guests</Label>
                                <Input
                                    id="number_of_guests"
                                    v-model.number="form.number_of_guests"
                                    type="number"
                                    min="1"
                                    :max="maxGuests"
                                    required
                                />
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Maximum {{ maxGuests }} guests allowed
                                </p>
                                <p v-if="form.errors.number_of_guests" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.number_of_guests }}
                                </p>
                            </div>

                            <!-- Phone Number (user fills this) -->
                            <div>
                                <Label for="phone_number">Phone Number</Label>
                                <Input
                                    id="phone_number"
                                    v-model="form.phone_number"
                                    type="tel"
                                    placeholder="e.g. +63 912 345 6789"
                                    required
                                />
                                <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.phone_number }}
                                </p>
                            </div>

                            <!-- Nationality (user fills this) -->
                            <div>
                                <Label for="nationality">Nationality</Label>
                                <Input
                                    id="nationality"
                                    v-model="form.nationality"
                                    placeholder="e.g. Filipino, American, etc."
                                    required
                                />
                                <p v-if="form.errors.nationality" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.nationality }}
                                </p>
                            </div>

                            <!-- Special Request (optional) -->
                            <div>
                                <Label for="special_request">Special Request (Optional)</Label>
                                <Textarea
                                    id="special_request"
                                    v-model="form.special_request"
                                    rows="3"
                                    placeholder="Any special requirements or requests..."
                                />
                                <p v-if="form.errors.special_request" class="mt-1 text-sm text-red-500">
                                    {{ form.errors.special_request }}
                                </p>
                            </div>

                            <Button type="submit" class="w-full" :disabled="form.processing">
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Confirm Booking</span>
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
                            <CardTitle>Order Summary</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-muted-foreground">Package</span>
                                <span class="font-medium">{{ tourDate.package.package_name }}</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-muted-foreground">Date</span>
                                <span class="font-medium">{{ formatDate(tourDate.tour_date) }}</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-muted-foreground">Guests</span>
                                <span class="font-medium">{{ form.number_of_guests }}</span>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <span class="text-muted-foreground">Price per person</span>
                                <span class="font-medium">{{ formatPrice(tourDate.package.price) }}</span>
                            </div>
                            <div class="flex justify-between pt-2 text-lg font-bold">
                                <span>Total</span>
                                <span class="text-primary">{{ formatPrice(totalPrice) }}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>