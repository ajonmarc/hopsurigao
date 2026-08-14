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
import { index, store } from '@/routes/admin/bookings';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Bookings', href: index() },
            { title: 'Create' },
        ],
    },
});

defineProps<{
    tourDates: { id: number; label: string }[];
    pickupLocations: { id: number; name: string; address: string | null }[];
    users: { id: number; name: string; email: string }[];
    selectedTourDateId?: string | number | null;
}>();
</script>

<template>
    <Head title="Create Booking" />
    <div class="px-4 py-6">
        <div class="mx-auto max-w-4xl">
            <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
                <Link :href="index()">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Bookings
                </Link>
            </Button>

            <Card>
                <CardHeader>
                    <Heading title="Create Booking" description="Create a new tour booking for a guest" />
                </CardHeader>
                <CardContent>
                    <Form
                        :submit-action="store()"
                        :tour-dates="tourDates"
                        :pickup-locations="pickupLocations"
                        :users="users"
                        :selected-tour-date-id="selectedTourDateId"
                        :cancel-href="index().url"
                        submit-label="Create Booking"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>