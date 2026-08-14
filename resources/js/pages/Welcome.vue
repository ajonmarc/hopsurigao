<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { login, register } from '@/routes';
import { useRole } from '@/composables/useRole';
import AppLogo from '@/components/AppLogo.vue';

const { isAdmin, isOperator, isTourist } = useRole();

const homeHref = computed(() => {
    if (isAdmin.value) return '/admin/dashboard';
    if (isOperator.value) return '/operator/dashboard';
    if (isTourist.value) return '/tourist/dashboard';
    return '/';
});
</script>

<template>

    <Head title="Welcome to HopSurigao">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header class="w-full px-6 py-4 lg:px-10">
            <nav class="mx-auto flex max-w-6xl items-center justify-between">
                <Link href="/" class="flex items-center gap-2 text-lg font-medium">
                    <AppLogo />
                </Link>

                <div class="flex items-center gap-4 text-sm">
                    <Link v-if="$page.props.auth.user" :href="homeHref"
                        class="inline-block rounded-sm border border-border px-5 py-1.5 leading-normal text-foreground hover:border-primary/40">
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="login()"
                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 leading-normal text-foreground hover:border-border">
                            Log in
                        </Link>
                        <Link :href="register()"
                            class="inline-block rounded-sm border border-border px-5 py-1.5 leading-normal text-foreground hover:border-primary/40">
                            Register
                        </Link>
                    </template>
                </div>
            </nav>
        </header>

        <main class="relative flex flex-1 items-center justify-center overflow-hidden">
            <div
                class="absolute inset-0 bg-cover bg-center"
                style="background-image: url('/images/auth-bg.jpg')"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-sidebar/90 via-sidebar/50 to-sidebar/60" />

            <div class="relative z-10 mx-auto max-w-3xl px-6 py-24 text-center text-sidebar-foreground lg:py-32">
                <h1 class="text-4xl font-semibold tracking-tight lg:text-5xl">
                    Discover Surigao, one island at a time
                </h1>
                <p class="mt-4 text-base text-sidebar-foreground/80 lg:text-lg">
                    Book island-hopping tours, browse local operators, and plan your next
                    getaway across Surigao City's islands and lagoons — all in one place.
                </p>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <Link v-if="!$page.props.auth.user" :href="register()"
                        class="inline-block rounded-md bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                        Start booking
                    </Link>
                    <Link v-else :href="homeHref"
                        class="inline-block rounded-md bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                        Go to my dashboard
                    </Link>
                    <Link :href="login()" v-if="!$page.props.auth.user"
                        class="inline-block rounded-md border border-sidebar-foreground/30 px-6 py-2.5 text-sm font-medium text-sidebar-foreground hover:border-sidebar-foreground/60">
                        Log in
                    </Link>
                </div>
            </div>
        </main>

        <footer class="w-full border-t border-border px-6 py-6 text-center text-xs text-muted-foreground">
            HopSurigao — Island hopping tours, made simple.
        </footer>
    </div>
</template>