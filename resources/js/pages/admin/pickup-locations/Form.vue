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

type PickupLocationFormValues = {
    id?: number;
    name: string;
    address: string | null;
    description: string | null;
    status: string;
};

const props = defineProps<{
    pickupLocation?: PickupLocationFormValues;
    submitAction: { url: string; method: 'post' | 'put' };
    submitLabel: string;
    cancelHref?: string;
    onCancel?: () => void;
}>();

const emit = defineEmits<{ success: [] }>();

const selectedStatus = ref<string | undefined>(props.pickupLocation?.status ?? 'active');

watch(() => props.pickupLocation, (newLocation) => {
    selectedStatus.value = newLocation?.status ?? 'active';
}, { immediate: true });

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
        <div class="grid grid-cols-1 gap-4 sm:gap-6">
            <div class="grid gap-2">
                <Label for="name">Location Name</Label>
                <Input
                    id="name"
                    name="name"
                    :default-value="pickupLocation?.name"
                    required
                    placeholder="e.g. Siargao Airport, General Luna Port, etc."
                />
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="address">Address</Label>
                <Input
                    id="address"
                    name="address"
                    :default-value="pickupLocation?.address ?? ''"
                    placeholder="e.g. Brgy. Catangnan, General Luna, Siargao"
                />
                <InputError :message="errors.address" />
                <p class="text-xs text-muted-foreground">Full address of the pickup location (optional).</p>
            </div>

            <div class="grid gap-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="pickupLocation?.description ?? ''"
                    rows="3"
                    placeholder="e.g. Meet at the airport arrival area with a sign, or at the port terminal."
                />
                <InputError :message="errors.description" />
                <p class="text-xs text-muted-foreground">Additional details about this pickup location (optional).</p>
            </div>

            <div class="grid gap-2">
                <Label for="status">Status</Label>
                <input type="hidden" name="status" :value="selectedStatus" />
                <Select :model-value="selectedStatus" @update:model-value="handleStatusChange">
                    <SelectTrigger id="status" class="w-full">
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="inactive">Inactive</SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="errors.status" />
                <p class="text-xs text-muted-foreground">Only active locations will appear in booking forms.</p>
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