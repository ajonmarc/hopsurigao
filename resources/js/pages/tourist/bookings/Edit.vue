<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Package, MapPin, Users, Phone, Globe, MessageSquare, AlertCircle } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import tourist from '@/routes/tourist';

const props = defineProps<{
    booking: {
        id: number;
        pickup_schedule_id: number; // CHANGED
        number_of_guests: number;
        phone_number: string;
        nationality: string;
        special_request: string | null;
        booking_status: string;
        tour_date: {
            tour_date: string;
            package: {
                package_name: string;
                image: string | null;
                price: number | null;
            };
        };
        // CHANGED: pickup location now nested under pickup_schedule
        pickup_schedule: {
            id: number;
            pickup_time: string;
            pickup_location: {
                id: number;
                name: string;
            };
        };
    };
    // CHANGED: was pickupLocations
    pickupSchedules: Array<{
        id: number;
        tour_date_id: number;
        pickup_location_id: number;
        label: string;
    }>;
    availableSpots: number;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Bookings', href: tourist.bookings.index().url },
            { title: 'Edit Booking', href: '#' },
        ],
    },
});

const form = useForm({
    pickup_schedule_id: props.booking.pickup_schedule_id,
    number_of_guests: props.booking.number_of_guests,
    phone_number: props.booking.phone_number,
    nationality: props.booking.nationality,
    special_request: props.booking.special_request ?? '',
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatPrice = (price: number | null) => {
    if (price === null || price === undefined || isNaN(price)) return '₱0';
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const submit = () => {
    form.put(`/tourist/bookings/${props.booking.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Booking" />
    <div class="mx-auto max-w-2xl px-4 py-6">
        <Heading title="Edit Booking" description="Update your booking details" />

        <!-- Package summary, read-only -->
        <Card class="mt-6">
            <CardContent class="flex items-center gap-4 p-4">
                <div class="h-14 w-14 flex-shrink-0 overflow-hidden rounded-md bg-muted">
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
                <div class="min-w-0">
                    <p class="truncate font-semibold">{{ booking.tour_date.package.package_name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ formatDate(booking.tour_date.tour_date) }}
                        &middot; {{ formatPrice(booking.tour_date.package.price) }} per person
                    </p>
                </div>
            </CardContent>
        </Card>

        <p class="mt-2 flex items-center gap-1.5 text-xs text-muted-foreground">
            <AlertCircle class="h-3.5 w-3.5" />
            Tour date can't be changed here — cancel and rebook if you need a different date.
        </p>

        <!-- Editable fields -->
        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <div>
                <Label for="pickup_schedule_id" class="mb-1.5 flex items-center gap-1.5">
                    <MapPin class="h-3.5 w-3.5" /> Pickup Location & Time
                </Label>
                <Select
                    :model-value="String(form.pickup_schedule_id)"
                    @update:model-value="(v) => form.pickup_schedule_id = Number(v)"
                >
                    <SelectTrigger id="pickup_schedule_id" class="w-full">
                        <SelectValue placeholder="Select pickup location & time" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="schedule in pickupSchedules" :key="schedule.id" :value="String(schedule.id)">
                            {{ schedule.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.pickup_schedule_id" class="mt-1 text-sm text-destructive">
                    {{ form.errors.pickup_schedule_id }}
                </p>
            </div>

            <div>
                <Label for="number_of_guests" class="mb-1.5 flex items-center gap-1.5">
                    <Users class="h-3.5 w-3.5" /> Number of Guests
                </Label>
                <Input
                    id="number_of_guests"
                    v-model.number="form.number_of_guests"
                    type="number"
                    min="1"
                    :max="availableSpots + booking.number_of_guests"
                />
                <p class="mt-1 text-xs text-muted-foreground">
                    Up to {{ availableSpots + booking.number_of_guests }} guests available for this tour date.
                </p>
                <p v-if="form.errors.number_of_guests" class="mt-1 text-sm text-destructive">
                    {{ form.errors.number_of_guests }}
                </p>
            </div>

            <div>
                <Label for="phone_number" class="mb-1.5 flex items-center gap-1.5">
                    <Phone class="h-3.5 w-3.5" /> Phone Number
                </Label>
                <Input id="phone_number" v-model="form.phone_number" type="text" />
                <p v-if="form.errors.phone_number" class="mt-1 text-sm text-destructive">
                    {{ form.errors.phone_number }}
                </p>
            </div>

            <div>
                <Label for="nationality" class="mb-1.5 flex items-center gap-1.5">
                    <Globe class="h-3.5 w-3.5" /> Nationality
                </Label>
                <Input id="nationality" v-model="form.nationality" type="text" />
                <p v-if="form.errors.nationality" class="mt-1 text-sm text-destructive">
                    {{ form.errors.nationality }}
                </p>
            </div>

            <div>
                <Label for="special_request" class="mb-1.5 flex items-center gap-1.5">
                    <MessageSquare class="h-3.5 w-3.5" /> Special Request
                </Label>
                <Textarea id="special_request" v-model="form.special_request" rows="3" />
                <p v-if="form.errors.special_request" class="mt-1 text-sm text-destructive">
                    {{ form.errors.special_request }}
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button as-child variant="outline" type="button">
                    <Link :href="tourist.bookings.index().url">Cancel</Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>