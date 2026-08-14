<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
} from '@/components/ui/card';
import Form from './Form.vue';
import { index, store } from '@/routes/admin/times';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Times', href: index() },
            { title: 'Create' },
        ],
    },
});

defineProps<{
    tourDates: { id: number; label: string }[];
    selectedTourDateId?: string | number | null;
}>();
</script>

<template>
    <Head title="Create Time Slot" />
    <div class="px-4 py-6">
        <div class="mx-auto max-w-4xl">
            <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
                <Link :href="index()">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Time Slots
                </Link>
            </Button>

            <Card>
                <CardHeader>
                    <Heading title="Create Time Slot" description="Add a new available time for a tour date" />
                </CardHeader>
                <CardContent>
                    <Form
                        :submit-action="store()"
                        :tour-dates="tourDates"
                        :selected-tour-date-id="selectedTourDateId"
                        :cancel-href="index().url"
                        submit-label="Create Time Slot"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>