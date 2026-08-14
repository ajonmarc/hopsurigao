import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from '@lucide/vue';
import type { AppTableFeatures } from '@/lib/tableFeatures';

export type PickupLocationRow = {
    id: number;
    name: string;
    address: string | null;
    description: string | null;
    status: 'active' | 'inactive';
    created_at: string;
};

export function createColumns(
    onEdit: (location: PickupLocationRow) => void,
    onDelete: (location: PickupLocationRow) => void,
): ColumnDef<AppTableFeatures, PickupLocationRow>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Name',
            enableSorting: true,
        },
        {
            accessorKey: 'address',
            header: 'Address',
            enableSorting: false,
            cell: ({ row }) => row.original.address || '—',
        },
        {
            accessorKey: 'description',
            header: 'Description',
            enableSorting: false,
            cell: ({ row }) => {
                const desc = row.original.description;
                if (!desc) return '—';
                return desc.length > 100 ? `${desc.substring(0, 100)}...` : desc;
            },
        },
        {
            accessorKey: 'status',
            header: 'Status',
            enableSorting: true,
            cell: ({ row }) =>
                h(
                    'span',
                    {
                        class: [
                            'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                            row.original.status === 'active'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-neutral-100 text-neutral-600',
                        ],
                    },
                    row.original.status.charAt(0).toUpperCase() + row.original.status.slice(1),
                ),
        },
        {
            accessorKey: 'created_at',
            header: 'Created',
            enableSorting: true,
            cell: ({ row }) => new Date(row.original.created_at).toLocaleDateString(),
        },
        {
            id: 'actions',
            header: 'Actions',
            enableSorting: false,
            cell: ({ row }) =>
                h('div', { class: 'flex justify-start gap-2' }, [
                    h(
                        Button,
                        {
                            variant: 'default',
                            size: 'sm',
                            class: 'h-8 w-8 p-0 bg-blue-600 hover:bg-blue-700',
                            onClick: () => onEdit(row.original),
                        },
                        () => h(Pencil, { class: 'h-4 w-4 text-white' }),
                    ),
                    h(
                        Button,
                        {
                            variant: 'destructive',
                            size: 'sm',
                            class: 'h-8 w-8 p-0',
                            onClick: () => onDelete(row.original),
                        },
                        () => h(Trash2, { class: 'h-4 w-4' }),
                    ),
                ]),
        },
    ];
}