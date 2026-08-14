import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from '@lucide/vue';
import type { AppTableFeatures } from '@/lib/tableFeatures';

export type ReminderRow = {
    id: number;
    package_id: number;
    package: {
        id: number;
        package_name: string;
    };
    description: string;
    created_at: string;
};

export function createColumns(
    onEdit: (reminder: ReminderRow) => void,
    onDelete: (reminder: ReminderRow) => void,
): ColumnDef<AppTableFeatures, ReminderRow>[] {
    return [
        {
            accessorKey: 'package.package_name',
            header: 'Package',
            enableSorting: true,
            cell: ({ row }) => row.original.package?.package_name ?? 'N/A',
        },
        {
            accessorKey: 'description',
            header: 'Reminder',
            enableSorting: true,
            cell: ({ row }) => {
                const desc = row.original.description;
                return desc.length > 150 ? `${desc.substring(0, 150)}...` : desc;
            },
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