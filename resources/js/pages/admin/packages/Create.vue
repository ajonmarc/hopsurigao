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
import { index, store } from '@/routes/admin/packages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Packages', href: index() },
            { title: 'Create' },
        ],
    },
});
</script>

<template>
    <Head title="Create Package" />
    <div class="px-4 py-6">
        <div class="mx-auto max-w-4xl">
            <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
                <Link :href="index()">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back to Packages
                </Link>
            </Button>

            <Card>
                <CardHeader>
                    <Heading title="Create Package" description="Add a new tour package" />
                </CardHeader>
                <CardContent>
                    <Form
                        :submit-action="store()"
                        :cancel-href="index().url"
                        submit-label="Create Package"
                    />
                </CardContent>
            </Card>
        </div>
    </div>
</template>