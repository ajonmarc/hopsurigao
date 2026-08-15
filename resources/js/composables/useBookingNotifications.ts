import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export type BookingNotification = {
    id: number;
    guest_name: string;
    package_name: string;
    number_of_guests: number;
    booking_status: string;
    created_at: string;
};

const LAST_SEEN_KEY = 'admin_booking_notifications_last_seen';
const SOUND_PREF_KEY = 'admin_booking_notifications_sound';
const NOTIFICATIONS_KEY = 'admin_booking_notifications_list';
// Tracks which notification IDs have been read/dismissed, so unread count
// is always derived from real per-item state instead of a loose counter.
const READ_IDS_KEY = 'admin_booking_notifications_read_ids';
const POLL_INTERVAL_MS = 15000;

function loadStoredNotifications(): BookingNotification[] {
    try {
        const raw = localStorage.getItem(NOTIFICATIONS_KEY);
        return raw ? JSON.parse(raw) : [];
    } catch {
        return [];
    }
}

function loadReadIds(): Set<number> {
    try {
        const raw = localStorage.getItem(READ_IDS_KEY);
        return raw ? new Set(JSON.parse(raw)) : new Set();
    } catch {
        return new Set();
    }
}

export function useBookingNotifications() {
    // Initialize from localStorage instead of empty, so a refresh doesn't
    // wipe out notifications the admin hasn't dealt with yet.
    const notifications = ref<BookingNotification[]>(loadStoredNotifications());
    const readIds = ref<Set<number>>(loadReadIds());
    const soundEnabled = ref(localStorage.getItem(SOUND_PREF_KEY) !== 'off');

    // Derived, not stored directly — always accurate against the actual
    // list + read state, so dismissing/clearing can never get out of sync
    // with a separately-tracked counter.
    const unreadCount = computed(
        () => notifications.value.filter((n) => !readIds.value.has(n.id)).length
    );

    let lastSeenAt: string | null = localStorage.getItem(LAST_SEEN_KEY);
    let pollTimer: ReturnType<typeof setInterval> | null = null;

    watch(notifications, (val) => {
        localStorage.setItem(NOTIFICATIONS_KEY, JSON.stringify(val));
    }, { deep: true });

    watch(readIds, (val) => {
        localStorage.setItem(READ_IDS_KEY, JSON.stringify(Array.from(val)));
    }, { deep: true });

    // Custom notification sound — place the file in `public/sounds/`.
    const NOTIFICATION_SOUND_URL = '/sounds/notification.mp3';
    let notificationAudio: HTMLAudioElement | null = null;

    const getNotificationAudio = (): HTMLAudioElement => {
        if (!notificationAudio) {
            notificationAudio = new Audio(NOTIFICATION_SOUND_URL);
            notificationAudio.volume = 0.6; // adjust to taste, 0.0–1.0
        }
        return notificationAudio;
    };

    const playChime = () => {
        if (!soundEnabled.value) return;

        try {
            const audio = getNotificationAudio();
            audio.currentTime = 0;
            audio.play().catch((err) => {
                // Most common cause: browser autoplay policy blocking
                // playback because there hasn't been a user gesture yet
                // on this page load — needs one click anywhere on the
                // actual page (not the DevTools console).
                console.error('[booking-notifications] chime failed', err);
            });
        } catch (err) {
            console.error('[booking-notifications] chime failed', err);
        }
    };

    const fetchNotifications = async (isFirstLoad = false) => {
        try {
            const { data } = await axios.get('/admin/notifications/bookings', {
                params: lastSeenAt ? { since: lastSeenAt } : undefined,
            });

            const incoming: (BookingNotification & { is_new?: boolean })[] = data.bookings ?? [];

            if (incoming.length > 0) {
                // Backend returns both brand-new bookings AND bookings
                // whose status changed since last poll (tagged is_new).
                // Update existing entries in place so status stays
                // current; only genuinely new ones get a chime.
                const freshlyCreated = incoming.filter(
                    (b) => b.is_new && !notifications.value.some((n) => n.id === b.id)
                );
                const merged = notifications.value.map(
                    (n) => incoming.find((b) => b.id === n.id) ?? n
                );

                notifications.value = [...freshlyCreated, ...merged].slice(0, 30);

                if (!isFirstLoad && freshlyCreated.length > 0) {
                    playChime();
                }
            }

            lastSeenAt = data.server_time;
            if (lastSeenAt) {
                localStorage.setItem(LAST_SEEN_KEY, lastSeenAt);
            }
        } catch (err) {
            console.error('[booking-notifications] poll failed', err);
        }
    };

    // Marks every currently-listed notification as read, but keeps them
    // visible in the list (just no longer counted as unread).
    const markAllRead = () => {
        const next = new Set(readIds.value);
        notifications.value.forEach((n) => next.add(n.id));
        readIds.value = next;
    };

    // Dismiss a single notification — removes it from the list entirely
    // and cleans up its read-state entry.
    const dismissNotification = (id: number) => {
        notifications.value = notifications.value.filter((n) => n.id !== id);
        if (readIds.value.has(id)) {
            const next = new Set(readIds.value);
            next.delete(id);
            readIds.value = next;
        }
    };

    // Clear every notification at once.
    const clearAll = () => {
        notifications.value = [];
        readIds.value = new Set();
    };

    const toggleSound = () => {
        soundEnabled.value = !soundEnabled.value;
        localStorage.setItem(SOUND_PREF_KEY, soundEnabled.value ? 'on' : 'off');
    };

    onMounted(() => {
        fetchNotifications(true);
        pollTimer = setInterval(() => fetchNotifications(false), POLL_INTERVAL_MS);
    });

    onUnmounted(() => {
        if (pollTimer) clearInterval(pollTimer);
        if (notificationAudio) {
            notificationAudio.pause();
            notificationAudio = null;
        }
    });

    return {
        notifications,
        unreadCount,
        soundEnabled,
        markAllRead,
        dismissNotification,
        clearAll,
        toggleSound,
    };
}