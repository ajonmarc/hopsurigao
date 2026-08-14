<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FlexRender } from '@tanstack/vue-table';
import { computed, ref } from 'vue';
import { Plus, ArrowUp, ArrowDown, Search, Trash2, CalendarRange, Eye } from '@lucide/vue';
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
import { createColumns, type BookingRow } from './columns';
import BookingForm from './Form.vue';
import BookingView from './View.vue';
import { index, create, update } from '@/routes/admin/bookings';
import type { AcceptableValue } from 'reka-ui';

const props = defineProps<{
    bookings: {
        data: BookingRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    packages: { id: number; package_name: string }[];
    tourDates: { id: number; label: string }[];
    pickupLocations: { id: number; name: string; address: string | null }[];
    users: { id: number; name: string; email: string }[];
    filters?: {
        sort?: string;
        search?: string;
        per_page?: number | string;
        booking_status?: string;
        package_id?: string;
        tour_date_id?: string;
        from_date?: string;
        to_date?: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Bookings', href: index() }],
    },
});

const bookingsData = computed(() => props.bookings.data);
const sort = computed(() => props.filters?.sort);
const search = computed(() => props.filters?.search);
const statusFilter = computed(() => props.filters?.booking_status);
const packageFilter = computed(() => props.filters?.package_id);
const tourDateFilter = computed(() => props.filters?.tour_date_id);
const fromDateFilter = computed(() => props.filters?.from_date);
const toDateFilter = computed(() => props.filters?.to_date);
const searchInput = ref(props.filters?.search ?? '');
const perPage = computed(() => String(props.filters?.per_page ?? 10));

const editingBooking = ref<BookingRow | null>(null);
const deletingBooking = ref<BookingRow | null>(null);
const viewingBooking = ref<BookingRow | null>(null);
const bulkDeleteOpen = ref(false);
const viewDetailsOpen = ref(false);

const deleteAction = computed(() => {
    if (!deletingBooking.value) return null;
    return {
        url: `/admin/bookings/${deletingBooking.value.id}`,
        method: 'delete' as const,
    };
});

const editingBookingFormValues = computed(() => {
    if (!editingBooking.value) return undefined;
    return {
        id: editingBooking.value.id,
        user_id: editingBooking.value.user_id,
        tour_date_id: editingBooking.value.tour_date_id,
        pickup_location_id: editingBooking.value.pickup_location_id,
        number_of_guests: editingBooking.value.number_of_guests,
        phone_number: editingBooking.value.phone_number,
        nationality: editingBooking.value.nationality,
        special_request: editingBooking.value.special_request,
        booking_status: editingBooking.value.booking_status,
    };
});

const viewBookingDetails = (booking: BookingRow) => {
    viewingBooking.value = booking;
    viewDetailsOpen.value = true;
};

const handleEditFromView = () => {
    editingBooking.value = viewingBooking.value;
    viewDetailsOpen.value = false;
};

const columns = createColumns(
    (booking) => (editingBooking.value = booking),
    (booking) => (deletingBooking.value = booking),
    (booking) => viewBookingDetails(booking),
);

