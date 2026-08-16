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
import { createColumns, type PickupScheduleRow } from './columns';
import PickupScheduleForm from './Form.vue';
import { index, create, update } from '@/routes/admin/pickup-schedules';
import type { AcceptableValue } from 'reka-ui';

const props = defineProps<{
    pickupSchedules: {
        data: PickupScheduleRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    tourDates: { id: number; label: string }[];
    pickupLocations: { id: number; name: string; address: string | null }[];
    filters?: {
        sort?: string;
        search?: string;
        per_page?: number | string;
        tour_date_id?: string;
        pickup_location_id?: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Pickup Schedules', href: index() }],
    },
});

const scheduleData = computed(() => props.pickupSchedules.data);
const sort = computed(() => props.filters?.sort);
const search = computed(() => props.filters?.search);
const tourDateFilter = computed(() => props.filters?.tour_date_id);
const pickupLocationFilter = computed(() => props.filters?.pickup_location_id);
const searchInput = ref(props.filters?.search ?? '');
const perPage = computed(() => String(props.filters?.per_page ?? 10));

const editingSchedule = ref<PickupScheduleRow | null>(null);
const deletingSchedule = ref<PickupScheduleRow | null>(null);
const bulkDeleteOpen = ref(false);

const deleteAction = computed(() => {
    if (!deletingSchedule.value) return null;
    return {
        url: `/admin/pickup-schedules/${deletingSchedule.value.id}`,
        method: 'delete' as const,
    };
});

const editingScheduleFormValues = computed(() => {
    if (!editingSchedule.value) return undefined;
    return {
        id: editingSchedule.value.id,
        tour_date_id: editingSchedule.value.tour_date_id,
        pickup_location_id: editingSchedule.value.pickup_location_id,
        pickup_time: editingSchedule.value.pickup_time,
    };
});

const columns = createColumns(
    (schedule) => (editingSchedule.value = schedule),
    (schedule) => (deletingSchedule.value = schedule),
);

const { table, runSearch } = useServerTable<PickupScheduleRow>({
    data: scheduleData,
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
        {
            sort: sort.value,
            search: search.value,
            per_page: value,
            tour_date_id: tourDateFilter.value,
            pickup_location_id: pickupLocationFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onTourDateFilterChange = (value: AcceptableValue) => {
    if (value === null || value === undefined) return;

    router.get(
        index().url,
        {
            sort: sort.value,
            search: search.value,
            per_page: perPage.value,
            tour_date_id: value === 'all' ? undefined : String(value),
            pickup_location_id: pickupLocationFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onPickupLocationFilterChange = (value: AcceptableValue) => {
    if (value === null || value === undefined) return;

    router.get(
        index().url,
        {
            sort: sort.value,
            search: search.value,
            per_page: perPage.value,
            tour_date_id: tourDateFilter.value,
            pickup_location_id: value === 'all' ? undefined : String(value),
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
</script>

<template>
    <Head title="Pickup Schedules" />
    <div class="px-4 py-6">
        <div class="flex items-center justify-between">
            <Heading title="Pickup Schedules" description="Manage pickup times per tour date and location" />
            <Button as-child>
                <Link :href="create()">
                    <Plus class="mr-2 h-4 w-4" />
                    New Pickup Schedule
                </Link>
            </Button>
        </div>

        <div class="mt-6 rounded-lg border">
            <div
                class="flex flex-col flex-wrap items-start gap-4 border-b px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
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
                    <div class="relative w-full sm:w-48">
                        <Select :model-value="tourDateFilter ?? 'all'" @update:model-value="onTourDateFilterChange">
                            <SelectTrigger>
                                <SelectValue placeholder="All Tour Dates" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Tour Dates</SelectItem>
                                <SelectItem v-for="tourDate in tourDates" :key="tourDate.id"
                                    :value="String(tourDate.id)">
                                    {{ tourDate.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="relative w-full sm:w-48">
                        <Select :model-value="pickupLocationFilter ?? 'all'"
                            @update:model-value="onPickupLocationFilterChange">
                            <SelectTrigger>
                                <SelectValue placeholder="All Pickup Locations" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Pickup Locations</SelectItem>
                                <SelectItem v-for="location in pickupLocations" :key="location.id"
                                    :value="String(location.id)">
                                    {{ location.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="relative w-full sm:max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="searchInput" placeholder="Search by package or location..." class="pl-9"
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
                        <tr v-if="scheduleData.length === 0">
                            <td :colspan="columns.length" class="px-4 py-6 text-center text-muted-foreground">
                                No pickup schedules found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-row flex-nowrap items-center justify-between gap-4 overflow-x-auto border-t px-4 py-3">
                <p class="shrink-0 whitespace-nowrap text-sm text-muted-foreground">
                    Showing {{ props.pickupSchedules.from ?? 0 }} to {{ props.pickupSchedules.to ?? 0 }} of
                    {{ props.pickupSchedules.total }} pickup schedules
                </p>

                <div v-if="props.pickupSchedules.links.length > 3" class="flex flex-nowrap gap-1">
                    <Link v-for="(link, i) in props.pickupSchedules.links" :key="i" :href="link.url ?? '#'" :class="[
                        'whitespace-nowrap rounded px-3 py-1 text-sm',
                        link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                        !link.url && 'pointer-events-none opacity-50',
                    ]" v-html="link.label" />
                </div>
            </div>
        </div>

        <!-- Edit Dialog -->
        <FormDialog :open="!!editingSchedule" title="Edit Pickup Schedule" content-class="sm:max-w-xl"
            description="Update this pickup schedule." @update:open="(v) => !v && (editingSchedule = null)">
            <template #default="{ close }">
                <PickupScheduleForm v-if="editingScheduleFormValues" :pickup-schedule="editingScheduleFormValues"
                    :tour-dates="tourDates" :pickup-locations="pickupLocations"
                    :submit-action="update(editingSchedule!.id)" submit-label="Save Changes" :on-cancel="close"
                    @success="editingSchedule = null" />
            </template>
        </FormDialog>

        <DeleteDialog :open="!!deletingSchedule" :action="deleteAction" :description="deletingSchedule
            ? `This will permanently delete the pickup schedule for ${deletingSchedule.pickup_location?.name}. This action cannot be undone.`
            : ''
            " @update:open="(v) => !v && (deletingSchedule = null)" />

        <BulkDeleteDialog :open="bulkDeleteOpen" :count="selectedCount" :ids="selectedIds"
            item-label="pickup schedule" action="/admin/pickup-schedules-bulk-destroy"
            @update:open="bulkDeleteOpen = $event" @deleted="handleBulkDeleteSuccess" />
    </div>
</template>