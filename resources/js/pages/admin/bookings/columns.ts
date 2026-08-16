import { h } from 'vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2, Eye, CheckCircle2, RefreshCcw } from '@lucide/vue';
import type { AppTableFeatures } from '@/lib/tableFeatures';

export type BookingRow = {
    id: number;
    user_id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    tour_date_id: number;
    tour_date: {
        id: number;
        tour_date: string;
        package: {
            id: number;
            package_name: string;
            price: number;
            description: string;
            image: string | null;
            destination: string;
            status: string;
        };
    };
    // CHANGED: pickup now comes through pickup_schedule, not a direct pickup_location
    pickup_schedule_id: number;
    pickup_schedule: {
        id: number;
        tour_date_id: number;
        pickup_location_id: number;
        pickup_time: string;
        pickup_location: {
            id: number;
            name: string;
            address: string | null;
        };
    };
    number_of_guests: number;
    phone_number: string;
    nationality: string;
    special_request: string | null;
    booking_status: 'pending' | 'confirmed' | 'cancelled' | 'completed';
    created_at: string;
    updated_at: string;
    payments?: Array<{
        id: number;
        amount: number;
        payment_status: 'pending' | 'paid' | 'failed' | 'refunded';
        payment_method: string;
    }>;
};

const statusColors = {
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
    completed: 'bg-green-100 text-green-700',
};

const paymentStatusColors = {
    pending: 'bg-yellow-100 text-yellow-700',
    paid: 'bg-green-100 text-green-700',
    failed: 'bg-red-100 text-red-700',
    refunded: 'bg-gray-100 text-gray-700',
};

// Helper: most recent payment for a booking, if any
function latestPayment(booking: BookingRow) {
    if (!booking.payments || booking.payments.length === 0) return null;
    return booking.payments[booking.payments.length - 1];
}

export function createColumns(
    onEdit: (booking: BookingRow) => void,
    onDelete: (booking: BookingRow) => void,
    onView: (booking: BookingRow) => void,
    onConfirmPayment: (booking: BookingRow) => void,
    onChangeStatus: (booking: BookingRow) => void, // NEW
): ColumnDef<AppTableFeatures, BookingRow>[] {
    return [
        {
            accessorKey: 'user.name',
            header: 'Guest',
            enableSorting: false,
            cell: ({ row }) => {
                const user = row.original.user;
                return user ? `${user.name}\n${user.email}` : 'N/A';
            },
        },
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
            accessorKey: 'number_of_guests',
            header: 'Guests',
            enableSorting: true,
            cell: ({ row }) => `${row.original.number_of_guests} pax`,
        },
        {
            accessorKey: 'booking_status',
            header: 'Status',
            enableSorting: true,
            cell: ({ row }) =>
                h(
                    'span',
                    {
                        class: [
                            'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                            statusColors[row.original.booking_status] || 'bg-neutral-100 text-neutral-600',
                        ],
                    },
                    row.original.booking_status.charAt(0).toUpperCase() + row.original.booking_status.slice(1),
                ),
        },
        // NEW: Payment status column
        {
            id: 'payment_status',
            header: 'Payment',
            enableSorting: false,
            cell: ({ row }) => {
                const payment = latestPayment(row.original);

                if (!payment) {
                    return h(
                        'span',
                        { class: 'inline-block rounded-full bg-neutral-100 px-2 py-0.5 text-xs font-medium text-neutral-600' },
                        'No Payment',
                    );
                }

                return h(
                    'span',
                    {
                        class: [
                            'inline-block rounded-full px-2 py-0.5 text-xs font-medium',
                            paymentStatusColors[payment.payment_status] || 'bg-neutral-100 text-neutral-600',
                        ],
                    },
                    payment.payment_status.charAt(0).toUpperCase() + payment.payment_status.slice(1),
                );
            },
        },
        {
            accessorKey: 'created_at',
            header: 'Booked On',
            enableSorting: true,
            cell: ({ row }) => new Date(row.original.created_at).toLocaleDateString(),
        },
        {
            id: 'actions',
            header: 'Actions',
            enableSorting: false,
            cell: ({ row }) => {
                const payment = latestPayment(row.original);
                const canConfirmPayment = !!payment && payment.payment_status !== 'paid';

                const buttons = [
                    h(
                        Button,
                        {
                            variant: 'outline',
                            size: 'sm',
                            class: 'h-8 w-8 p-0',
                            onClick: () => onView(row.original),
                        },
                        () => h(Eye, { class: 'h-4 w-4' }),
                    ),
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
                            variant: 'default',
                            size: 'sm',
                            class: 'h-8 w-8 p-0 bg-amber-500 hover:bg-amber-600',
                            title: 'Change Status',
                            onClick: () => onChangeStatus(row.original),
                        },
                        () => h(RefreshCcw, { class: 'h-4 w-4 text-white' }),
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
                ];

                // NEW: only show the confirm-payment button when there's an unpaid payment
                if (canConfirmPayment) {
                    buttons.push(
                        h(
                            Button,
                            {
                                variant: 'default',
                                size: 'sm',
                                class: 'h-8 w-8 p-0 bg-green-600 hover:bg-green-700',
                                title: 'Confirm Payment',
                                onClick: () => onConfirmPayment(row.original),
                            },
                            () => h(CheckCircle2, { class: 'h-4 w-4 text-white' }),
                        ),
                    );
                }

                return h('div', { class: 'flex justify-start gap-2' }, buttons);
            },
        },
    ];
}