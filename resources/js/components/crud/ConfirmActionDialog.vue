<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const props = withDefaults(
    defineProps<{
        open: boolean;
        title?: string;
        description?: string;
        confirmLabel?: string;
        cancelLabel?: string;
        confirmClass?: string;
        action: { url: string; method: 'put' | 'post' | 'patch' | 'delete' } | null;
    }>(),
    {
        title: 'Are you sure?',
        description: '',
        confirmLabel: 'Confirm',
        cancelLabel: 'Cancel',
        confirmClass: '',
    },
);

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirmed'): void;
}>();

const processing = ref(false);

const handleConfirm = () => {
    if (!props.action) return;
    processing.value = true;

    const { url, method } = props.action;

    router[method](
        url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('confirmed');
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
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" :disabled="processing" @click="handleCancel">
                    {{ cancelLabel }}
                </Button>
                <Button :class="confirmClass" :disabled="processing" @click="handleConfirm">
                    <span v-if="processing">Processing...</span>
                    <span v-else>{{ confirmLabel }}</span>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>