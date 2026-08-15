import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export type TouristBookingNotification = {
    id: number;
    package_name: string;
    number_of_guests: number;
    booking_status: string;
    created_at: string;
};

// Separate keys from the admin composable so the two never collide,
// e.g. when an admin account and a tourist account share a browser.
const LAST_SEEN_KEY = 'tourist_booking_notifications_last_seen';
const SOUND_PREF_KEY = 'tourist_booking_notifications_sound';
const NOTIFICATIONS_KEY = 'tourist_booking_notifications_list';
const READ_IDS_KEY = 'tourist_booking_notifications_read_ids';
const POLL_INTERVAL_MS = 15000;

function loadStoredNotifications(): TouristBookingNotification[] {
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

export function useTouristBookingNotifications() {
    const notifications = ref<TouristBookingNotification[]>(loadStoredNotifications());
    const readIds = ref<Set<number>>(loadReadIds());
    const soundEnabled = ref(localStorage.getItem(SOUND_PREF_KEY) !== 'off');

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

    // Custom notification sound — reuse the same file as admin, or swap
    // to a different one if you want tourists to hear something distinct.
    const NOTIFICATION_SOUND_URL = '/sounds/notification.mp3';
    let notificationAudio: HTMLAudioElement | null = null;

    const getNotificationAudio = (): HTMLAudioElement => {
        if (!notificationAudio) {
            notificationAudio = new Audio(NOTIFICATION_SOUND_URL);
            notificationAudio.volume = 0.6;
        }
        return notificationAudio;
    };

    const playChime = () => {
        if (!soundEnabled.value) return;

        try {
            const audio = getNotificationAudio();
            audio.currentTime = 0;
            audio.play().catch((err) => {
                console.error('[tourist-notifications] chime failed', err);
            });
        } catch (err) {
            console.error('[tourist-notifications] chime failed', err);
        }
    };

    const fetchNotifications = async (isFirstLoad = false) => {
        try {
            const { data } = await axios.get('/tourist/notifications/bookings', {
                params: lastSeenAt ? { since: lastSeenAt } : undefined,
            });

            const incoming: (TouristBookingNotification & { is_new?: boolean })[] = data.bookings ?? [];

            if (incoming.length > 0) {
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
            console.error('[tourist-notifications] poll failed', err);
        }
    };

    const markAllRead = () => {
        const next = new Set(readIds.value);
        notifications.value.forEach((n) => next.add(n.id));
        readIds.value = next;
    };

    const dismissNotification = (id: number) => {
        notifications.value = notifications.value.filter((n) => n.id !== id);
        if (readIds.value.has(id)) {
            const next = new Set(readIds.value);
            next.delete(id);
            readIds.value = next;
        }
    };

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