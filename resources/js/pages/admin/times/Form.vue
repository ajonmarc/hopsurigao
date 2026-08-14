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

type TimeFormValues = {
    id?: number;
    tour_date_id: number;
    time: string;
    description: string;
};

const props = defineProps<{
    time?: TimeFormValues;
    tourDates: TourDateOption[];
    selectedTourDateId?: number | string | null;
    submitAction: { url: string; method: 'post' | 'put' };
    submitLabel: string;
    cancelHref?: string;
    onCancel?: () => void;
}>();

const emit = defineEmits<{ success: [] }>();

const selectedTourDate = ref<string | undefined>(
    props.selectedTourDateId 
        ? String(props.selectedTourDateId) 
        : props.time?.tour_date_id 
            ? String(props.time.tour_date_id) 
            : undefined
);

const timeValue = ref<string | undefined>(
    props.time?.time || undefined
);

const descriptionValue = ref<string | undefined>(
    props.time?.description || undefined
);

watch(() => props.time, (newTime) => {
    selectedTourDate.value = newTime?.tour_date_id ? String(newTime.tour_date_id) : undefined;
    timeValue.value = newTime?.time || undefined;
    descriptionValue.value = newTime?.description || undefined;
}, { immediate: true });

const handleTourDateChange = (value: AcceptableValue) => {
    selectedTourDate.value = value === null || value === undefined ? undefined : String(value);
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
        <div class="grid grid-cols-1 gap-4 sm:gap-6">
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

            <div class="grid gap-2">
                <Label for="time">Time</Label>
                <Input
                    id="time"
                    type="time"
                    name="time"
                    :default-value="timeValue"
                    required
                    step="60"
                />
                <InputError :message="errors.time" />
                <p class="text-xs text-muted-foreground">Select the start time for this tour slot.</p>
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Input
                    id="description"
                    name="description"
                    :default-value="descriptionValue"
                    required
                    placeholder="e.g. Morning tour, Afternoon tour, Sunset tour, etc."
                />
                <InputError :message="errors.description" />
                <p class="text-xs text-muted-foreground">Brief description of this time slot.</p>
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