import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from '@lucide/vue';
import type { AppTableFeatures } from '@/lib/tableFeatures';

export type TimeRow = {
    id: number;
    tour_date_id: number;
    tour_date: {
        id: number;
        tour_date: string;
        package: {
            id: number;
            package_name: string;
        };
    };
    time: string;
    description: string;
    created_at: string;
};

export function createColumns(
    onEdit: (time: TimeRow) => void,
    onDelete: (time: TimeRow) => void,
): ColumnDef<AppTableFeatures, TimeRow>[] {
    return [
        {
            accessorKey: 'tour_date.package.package_name',
            header: 'Package',
            enableSorting: true,
            cell: ({ row }) => row.original.tour_date?.package?.package_name ?? 'N/A',
        },
        {
            accessorKey: 'tour_date.tour_date',
            header: 'Tour Date',
            enableSorting: true,
            cell: ({ row }) => {
                const date = row.original.tour_date?.tour_date;
                return date ? new Date(date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                }) : 'N/A';
            },
        },
        {
            accessorKey: 'time',
            header: 'Time',
            enableSorting: true,
            cell: ({ row }) => {
                const time = row.original.time;
                if (!time) return 'N/A';
                const [hours, minutes] = time.split(':');
                const date = new Date();
                date.setHours(parseInt(hours), parseInt(minutes));
                return date.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true,
                });
            },
        },
        {
            accessorKey: 'description',
            header: 'Description',
            enableSorting: true,
            cell: ({ row }) => {
                const desc = row.original.description;
                return desc.length > 100 ? `${desc.substring(0, 100)}...` : desc;
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