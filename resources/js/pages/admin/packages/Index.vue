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
import { createColumns, type PackageRow } from './columns';
import PackageForm from './Form.vue';
import { index, create, update } from '@/routes/admin/packages';
import type { AcceptableValue } from 'reka-ui';

const props = defineProps<{
    packages: {
        data: PackageRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    filters?: { sort?: string; search?: string; per_page?: number | string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Packages', href: index() }],
    },
});

const packagesData = computed(() => props.packages.data);
const sort = computed(() => props.filters?.sort);
const search = computed(() => props.filters?.search);
const searchInput = ref(props.filters?.search ?? '');
const perPage = computed(() => String(props.filters?.per_page ?? 10));

const editingPackage = ref<PackageRow | null>(null);
const deletingPackage = ref<PackageRow | null>(null);
const bulkDeleteOpen = ref(false);

const deleteAction = computed(() => {
    if (!deletingPackage.value) return null;
    return {
        url: `/admin/packages/${deletingPackage.value.id}`,
        method: 'delete' as const,
    };
});

const editingPackageFormValues = computed(() => {
    if (!editingPackage.value) return undefined;
    return { ...editingPackage.value };
});

const columns = createColumns(
    (pkg) => (editingPackage.value = pkg),
    (pkg) => (deletingPackage.value = pkg),
);

const { table, runSearch } = useServerTable<PackageRow>({
    data: packagesData,
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
        { sort: sort.value, search: search.value, per_page: value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};
</script>

<template>

    <Head title="Packages" />
    <div class="px-4 py-6">
        <div class="flex items-center justify-between">
            <Heading title="Packages" description="Manage your tour packages" />
            <Button as-child>
                <Link :href="create()">
                    <Plus class="mr-2 h-4 w-4" />
                    New Package
                </Link>
            </Button>
        </div>

        <div class="mt-6 rounded-lg border">
            <div class="flex flex-col sm:flex-row flex-wrap items-start sm:items-center justify-between gap-4 border-b px-4 py-3">
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

                <div class="flex flex-col gap-3 w-full sm:w-auto sm:flex-row sm:items-center sm:gap-4">
                    <div class="relative order-1 w-full sm:order-2 sm:max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="searchInput" placeholder="Search by name or destination..." class="pl-9"
                            @input="onSearchInput" />
                    </div>

                    <div v-if="selectedCount > 0"
                        class="order-2 flex items-center justify-between gap-3 sm:order-1 sm:justify-start">
                        <span class="text-sm text-muted-foreground whitespace-nowrap">
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
                        <tr v-if="packagesData.length === 0">
                            <td :colspan="columns.length" class="px-4 py-6 text-center text-muted-foreground">
                                No packages found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-row flex-nowrap items-center justify-between gap-4 overflow-x-auto border-t px-4 py-3">
                <p class="shrink-0 text-sm whitespace-nowrap text-muted-foreground">
                    Showing {{ props.packages.from ?? 0 }} to {{ props.packages.to ?? 0 }} of {{ props.packages.total }} packages
                </p>

                <div v-if="props.packages.links.length > 3" class="flex flex-nowrap gap-1">
                    <Link v-for="(link, i) in props.packages.links" :key="i" :href="link.url ?? '#'" :class="[
                        'whitespace-nowrap rounded px-3 py-1 text-sm',
                        link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                        !link.url && 'pointer-events-none opacity-50',
                    ]" v-html="link.label" />
                </div>
            </div>
        </div>

        <FormDialog :open="!!editingPackage" title="Edit Package" content-class="sm:max-w-2xl"
            :description="editingPackage ? `Update ${editingPackage.package_name}'s details.` : undefined"
            @update:open="(v) => !v && (editingPackage = null)">
            <template #default="{ close }">
                <PackageForm v-if="editingPackageFormValues" :package="editingPackageFormValues"
                    :submit-action="update(editingPackage!.id)" submit-label="Save Changes" :on-cancel="close"
                    @success="editingPackage = null" />
            </template>
        </FormDialog>

        <DeleteDialog :open="!!deletingPackage" :action="deleteAction" :description="deletingPackage
            ? `This will permanently delete ${deletingPackage.package_name}. This action cannot be undone.`
            : ''
            " @update:open="(v) => !v && (deletingPackage = null)" />

        <BulkDeleteDialog :open="bulkDeleteOpen" :count="selectedCount" :ids="selectedIds" item-label="package"
            action="/admin/packages-bulk-destroy" @update:open="bulkDeleteOpen = $event"
            @deleted="handleBulkDeleteSuccess" />
    </div>
</template>