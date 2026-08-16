<script setup lang="ts">
import { Form, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

type PickupScheduleFormValues = {
    id?: number;
    tour_date_id: number;
    pickup_location_id: number;
    pickup_time: string;
};

const props = defineProps<{
    pickupSchedule?: PickupScheduleFormValues;
    tourDates: TourDateOption[];
    pickupLocations: PickupLocationOption[];
    submitAction: { url: string; method: 'post' | 'put' };
    submitLabel: string;
    cancelHref?: string;
    onCancel?: () => void;
}>();

const emit = defineEmits<{ success: [] }>();

const selectedTourDate = ref<string | undefined>(
    props.pickupSchedule?.tour_date_id ? String(props.pickupSchedule.tour_date_id) : undefined,
);

const selectedPickupLocation = ref<string | undefined>(
    props.pickupSchedule?.pickup_location_id ? String(props.pickupSchedule.pickup_location_id) : undefined,
);

watch(
    () => props.pickupSchedule,
    (newVal) => {
        selectedTourDate.value = newVal?.tour_date_id ? String(newVal.tour_date_id) : undefined;
        selectedPickupLocation.value = newVal?.pickup_location_id ? String(newVal.pickup_location_id) : undefined;
    },
    { immediate: true },
);

const handleTourDateChange = (value: AcceptableValue) => {
    selectedTourDate.value = value === null || value === undefined ? undefined : String(value);
};

const handlePickupLocationChange = (value: AcceptableValue) => {
    selectedPickupLocation.value = value === null || value === undefined ? undefined : String(value);
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
            <!-- Tour Date -->
            <div class="grid gap-2 sm:col-span-2">
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
            <div class="grid gap-2 sm:col-span-2">
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
                            <span v-if="location.address" class="ml-2 text-muted-foreground">
                                ({{ location.address }})
                            </span>
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.pickup_location_id" />
                <p class="text-xs text-muted-foreground">
                    Only one schedule is allowed per location, per tour date.
                </p>
            </div>

            <!-- Pickup Time -->
            <div class="grid gap-2 sm:col-span-2">
                <Label for="pickup_time">Pickup Time</Label>
                <Input
                    id="pickup_time"
                    type="time"
                    name="pickup_time"
                    :default-value="pickupSchedule?.pickup_time"
                    required
                />
                <InputError :message="errors.pickup_time" />
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