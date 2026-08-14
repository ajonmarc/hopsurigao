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

type PackageOption = {
    id: number;
    package_name: string;
};

type TourDateFormValues = {
    id?: number;
    package_id: number;
    tour_date: string;
    capacity: number;
};

const props = defineProps<{
    tourDate?: TourDateFormValues;
    packages: PackageOption[];
    selectedPackageId?: number | string | null;
    submitAction: { url: string; method: 'post' | 'put' };
    submitLabel: string;
    cancelHref?: string;
    onCancel?: () => void;
}>();

const emit = defineEmits<{ success: [] }>();

const selectedPackage = ref<string | undefined>(
    props.selectedPackageId 
        ? String(props.selectedPackageId) 
        : props.tourDate?.package_id 
            ? String(props.tourDate.package_id) 
            : undefined
);

const tourDateValue = ref<string | undefined>(
    props.tourDate?.tour_date || undefined
);

const capacityValue = ref<number | undefined>(
    props.tourDate?.capacity || undefined
);

watch(() => props.tourDate, (newTourDate) => {
    selectedPackage.value = newTourDate?.package_id ? String(newTourDate.package_id) : undefined;
    tourDateValue.value = newTourDate?.tour_date || undefined;
    capacityValue.value = newTourDate?.capacity || undefined;
}, { immediate: true });

const handlePackageChange = (value: AcceptableValue) => {
    selectedPackage.value = value === null || value === undefined ? undefined : String(value);
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
                <Label for="package_id">Package</Label>
                <input type="hidden" name="package_id" :value="selectedPackage" />
                <Select :model-value="selectedPackage" @update:model-value="handlePackageChange">
                    <SelectTrigger id="package_id" class="w-full">
                        <SelectValue placeholder="Select a package" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem 
                            v-for="pkg in packages" 
                            :key="pkg.id" 
                            :value="String(pkg.id)"
                        >
                            {{ pkg.package_name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.package_id" />
            </div>

            <div class="grid gap-2">
                <Label for="tour_date">Tour Date</Label>
                <Input
                    id="tour_date"
                    type="date"
                    name="tour_date"
                    :default-value="tourDateValue"
                    required
                    :min="new Date().toISOString().split('T')[0]"
                />
                <InputError :message="errors.tour_date" />
                <p class="text-xs text-muted-foreground">Select the date this tour will be available.</p>
            </div>

            <div class="grid gap-2">
                <Label for="capacity">Maximum Capacity</Label>
                <Input
                    id="capacity"
                    type="number"
                    step="1"
                    min="1"
                    max="999"
                    name="capacity"
                    :default-value="capacityValue"
                    required
                    placeholder="e.g. 20"
                />
                <InputError :message="errors.capacity" />
                <p class="text-xs text-muted-foreground">Maximum number of guests allowed for this date.</p>
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