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
import { computed, ref, watch } from 'vue';
import type { AcceptableValue } from 'reka-ui';

type TourDateOption = {
    id: number;
    label: string;
};

// CHANGED: was PickupLocationOption. Each row = one pickup_schedules record,
// tagged with tour_date_id so we can filter it down client-side.
type PickupScheduleOption = {
    id: number;
    tour_date_id: number;
    pickup_location_id: number;
    label: string;
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
    pickup_schedule_id: number; // CHANGED from pickup_location_id
    number_of_guests: number;
    phone_number: string;
    nationality: string;
    special_request: string | null;
    booking_status: string;
};

const props = defineProps<{
    booking?: BookingFormValues;
    tourDates: TourDateOption[];
    pickupSchedules: PickupScheduleOption[]; // CHANGED from pickupLocations
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

const selectedPickupSchedule = ref<string | undefined>(
    props.booking?.pickup_schedule_id
        ? String(props.booking.pickup_schedule_id)
        : undefined
);

// NEW: only show pickup schedules that belong to the currently selected tour date
const filteredPickupSchedules = computed(() => {
    if (!selectedTourDate.value) return [];
    return props.pickupSchedules.filter(
        (schedule) => String(schedule.tour_date_id) === selectedTourDate.value
    );
});

watch(() => props.booking, (newBooking) => {
    selectedUser.value = newBooking?.user_id ? String(newBooking.user_id) : undefined;
    selectedTourDate.value = newBooking?.tour_date_id ? String(newBooking.tour_date_id) : undefined;
    selectedPickupSchedule.value = newBooking?.pickup_schedule_id
        ? String(newBooking.pickup_schedule_id)
        : undefined;
}, { immediate: true });

const handleUserChange = (value: AcceptableValue) => {
    selectedUser.value = value === null || value === undefined ? undefined : String(value);
};

const handleTourDateChange = (value: AcceptableValue) => {
    const newValue = value === null || value === undefined ? undefined : String(value);
    selectedTourDate.value = newValue;

    // NEW: if the currently selected pickup schedule no longer belongs to
    // the newly selected tour date, clear it so a stale value can't be submitted.
    const stillValid = props.pickupSchedules.some(
        (schedule) =>
            String(schedule.id) === selectedPickupSchedule.value &&
            String(schedule.tour_date_id) === newValue
    );
    if (!stillValid) {
        selectedPickupSchedule.value = undefined;
    }
};

const handlePickupScheduleChange = (value: AcceptableValue) => {
    selectedPickupSchedule.value = value === null || value === undefined ? undefined : String(value);
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

            <!-- Pickup Schedule (CHANGED: was Pickup Location) -->
            <div class="grid gap-2">
                <Label for="pickup_schedule_id">Pickup Schedule</Label>
                <input type="hidden" name="pickup_schedule_id" :value="selectedPickupSchedule" />
                <Select
                    :model-value="selectedPickupSchedule"
                    :disabled="!selectedTourDate"
                    @update:model-value="handlePickupScheduleChange"
                >
                    <SelectTrigger id="pickup_schedule_id" class="w-full">
                        <SelectValue
                            :placeholder="selectedTourDate ? 'Select pickup location & time' : 'Select a tour date first'"
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="schedule in filteredPickupSchedules"
                            :key="schedule.id"
                            :value="String(schedule.id)"
                        >
                            {{ schedule.label }}
                        </SelectItem>
                        <p v-if="selectedTourDate && filteredPickupSchedules.length === 0" class="px-2 py-1.5 text-xs text-muted-foreground">
                            No pickup schedules for this tour date.
                        </p>
                    </SelectContent>
                </Select>
                <InputError :message="errors.pickup_schedule_id" />
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
            <!-- add near the other hidden inputs -->
<input type="hidden" name="booking_status" :value="booking?.booking_status ?? 'pending'" />
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