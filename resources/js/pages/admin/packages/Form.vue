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

type PackageFormValues = {
    id?: number;
    package_name: string;
    destination: string;
    description: string | null;
    image: string | null;
    price: number;
    status: string;
};

const props = defineProps<{
    package?: PackageFormValues;
    submitAction: { url: string; method: 'post' | 'put' };
    submitLabel: string;
    cancelHref?: string;
    onCancel?: () => void;
}>();

const emit = defineEmits<{ success: [] }>();

const selectedStatus = ref<string | undefined>(props.package?.status ?? 'active');
const imagePreview = ref<string | null>(
    props.package?.image ? `/storage/${props.package.image}` : null,
);

watch(() => props.package, (newPkg) => {
    selectedStatus.value = newPkg?.status ?? 'active';
    imagePreview.value = newPkg?.image ? `/storage/${newPkg.image}` : null;
}, { immediate: true });

const handleStatusChange = (value: AcceptableValue) => {
    selectedStatus.value = value === null || value === undefined ? undefined : String(value);
};

const handleImageChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        imagePreview.value = URL.createObjectURL(file);
    }
};
</script>

<template>
    <Form
        :action="submitAction.url"
        :method="submitAction.method === 'put' ? 'post' : submitAction.method"
        :transform="(data) => (submitAction.method === 'put' ? { ...data, _method: 'put' } : data)"
        enctype="multipart/form-data"
        class="flex flex-col gap-6"
        v-slot="{ errors, processing }"
        @success="emit('success')"
    >
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
            <div class="grid gap-2">
                <Label for="package_name">Package Name</Label>
                <Input
                    id="package_name"
                    name="package_name"
                    :default-value="package?.package_name"
                    required
                    placeholder="e.g. Sugba Lagoon Day Tour"
                />
                <InputError :message="errors.package_name" />
            </div>

            <div class="grid gap-2">
                <Label for="destination">Destination</Label>
                <Input
                    id="destination"
                    name="destination"
                    :default-value="package?.destination"
                    required
                    placeholder="e.g. Sugba Lagoon, Siargao"
                />
                <InputError :message="errors.destination" />
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="description">Description</Label>
                <Textarea
                    id="description"
                    name="description"
                    :default-value="package?.description ?? ''"
                    rows="4"
                    placeholder="Describe what's included in this tour..."
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid gap-2">
                <Label for="price">Price (₱)</Label>
                <Input
                    id="price"
                    type="number"
                    step="0.01"
                    min="0"
                    name="price"
                    :default-value="package?.price"
                    required
                    placeholder="0.00"
                />
                <InputError :message="errors.price" />
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
            </div>

            <div class="grid gap-2 sm:col-span-2">
                <Label for="image">
                    Image
                    <span v-if="package" class="text-muted-foreground">(leave blank to keep current)</span>
                </Label>
                <Input id="image" type="file" name="image" accept="image/*" @change="handleImageChange" />
                <InputError :message="errors.image" />
                <img
                    v-if="imagePreview"
                    :src="imagePreview"
                    alt="Package preview"
                    class="mt-2 h-32 w-full max-w-xs rounded-md border object-cover"
                />
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