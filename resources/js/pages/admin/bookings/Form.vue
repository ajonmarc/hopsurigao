<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
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
import { Spinner } from '@/components/ui/spinner';
import { ref, watch } from 'vue';
import type { AcceptableValue } from 'reka-ui';

type TourDateOption = {
    id: number;
    label: string;
};

type PickupLocationOption = {
    id: number;
    name: string;
    address: string | null;
};

type UserOption = {
    id: number;
    name: string;
    email: string;
};

type BookingFormValues = {
    id?: number;
    user_id: number;
    tour_date_id: number;
    pickup_location_id: number;
    number_of_guests: number;
    phone_number: string;
    nationality: string;
    special_request: string | null;
    booking_status: string;
};

const props = defineProps<{
    booking?: BookingFormValues;
    tourDates: TourDateOption[];
    pickupLocations: PickupLocationOption[];
    users: UserOption[];
    selectedTourDateId?: number | string | null;
    submitAction: { url: string; method: 'post' | 'put' };
    submitLabel: string;
    cancelHref?: string;
    onCancel?: () => void;
}>();

const emit = defineEmits<{ success: [] }>();

const selectedUser = ref<string | undefined>(
    props.booking?.user_id ? String(props.booking.user_id) : undefined
);

const selectedTourDate = ref<string | undefined>(
    props.selectedTourDateId 
        ? String(props.selectedTourDateId) 
        : props.booking?.tour_date_id 
            ? String(props.booking.tour_date_id) 
            : undefined
);

const selectedPickupLocation = ref<string | undefined>(
    props.booking?.pickup_location_id 
        ? String(props.booking.pickup_location_id) 
        : undefined
);

const selectedStatus = ref<string | undefined>(
    props.booking?.booking_status ?? 'pending'
);

watch(() => props.booking, (newBooking) => {
    selectedUser.value = newBooking?.user_id ? String(newBooking.user_id) : undefined;
    selectedTourDate.value = newBooking?.tour_date_id ? String(newBooking.tour_date_id) : undefined;
    selectedPickupLocation.value = newBooking?.pickup_location_id ? String(newBooking.pickup_location_id) : undefined;
    selectedStatus.value = newBooking?.booking_status ?? 'pending';
}, { immediate: true });

const handleUserChange = (value: AcceptableValue) => {
    selectedUser.value = value === null || value === undefined ? undefined : String(value);
};

const handleTourDateChange = (value: AcceptableValue) => {
    selectedTourDate.value = value === null || value === undefined ? undefined : String(value);
};

const handlePickupLocationChange = (value: AcceptableValue) => {
    selectedPickupLocation.value = value === null || value === undefined ? undefined : String(value);
};

const handleStatusChange = (value: AcceptableValue) => {
    selectedStatus.value = value === null || value === undefined ? undefined : String(value);
};
</script>

<template>
    <Form
        :action="submitAction.url"
        :method="submitAction.method === 'put' ? 'post' : submitAction.method"
        :transform="(data) => (submitAction.method === 'put' ? { ...data, _method: 'put' } : data)"
        class="flex flex-col gap-6"
        v-slot="{ errors, processing }"
        @success="emit('success')"
    >
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
            <!-- User -->
            <div class="grid gap-2">
                <Label for="user_id">Guest</Label>
                <input type="hidden" name="user_id" :value="selectedUser" />
                <Select :model-value="selectedUser" @update:model-value="handleUserChange">
                    <SelectTrigger id="user_id" class="w-full">
                        <SelectValue placeholder="Select a guest" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem 
                            v-for="user in users" 
                            :key="user.id" 
                            :value="String(user.id)"
                        >
                            {{ user.name }} ({{ user.email }})
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.user_id" />
            </div>

            <!-- Tour Date -->
            <div class="grid gap-2">
                <Label for="tour_date_id">Tour Date</Label>
                <input type="hidden" name="tour_date_id" :value="selectedTourDate" />
                <Select :model-value="selectedTourDate" @update:model-value="handleTourDateChange">
                    <SelectTrigger id="tour_date_id" class="w-full">
                        <SelectValue placeholder="Select a tour date" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem 
                            v-for="tourDate in tourDates" 
                            :key="tourDate.id" 
                            :value="String(tourDate.id)"
                        >
                            {{ tourDate.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.tour_date_id" />
            </div>

            <!-- Pickup Location -->
            <div class="grid gap-2">
                <Label for="pickup_location_id">Pickup Location</Label>
                <input type="hidden" name="pickup_location_id" :value="selectedPickupLocation" />
                <Select :model-value="selectedPickupLocation" @update:model-value="handlePickupLocationChange">
                    <SelectTrigger id="pickup_location_id" class="w-full">
                        <SelectValue placeholder="Select pickup location" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem 
                            v-for="location in pickupLocations" 
                            :key="location.id" 
                            :value="String(location.id)"
                        >
                            {{ location.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.pickup_location_id" />
            </div>

            <!-- Number of Guests -->
            <div class="grid gap-2">
                <Label for="number_of_guests">Number of Guests</Label>
                <Input
                    id="number_of_guests"
                    type="number"
                    step="1"
                    min="1"
                    name="number_of_guests"
                    :default-value="booking?.number_of_guests"
                    required
                    placeholder="e.g. 2"
                />
                <InputError :message="errors.number_of_guests" />
            </div>

            <!-- Phone Number -->
            <div class="grid gap-2">
                <Label for="phone_number">Phone Number</Label>
                <Input
                    id="phone_number"
                    name="phone_number"
                    :default-value="booking?.phone_number"
                    required
                    placeholder="e.g. +63 912 345 6789"
                />
                <InputError :message="errors.phone_number" />
            </div>

            <!-- Nationality -->
            <div class="grid gap-2">
                <Label for="nationality">Nationality</Label>
                <Input
                    id="nationality"
                    name="nationality"
                    :default-value="booking?.nationality"
                    required
                    placeholder="e.g. Filipino, American, etc."
                />
                <InputError :message="errors.nationality" />
            </div>

            <!-- Booking Status -->
            <div class="grid gap-2">
                <Label for="booking_status">Booking Status</Label>
                <input type="hidden" name="booking_status" :value="selectedStatus" />
                <Select :model-value="selectedStatus" @update:model-value="handleStatusChange">
                    <SelectTrigger id="booking_status" class="w-full">
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="confirmed">Confirmed</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.booking_status" />
            </div>

            <!-- Special Request -->
            <div class="grid gap-2 sm:col-span-2">
                <Label for="special_request">Special Request</Label>
                <Textarea
                    id="special_request"
                    name="special_request"
                    :default-value="booking?.special_request ?? ''"
                    rows="3"
                    placeholder="e.g. Dietary restrictions, wheelchair access, etc."
                />
                <InputError :message="errors.special_request" />
                <p class="text-xs text-muted-foreground">Optional: Any special requests or requirements.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <Button v-if="cancelHref" as-child variant="outline" type="button" :disabled="processing">
                <Link :href="cancelHref">Cancel</Link>
            </Button>
            <Button v-else-if="onCancel" variant="outline" type="button" :disabled="processing" @click="onCancel">
                Cancel
            </Button>

            <Button type="submit" :disabled="processing">
                <Spinner v-if="processing" />
                {{ submitLabel }}
            </Button>
        </div>
    </Form>
</template>