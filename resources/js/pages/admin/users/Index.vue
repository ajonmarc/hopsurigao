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
import { createColumns, type UserRow } from './columns';
import UserForm from './Form.vue';
import { index, create, update, destroy } from '@/routes/admin/users';
import type { AcceptableValue } from 'reka-ui';

const props = defineProps<{
    users: {
        data: UserRow[];
        links: { url: string | null; label: string; active: boolean }[];
        from: number | null;
        to: number | null;
        total: number;
    };
    roles: { id: number; name: string }[];
    filters?: { sort?: string; search?: string; per_page?: number | string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Users', href: index() }],
    },
});

const usersData = computed(() => props.users.data);
const sort = computed(() => props.filters?.sort);
const search = computed(() => props.filters?.search);
const searchInput = ref(props.filters?.search ?? '');
const perPage = computed(() => String(props.filters?.per_page ?? 10));

// --- Edit / Delete dialog state --------------------------------------
const editingUser = ref<UserRow | null>(null);
const deletingUser = ref<UserRow | null>(null);
const bulkDeleteOpen = ref(false);

const deleteAction = computed(() => {
    if (!deletingUser.value) return null;
    return {
        url: `/admin/users/${deletingUser.value.id}`,
        method: 'delete' as const,
    };
});

const editingUserFormValues = computed(() => {
    if (!editingUser.value) return undefined;

    return {
        id: editingUser.value.id,
        name: editingUser.value.name,
        email: editingUser.value.email,
        role_id: editingUser.value.role?.id ?? '',
    };
});

const columns = createColumns(
    (user) => (editingUser.value = user),
    (user) => (deletingUser.value = user),
);

// --- Table setup -------------------------------------------------------
const { table, runSearch } = useServerTable<UserRow>({
    data: usersData,
    columns,
    baseUrl: index().url,
    sort,
    search,
});

// --- Selection handling ----------------------------------------------
const selectedRows = computed(() => table.getSelectedRowModel().rows);
const selectedCount = computed(() => selectedRows.value.length);
const selectedIds = computed(() => selectedRows.value.map((row) => row.original.id));

const handleBulkDeleteSuccess = () => {
    table.resetRowSelection();
    router.reload();
};

// --- Search / pagination handling -------------------------------------
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

    <Head title="Users" />
    <div class="px-4 py-6">
        <div class="flex items-center justify-between">
            <Heading title="admin Users" description="Manage your admin users" />
            <Button as-child>
                <Link :href="create()">
                    <Plus class="mr-2 h-4 w-4" />
                    New User
                </Link>
            </Button>
        </div>

        <!-- Single unified panel -->
        <div class="mt-6 rounded-lg border">
            <!-- Toolbar -->
            <div
                class="flex flex-col sm:flex-row flex-wrap items-start sm:items-center justify-between gap-4 border-b px-4 py-3">
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
                        <Input v-model="searchInput" placeholder="Search by name or email..." class="pl-9"
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

            <!-- Table -->
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
                        <tr v-if="usersData.length === 0">
                            <td :colspan="columns.length" class="px-4 py-6 text-center text-muted-foreground">
                                No users found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div
                class="flex flex-row flex-nowrap items-center justify-between gap-4 overflow-x-auto border-t px-4 py-3">
                <p class="shrink-0 text-sm whitespace-nowrap text-muted-foreground">
                    Showing {{ props.users.from ?? 0 }} to {{ props.users.to ?? 0 }} of {{ props.users.total }} users
                </p>

                <div v-if="props.users.links.length > 3" class="flex flex-nowrap gap-1">
                    <Link v-for="(link, i) in props.users.links" :key="i" :href="link.url ?? '#'" :class="[
                        'whitespace-nowrap rounded px-3 py-1 text-sm',
                        link.active ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                        !link.url && 'pointer-events-none opacity-50',
                    ]" v-html="link.label" />
                </div>
            </div>
        </div>

        <!-- Edit Dialog -->
        <FormDialog :open="!!editingUser" title="Edit User" content-class="sm:max-w-6xl"
            :description="editingUser ? `Update ${editingUser.name}'s account details.` : undefined"
            @update:open="(v) => !v && (editingUser = null)">
            <template #default="{ close }">
                <UserForm v-if="editingUserFormValues" :user="editingUserFormValues" :roles="roles"
                    :submit-action="update(editingUser!.id)" submit-label="Save Changes" :on-cancel="close"
                    @success="editingUser = null" />
            </template>
        </FormDialog>

        <!-- Single Delete Dialog -->
        <DeleteDialog :open="!!deletingUser" :action="deleteAction" :description="deletingUser
            ? `This will permanently delete ${deletingUser.name}. This action cannot be undone.`
            : ''
            " @update:open="(v) => !v && (deletingUser = null)" />

        <!-- Bulk Delete Dialog -->
        <BulkDeleteDialog :open="bulkDeleteOpen" :count="selectedCount" :ids="selectedIds" item-label="user"
            action="/admin/users-bulk-destroy" @update:open="bulkDeleteOpen = $event"
            @deleted="handleBulkDeleteSuccess" />
    </div>
</template>