const { table, runSearch } = useServerTable<BookingRow>({
    data: bookingsData,
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
            booking_status: statusFilter.value,
            package_id: packageFilter.value,
            tour_date_id: tourDateFilter.value,
            from_date: fromDateFilter.value,
            to_date: toDateFilter.value,
        },
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
            booking_status: value === 'all' ? undefined : String(value),
            package_id: packageFilter.value,
            tour_date_id: tourDateFilter.value,
            from_date: fromDateFilter.value,
            to_date: toDateFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onPackageFilterChange = (value: AcceptableValue) => {
    if (value === null || value === undefined) return;

    router.get(
        index().url,
        {
            sort: sort.value,
            search: search.value,
            per_page: perPage.value,
            booking_status: statusFilter.value,
            package_id: value === 'all' ? undefined : String(value),
            tour_date_id: tourDateFilter.value,
            from_date: fromDateFilter.value,
            to_date: toDateFilter.value,
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
            booking_status: statusFilter.value,
            package_id: packageFilter.value,
            tour_date_id: value === 'all' ? undefined : String(value),
            from_date: fromDateFilter.value,
            to_date: toDateFilter.value,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const onDateFilterChange = (key: string, value: string) => {
    const params: Record<string, any> = {
        sort: sort.value,
        search: search.value,
        per_page: perPage.value,
        booking_status: statusFilter.value,
        package_id: packageFilter.value,
        tour_date_id: tourDateFilter.value,
        from_date: fromDateFilter.value,
        to_date: toDateFilter.value,
        [key]: value || undefined,
    };

    Object.keys(params).forEach(k => {
        if (params[k] === undefined || params[k] === '') {
            delete params[k];
        }
    });

    router.get(index().url, params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>

    <Head title="Bookings" />
    <div class="px-4 py-6">
        <div class="flex items-center justify-between">
            <Heading title="Bookings" description="Manage all tour bookings" />
            <Button as-child>
                <Link :href="create()">
                    <Plus class="mr-2 h-4 w-4" />
                    New Booking
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
                    <div class="relative w-full sm:w-36">
                        <Select :model-value="statusFilter ?? 'all'" @update:model-value="onStatusFilterChange">
                            <SelectTrigger>
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="confirmed">Confirmed</SelectItem>
                                <SelectItem value="cancelled">Cancelled</SelectItem>
                                <SelectItem value="completed">Completed</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="relative w-full sm:w-48">
                        <Select :model-value="packageFilter ?? 'all'" @update:model-value="onPackageFilterChange">
                            <SelectTrigger>
                                <SelectValue placeholder="All Packages" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Packages</SelectItem>
                                <SelectItem v-for="pkg in packages" :key="pkg.id" :value="String(pkg.id)">
                                    {{ pkg.package_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

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

                    <div class="flex items-center gap-2">
                        <CalendarRange class="h-4 w-4 text-muted-foreground" />
                        <Input type="date" :model-value="fromDateFilter" class="w-36"
                            @update:model-value="(val) => onDateFilterChange('from_date', val as string)"
                            placeholder="From" />
                        <span class="text-muted-foreground">to</span>
                        <Input type="date" :model-value="toDateFilter" class="w-36"
                            @update:model-value="(val) => onDateFilterChange('to_date', val as string)"
                            placeholder="To" />
                    </div>

                    <div class="relative w-full sm:max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="searchInput" placeholder="Search bookings..." class="pl-9"
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
                        <tr v-if="bookingsData.length === 0">
                            <td :colspan="columns.length" class="px-4 py-6 text-center text-muted-foreground">
                                No bookings found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="flex flex-row flex-nowrap items-center justify-between gap-4 overflow-x-auto border-t px-4 py-3">
                <p class="shrink-0 whitespace-nowrap text-sm text-muted-foreground">
                    Showing {{ props.bookings.from ?? 0 }} to {{ props.bookings.to ?? 0 }} of {{ props.bookings.total }}
                    bookings
                </p>

                <div v-if="props.bookings.links.length > 3" class="flex flex-nowrap gap-1">
                    <Link v-for="(link, i) in props.bookings.links" :key="i" :href="link.url ?? '#'" :class="[
                        'whitespace-nowrap rounded px-3 py-1 text-sm',
                        link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                        !link.url && 'pointer-events-none opacity-50',
                    ]" v-html="link.label" />
                </div>
            </div>
        </div>

        <!-- View Details Dialog -->
        <FormDialog :open="viewDetailsOpen" title="Booking Details"
            content-class="sm:max-w-6xl max-h-[90vh] overflow-y-auto"
            :description="viewingBooking ? `Booking #${viewingBooking.id}` : undefined"
            @update:open="(v) => !v && (viewDetailsOpen = false)">
            <template #default="{ close }">
                <BookingView v-if="viewingBooking" :booking="viewingBooking" @close="close"
                    @edit="handleEditFromView" />
            </template>
        </FormDialog>

        <!-- Edit Booking Dialog -->
        <FormDialog :open="!!editingBooking" title="Edit Booking" content-class="sm:max-w-3xl"
            :description="editingBooking ? `Update booking for ${editingBooking.user?.name}` : undefined"
            @update:open="(v) => !v && (editingBooking = null)">
            <template #default="{ close }">
                <BookingForm v-if="editingBookingFormValues" :booking="editingBookingFormValues" :tour-dates="tourDates"
                    :pickup-locations="pickupLocations" :users="users" :submit-action="update(editingBooking!.id)"
                    submit-label="Save Changes" :on-cancel="close" @success="editingBooking = null" />
            </template>
        </FormDialog>

        <!-- Edit Booking Dialog -->
        <FormDialog :open="!!editingBooking" title="Edit Booking" content-class="sm:max-w-6xl"
            :description="editingBooking ? `Update booking for ${editingBooking.user?.name}` : undefined"
            @update:open="(v) => !v && (editingBooking = null)">
            <template #default="{ close }">
                <BookingForm v-if="editingBookingFormValues" :booking="editingBookingFormValues" :tour-dates="tourDates"
                    :pickup-locations="pickupLocations" :users="users" :submit-action="update(editingBooking!.id)"
                    submit-label="Save Changes" :on-cancel="close" @success="editingBooking = null" />
            </template>
        </FormDialog>

        <DeleteDialog :open="!!deletingBooking" :action="deleteAction" :description="deletingBooking
            ? `This will permanently delete the booking for ${deletingBooking.user?.name}. This action cannot be undone.`
            : ''
            " @update:open="(v) => !v && (deletingBooking = null)" />

        <BulkDeleteDialog :open="bulkDeleteOpen" :count="selectedCount" :ids="selectedIds" item-label="booking"
            action="/admin/bookings-bulk-destroy" @update:open="bulkDeleteOpen = $event"
            @deleted="handleBulkDeleteSuccess" />
    </div>
</template>