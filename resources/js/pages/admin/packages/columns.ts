import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from '@lucide/vue';
import type { AppTableFeatures } from '@/lib/tableFeatures';

export type PackageRow = {
    id: number;
    package_name: string;
    destination: string;
    description: string | null;
    image: string | null;
    price: number;
    status: string;
};

export function createColumns(
    onEdit: (pkg: PackageRow) => void,
    onDelete: (pkg: PackageRow) => void,
): ColumnDef<AppTableFeatures, PackageRow>[] {
    return [
        {
            accessorKey: 'package_name',
            header: 'Package',
            enableSorting: true,
        },
        {
            accessorKey: 'destination',
            header: 'Destination',
            enableSorting: true,
        },
        {
            accessorKey: 'price',
            header: 'Price',
            enableSorting: true,
            cell: ({ row }) => `₱${Number(row.original.price).toLocaleString()}`,
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
                    row.original.status,
                ),
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