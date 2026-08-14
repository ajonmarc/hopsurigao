<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FlexRender } from '@tanstack/vue-table';
import { computed, ref } from 'vue';
import { Plus, ArrowUp, ArrowDown, Search, Trash2 } from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import FormDialog from '@/components/crud/FormDialog.vue';
import DeleteDialog from '@/components/crud/DeleteDialog.vue';
import BulkDeleteDialog from '@/components/crud/BulkDeleteDialog.vue';
import { useServerTable } from '@/composables/useServerTable';
import { debounce } from '@/lib/debounce';
import { createColumns, type PickupLocationRow } from './columns';
import PickupLocationForm from './Form.vue';
import { index, create, update } from '@/routes/admin/pickup-locations';
import type { AcceptableValue } from 'reka-ui';

const props = defineProps<{
    pickupLocations: {
        data: PickupLocationRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    filters?: { 
        sort?: string; 
        search?: string; 
        per_page?: number | string;
        status?: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Pickup Locations', href: index() }],
    },
});

const pickupLocationsData = computed(() => props.pickupLocations.data);
const sort = computed(() => props.filters?.sort);
const search = computed(() => props.filters?.search);
const statusFilter = computed(() => props.filters?.status);
const searchInput = ref(props.filters?.search ?? '');
const perPage = computed(() => String(props.filters?.per_page ?? 10));

const editingLocation = ref<PickupLocationRow | null>(null);
const deletingLocation = ref<PickupLocationRow | null>(null);
const bulkDeleteOpen = ref(false);

const deleteAction = computed(() => {
    if (!deletingLocation.value) return null;
    return {
        url: `/admin/pickup-locations/${deletingLocation.value.id}`,
        method: 'delete' as const,
    };
});

const editingLocationFormValues = computed(() => {
    if (!editingLocation.value) return undefined;
    return { ...editingLocation.value };
});

const columns = createColumns(
    (location) => (editingLocation.value = location),
    (location) => (deletingLocation.value = location),
);

const { table, runSearch } = useServerTable<PickupLocationRow>({
    data: pickupLocationsData,
    columns,
    baseUrl: index().url,
    sort,
    search,
});

const selectedRows = computed(() => table.getSelectedRowModel().rows);
const selectedCount = computed(() => selectedRows.value.length);
const selectedIds = computed(() => selectedRows.value.map((row) => row.original.id));

const handleBulkDeleteSuccess = () => {
    table.resetRowSelection();
    router.reload();
};

const debouncedSearch = debounce((value: string) => runSearch(value), 350);

const onSearchInput = (event: Event) => {
    const value = (event.target as HTMLInputElement).value;
    debouncedSearch(value);
};

