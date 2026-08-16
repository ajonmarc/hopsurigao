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
import { index, store } from '@/routes/admin/pickup-schedules';

defineProps<{
    tourDates: { id: number; label: string }[];
    pickupLocations: { id: number; name: string; address: string | null }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pickup Schedules', href: index() },
            { title: 'Create' },
        ],
    },
});
</script>

<template>
    <Head title="Create Pickup Schedule" />
    <div class="px-4 py-6">
        <div class="mx-auto max-w-4xl">
            <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
                <Link :href="index()">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Pickup Schedules
                </Link>
            </Button>

            <Card>
                <CardHeader>
                    <Heading title="Create Pickup Schedule" description="Assign a pickup time to a location for a tour date" />
                </CardHeader>
                <CardContent>
                    <Form
                        :tour-dates="tourDates"
                        :pickup-locations="pickupLocations"
                        :submit-action="store()"
                        :cancel-href="index().url"
                        submit-label="Create Pickup Schedule"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>