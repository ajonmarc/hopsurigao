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
import { index, store } from '@/routes/admin/tour-dates';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Tour Dates', href: index() },
            { title: 'Create' },
        ],
    },
});

defineProps<{
    packages: { id: number; package_name: string }[];
    selectedPackageId?: string | number | null;
}>();
</script>

<template>
    <Head title="Create Tour Date" />
    <div class="px-4 py-6">
        <div class="mx-auto max-w-4xl">
            <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
                <Link :href="index()">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Tour Dates
                </Link>
            </Button>

            <Card>
                <CardHeader>
                    <Heading title="Create Tour Date" description="Add a new available date for a tour package" />
                </CardHeader>
                <CardContent>
                    <Form
                        :submit-action="store()"
                        :packages="packages"
                        :selected-package-id="selectedPackageId"
                        :cancel-href="index().url"
                        submit-label="Create Tour Date"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>