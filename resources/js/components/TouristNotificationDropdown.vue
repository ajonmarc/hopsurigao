<script setup lang="ts">
import { Bell, Volume2, VolumeX, X } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { useTouristBookingNotifications } from '@/composables/useTouristBookingNotifications';

const {
    notifications,
    unreadCount,
    soundEnabled,
    markAllRead,
    dismissNotification,
    clearAll,
    toggleSound,
} = useTouristBookingNotifications();

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
    completed: 'bg-green-100 text-green-700',
};

const statusMessages: Record<string, string> = {
    pending: 'Booking placed, awaiting confirmation',
    confirmed: 'Your booking is confirmed!',
    cancelled: 'This booking was cancelled',
    completed: 'Trip completed',
};
</script>

<template>
    <DropdownMenu @update:open="(open) => open && markAllRead()">
        <DropdownMenuTrigger :as-child="true">
            <Button variant="ghost" size="icon" class="relative size-9 w-9 cursor-pointer">
                <Bell class="size-5 opacity-80" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-semibold text-white"
                >
                    {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-80 p-0">
            <div class="flex items-center justify-between border-b px-3 py-2">
                <span class="text-sm font-semibold">My Booking Updates</span>
                <div class="flex items-center gap-1">
                    <Button
                        v-if="notifications.length > 0"
                        variant="ghost"
                        size="sm"
                        class="h-7 px-2 text-xs text-muted-foreground"
                        @click.stop="clearAll"
                    >
                        Clear all
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7"
                        :title="soundEnabled ? 'Mute sound' : 'Unmute sound'"
                        @click.stop="toggleSound"
                    >
                        <Volume2 v-if="soundEnabled" class="h-4 w-4" />
                        <VolumeX v-else class="h-4 w-4 text-muted-foreground" />
                    </Button>
                </div>
            </div>

            <div class="max-h-96 overflow-y-auto">
                <div
                    v-for="item in notifications"
                    :key="item.id"
                    class="group relative block border-b px-3 py-2.5 last:border-b-0 hover:bg-muted/50"
                >
                    <Link :href="`/tourist/bookings/${item.id}`" class="block pr-6">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ item.package_name }}</p>
                                <p class="truncate text-xs text-muted-foreground">
                                    {{ statusMessages[item.booking_status] || 'Status updated' }}
                                    &middot; {{ item.number_of_guests }} pax
                                </p>
                            </div>
                            <span
                                class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                :class="statusColors[item.booking_status] || 'bg-neutral-100 text-neutral-600'"
                            >
                                {{ item.booking_status }}
                            </span>
                        </div>
                        <p class="mt-1 text-[11px] text-muted-foreground">{{ formatTime(item.created_at) }}</p>
                    </Link>

                    <button
                        type="button"
                        title="Mark as read"
                        class="absolute right-2 top-2.5 rounded-full p-1 text-muted-foreground opacity-0 transition-opacity hover:bg-muted hover:text-foreground group-hover:opacity-100"
                        @click.stop.prevent="dismissNotification(item.id)"
                    >
                        <X class="h-3.5 w-3.5" />
                    </button>
                </div>

                <div v-if="notifications.length === 0" class="px-3 py-8 text-center text-sm text-muted-foreground">
                    No booking updates yet.
                </div>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>