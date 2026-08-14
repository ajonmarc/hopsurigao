//resources/js/pages/tourist/packages/Index.vue

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Search, Filter, Package, MapPin, Star, Users } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { debounce } from '@/lib/debounce';
import tourist from '@/routes/tourist';
import type { AcceptableValue } from 'reka-ui';

const props = defineProps<{
    packages: {
        data: Array<{
            id: number;
            package_name: string;
            destination: string;
            description: string;
            image: string | null;
            price: number;
            status: string;
            tour_dates_count: number;
        }>;
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    destinations: string[];
    filters?: {
        search?: string;
        destination?: string;
        min_price?: string;
        max_price?: string;
        sort?: string;
        per_page?: number | string;
    };
}>();

const searchInput = ref(props.filters?.search ?? '');
const selectedDestination = ref(props.filters?.destination ?? '');
const minPrice = ref(props.filters?.min_price ?? '');
const maxPrice = ref(props.filters?.max_price ?? '');
const sortBy = ref(props.filters?.sort ?? '');
const perPage = ref(String(props.filters?.per_page ?? 12));

const debouncedSearch = debounce((value: string) => {
    applyFilters({ search: value });
}, 350);

const applyFilters = (extra?: Record<string, any>) => {
    const params: Record<string, any> = {
        search: searchInput.value || undefined,
        destination: selectedDestination.value || undefined,
        min_price: minPrice.value || undefined,
        max_price: maxPrice.value || undefined,
        sort: sortBy.value || undefined,
        per_page: perPage.value || undefined,
        ...extra,
    };

    // Remove undefined values
    Object.keys(params).forEach(key => {
        if (params[key] === undefined || params[key] === '') {
            delete params[key];
        }
    });

    router.get(tourist.packages.index().url, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const onSearchInput = (event: Event) => {
    const value = (event.target as HTMLInputElement).value;
    searchInput.value = value;
    debouncedSearch(value);
};

const onFilterChange = () => {
    applyFilters();
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

// Helper function to get package detail URL
const getPackageUrl = (id: number) => {
    return `/tourist/packages/${id}`;
};
</script>

<template>
    <Head title="Browse Packages" />
    <div class="px-4 py-6">
        <Heading title="Browse Packages" description="Discover amazing island-hopping tours" />

        <!-- Filters -->
        <div class="mt-6 flex flex-col gap-4 rounded-lg border p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input v-model="searchInput" placeholder="Search packages..." class="pl-9" @input="onSearchInput" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <Select :model-value="selectedDestination || 'all'"
                        @update:model-value="(v) => { selectedDestination = v === 'all' ? '' : v as string; applyFilters(); }">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="Destination" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Destinations</SelectItem>
                            <SelectItem v-for="dest in destinations" :key="dest" :value="dest">
                                {{ dest }}
                            </SelectItem>
                        </SelectContent>
                    </Select>

                    <Select :model-value="sortBy || 'latest'"
                        @update:model-value="(v) => { sortBy = v === 'latest' ? '' : v as string; applyFilters(); }">
                        <SelectTrigger class="w-[150px]">
                            <SelectValue placeholder="Sort by" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="latest">Latest</SelectItem>
                            <SelectItem value="price">Price: Low to High</SelectItem>
                            <SelectItem value="-price">Price: High to Low</SelectItem>
                            <SelectItem value="package_name">Name</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select :model-value="perPage"
                        @update:model-value="(v) => { perPage = v as string; applyFilters(); }">
                        <SelectTrigger class="w-[100px]">
                            <SelectValue placeholder="Per page" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="12">12</SelectItem>
                            <SelectItem value="24">24</SelectItem>
                            <SelectItem value="48">48</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Price Range -->
            <div class="flex flex-wrap items-center gap-4">
                <span class="text-sm text-muted-foreground">Price Range:</span>
                <Input v-model="minPrice" type="number" placeholder="Min" class="w-[100px]" @change="applyFilters" />
                <span class="text-muted-foreground">to</span>
                <Input v-model="maxPrice" type="number" placeholder="Max" class="w-[100px]" @change="applyFilters" />
                <Button variant="ghost" size="sm" @click="minPrice = ''; maxPrice = ''; applyFilters();">
                    Clear
                </Button>
            </div>
        </div>

        <!-- Package Grid -->
        <div v-if="packages.data.length > 0"
            class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <Link v-for="pkg in packages.data" :key="pkg.id" :href="getPackageUrl(pkg.id)"
                class="block transition-transform hover:scale-[1.02]">
                <Card class="h-full overflow-hidden">
                    <div class="relative h-48 overflow-hidden bg-muted">
                        <img v-if="pkg.image" :src="`/storage/${pkg.image}`" :alt="pkg.package_name"
                            class="h-full w-full object-cover" />
                        <div v-else class="flex h-full items-center justify-center">
                            <Package class="h-12 w-12 text-muted-foreground" />
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-3">
                            <div class="flex items-center justify-between text-white">
                                <span class="flex items-center gap-1 text-sm">
                                    <MapPin class="h-3 w-3" />
                                    {{ pkg.destination }}
                                </span>
                                <span class="rounded-full bg-primary/90 px-2 py-0.5 text-xs font-medium">
                                    {{ formatPrice(pkg.price) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <CardHeader class="pb-2">
                        <CardTitle class="line-clamp-1 text-base">{{ pkg.package_name }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="line-clamp-2 text-sm text-muted-foreground">
                            {{ pkg.description }}
                        </p>
                    </CardContent>
                    <CardFooter class="flex items-center justify-between text-sm text-muted-foreground">
                        <span class="flex items-center gap-1">
                            <Users class="h-3 w-3" />
                            {{ pkg.tour_dates_count }} available dates
                        </span>
                    </CardFooter>
                </Card>
            </Link>
        </div>

        <div v-else class="mt-12 text-center">
            <p class="text-muted-foreground">No packages found matching your criteria.</p>
            <Button as-child variant="link" class="mt-2">
                <Link :href="tourist.packages.index().url">Clear filters</Link>
            </Button>
        </div>

        <!-- Pagination -->
        <div v-if="packages.links.length > 3" class="mt-6 flex flex-wrap items-center justify-center gap-1">
            <Link v-for="(link, i) in packages.links" :key="i" :href="link.url ?? '#'" :class="[
                'rounded px-3 py-1 text-sm',
                link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                !link.url && 'pointer-events-none opacity-50',
            ]" v-html="link.label" />
        </div>
    </div>
</template>