const onPerPageChange = (value: AcceptableValue) => {
    if (value === null || typeof value !== 'string') return;

    router.get(
        index().url,
        { sort: sort.value, search: search.value, per_page: value, status: statusFilter.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onStatusFilterChange = (value: AcceptableValue) => {
    if (value === null || value === undefined) return;
    
    router.get(
        index().url,
        { 
            sort: sort.value, 
            search: search.value, 
            per_page: perPage.value, 
            status: value === 'all' ? undefined : String(value) 
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
</script>

<template>
    <Head title="Pickup Locations" />
    <div class="px-4 py-6">
        <div class="flex items-center justify-between">
            <Heading title="Pickup Locations" description="Manage pickup locations for guest transportation" />
            <Button as-child>
                <Link :href="create()">
                    <Plus class="mr-2 h-4 w-4" />
                    New Location
                </Link>
            </Button>
        </div>

        <div class="mt-6 rounded-lg border">
            <div class="flex flex-col flex-wrap items-start gap-4 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <span>Show</span>
                    <Select :model-value="perPage" @update:model-value="onPerPageChange">
                        <SelectTrigger class="h-9 w-20">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="10">10</SelectItem>
                            <SelectItem value="25">25</SelectItem>
                            <SelectItem value="50">50</SelectItem>
                            <SelectItem value="100">100</SelectItem>
                        </SelectContent>
                    </Select>
                    <span>entries</span>
                </div>

                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center sm:gap-4">
                    <!-- Status Filter -->
                    <div class="relative w-full sm:w-36">
                        <Select 
                            :model-value="statusFilter ?? 'all'" 
                            @update:model-value="onStatusFilterChange"
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Search -->
                    <div class="relative w-full sm:max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="searchInput" placeholder="Search locations..." class="pl-9"
                            @input="onSearchInput" />
                    </div>

                    <div v-if="selectedCount > 0" class="flex items-center justify-between gap-3">
                        <span class="whitespace-nowrap text-sm text-muted-foreground">
                            {{ selectedCount }} selected
                        </span>
                        <Button variant="destructive" size="sm" @click="bulkDeleteOpen = true">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete selected
                        </Button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] border-collapse text-sm">
                    <thead class="bg-muted/50">
                        <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                            <th v-for="header in headerGroup.headers" :key="header.id"
                                class="border-b px-4 py-3 text-left font-medium"
                                :class="header.column.getCanSort() && 'cursor-pointer select-none'"
                                @click="header.column.getCanSort() && header.column.toggleSorting(undefined, $event.shiftKey)">
                                <div class="flex items-center gap-1">
                                    <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                    <ArrowUp v-if="header.column.getIsSorted() === 'asc'" class="h-3 w-3" />
                                    <ArrowDown v-else-if="header.column.getIsSorted() === 'desc'" class="h-3 w-3" />
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in table.getRowModel().rows" :key="row.id"
                            class="[&:not(:last-child)]:border-b hover:bg-muted/30"
                            :data-state="row.getIsSelected() ? 'selected' : undefined">
                            <td v-for="cell in row.getAllCells()" :key="cell.id" class="px-4 py-3">
                                <FlexRender :render="cell.column.columnDef.cell ?? cell.getValue()"
                                    :props="cell.getContext()" />
                            </td>
                        </tr>
                        <tr v-if="pickupLocationsData.length === 0">
                            <td :colspan="columns.length" class="px-4 py-6 text-center text-muted-foreground">
                                No pickup locations found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-row flex-nowrap items-center justify-between gap-4 overflow-x-auto border-t px-4 py-3">
                <p class="shrink-0 whitespace-nowrap text-sm text-muted-foreground">
                    Showing {{ props.pickupLocations.from ?? 0 }} to {{ props.pickupLocations.to ?? 0 }} of {{ props.pickupLocations.total }} locations
                </p>

                <div v-if="props.pickupLocations.links.length > 3" class="flex flex-nowrap gap-1">
                    <Link v-for="(link, i) in props.pickupLocations.links" :key="i" :href="link.url ?? '#'" :class="[
                        'whitespace-nowrap rounded px-3 py-1 text-sm',
                        link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                        !link.url && 'pointer-events-none opacity-50',
                    ]" v-html="link.label" />
                </div>
            </div>
        </div>

        <FormDialog :open="!!editingLocation" title="Edit Pickup Location" content-class="sm:max-w-2xl"
            :description="editingLocation ? `Update ${editingLocation.name}'s details.` : undefined"
            @update:open="(v) => !v && (editingLocation = null)">
            <template #default="{ close }">
                <PickupLocationForm 
                    v-if="editingLocationFormValues" 
                    :pickup-location="editingLocationFormValues"
                    :submit-action="update(editingLocation!.id)" 
                    submit-label="Save Changes" 
                    :on-cancel="close"
                    @success="editingLocation = null" 
                />
            </template>
        </FormDialog>

        <DeleteDialog :open="!!deletingLocation" :action="deleteAction" :description="deletingLocation
            ? `This will permanently delete ${deletingLocation.name}. This action cannot be undone.`
            : ''
            " @update:open="(v) => !v && (deletingLocation = null)" />

        <BulkDeleteDialog :open="bulkDeleteOpen" :count="selectedCount" :ids="selectedIds" item-label="pickup location"
            action="/admin/pickup-locations-bulk-destroy" @update:open="bulkDeleteOpen = $event"
            @deleted="handleBulkDeleteSuccess" />
    </div>
</template>