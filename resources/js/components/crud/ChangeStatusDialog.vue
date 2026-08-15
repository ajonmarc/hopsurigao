<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import type { AcceptableValue } from 'reka-ui';

const props = defineProps<{
    open: boolean;
    bookingId: number | null;
    currentStatus: string | null;
    guestName?: string | null;
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'updated'): void;
}>();

const selectedStatus = ref<string | undefined>(props.currentStatus ?? undefined);
const processing = ref(false);

watch(
    () => props.currentStatus,
    (val) => {
        selectedStatus.value = val ?? undefined;
    },
);

const handleStatusChange = (value: AcceptableValue) => {
    selectedStatus.value = value === null || value === undefined ? undefined : String(value);
};

const handleSubmit = () => {
    if (!props.bookingId || !selectedStatus.value) return;
    processing.value = true;

    router.put(
        `/admin/bookings/${props.bookingId}/status`,
        { booking_status: selectedStatus.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('updated');
                emit('update:open', false);
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
};

const handleCancel = () => {
    emit('update:open', false);
};
</script>

<template>
    <Dialog :open="open" @update:open="(v) => emit('update:open', v)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Change Booking Status</DialogTitle>
                <DialogDescription>
                    {{ guestName ? `Update the status for ${guestName}'s booking.` : 'Update the status for this booking.' }}
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-2 py-2">
                <Select :model-value="selectedStatus" @update:model-value="handleStatusChange">
                    <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="pending">Pending</SelectItem>
                        <SelectItem value="confirmed">Confirmed</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                        <SelectItem value="completed">Completed</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <DialogFooter>
                <Button variant="outline" :disabled="processing" @click="handleCancel">
                    Cancel
                </Button>
                <Button :disabled="processing || !selectedStatus" @click="handleSubmit">
                    <span v-if="processing">Saving...</span>
                    <span v-else>Save Status</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>