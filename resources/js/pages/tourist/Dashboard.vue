<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { MapPin, ClipboardList, Sailboat } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { dashboard } from '@/routes/tourist';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                href: dashboard(),
                title: 'Dashboard',
            },
        ],
    },
});
</script>

<template>

    <Head title="Dashboard" />
    <div class="px-4 py-6">
        <div class="relative overflow-hidden rounded-xl">
            <div
                class="h-48 bg-cover bg-center sm:h-56"
                style="background-image: url('/images/auth-bg.jpg')"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-sidebar/90 via-sidebar/40 to-sidebar/20" />
            <div class="absolute inset-0 flex flex-col justify-end p-6 text-sidebar-foreground">
                <h1 class="text-2xl font-semibold tracking-tight">Welcome back</h1>
                <p class="mt-1 text-sm text-sidebar-foreground/80">
                    Here's a quick look at your island-hopping plans
                </p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <span class="text-sm font-medium text-muted-foreground">Upcoming bookings</span>
                    <ClipboardList class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">0</p>
                    <p class="text-xs text-muted-foreground">No trips booked yet</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <span class="text-sm font-medium text-muted-foreground">Destinations</span>
                    <MapPin class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">Browse</p>
                    <p class="text-xs text-muted-foreground">Explore islands and lagoons</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <span class="text-sm font-medium text-muted-foreground">Tours</span>
                    <Sailboat class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">View all</p>
                    <p class="text-xs text-muted-foreground">Find your next tour</p>
                </CardContent>
            </Card>
        </div>

        <div class="mt-6 max-w-2xl">
            <section class="space-y-4">
                <h2 class="text-lg font-medium">Get started</h2>
                <p class="text-sm text-muted-foreground">
                    Ready for your next trip? Browse available island-hopping tours and book your
                    spot in a few clicks.
                </p>
                <Link href="/tourist/destinations"
                    class="inline-block rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                    Browse destinations
                </Link>
            </section>
        </div>
    </div>
</template>