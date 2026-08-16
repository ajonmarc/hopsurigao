import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from '@lucide/vue';
import type { AppTableFeatures } from '@/lib/tableFeatures';

export type PickupScheduleRow = {
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
    pickup_location_id: number;
    pickup_location: {
        id: number;
        name: string;
        address: string | null;
    };
    pickup_time: string; // "HH:mm" or "HH:mm:ss"
    created_at: string;
    updated_at: string;
};

const formatTime = (time: string) => {
    if (!time) return 'N/A';
    const [hours, minutes] = time.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

export function createColumns(
    onEdit: (schedule: PickupScheduleRow) => void,
    onDelete: (schedule: PickupScheduleRow) => void,
): ColumnDef<AppTableFeatures, PickupScheduleRow>[] {
    return [
        {
            accessorKey: 'tour_date.package.package_name',
            header: 'Package',
            enableSorting: false,
            cell: ({ row }) => row.original.tour_date?.package?.package_name ?? 'N/A',
        },
        {
            accessorKey: 'tour_date.tour_date',
            header: 'Tour Date',
            enableSorting: false,
            cell: ({ row }) =>
                row.original.tour_date?.tour_date ? formatDate(row.original.tour_date.tour_date) : 'N/A',
        },
        {
            accessorKey: 'pickup_location.name',
            header: 'Pickup Location',
            enableSorting: false,
            cell: ({ row }) => row.original.pickup_location?.name ?? 'N/A',
        },
        {
            accessorKey: 'pickup_time',
            header: 'Pickup Time',
            enableSorting: true,
            cell: ({ row }) => formatTime(row.original.pickup_time),
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