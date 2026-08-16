<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
    ArrowLeft,
    MapPin,
    Calendar,
    Users,
    Package as PackageIcon,
    CheckCircle,
    Clock,
    Info,
    AlertCircle,
    CalendarClock,
    Loader2
} from '@lucide/vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import tourist from '@/routes/tourist';

const props = defineProps<{
    package: {
        id: number;
        package_name: string;
        destination: string;
        description: string;
        image: string | null;
        price: number;
        status: string;
        inclusions: Array<{
            id: number;
            description: string;
        }>;
        reminders: Array<{
            id: number;
            description: string;
        }>;
    };
    tourDates: Array<{
        id: number;
        tour_date: string;
        capacity: number;
        available_spots: number;
        is_available: boolean;
    }>;
    pickupSchedules: Array<{
        id: number;
        tour_date_id: number;
        pickup_location_id: number;
        label: string;
    }>;
    tourTimes: Array<{
        id: number;
        time: string;
        description: string;
    }>;
}>();

const selectedTourDate = ref<string>('');
const selectedPickupSchedule = ref<string>('');
const numberOfGuests = ref(1);

// NEW: loading state while navigating to the booking page
const isProcessing = ref(false);

// NEW: field-level error state, shown inline instead of alert()
const errors = ref<{ tourDate?: string; pickupSchedule?: string }>({});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatTime = (timeString: string) => {
    if (!timeString) return 'N/A';
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const selectedTourDateData = computed(() => {
    return props.tourDates.find(td => td.id === Number(selectedTourDate.value));
});

const maxGuests = computed(() => {
    return selectedTourDateData.value?.available_spots || 20;
});

const filteredPickupSchedules = computed(() => {
    if (!selectedTourDate.value) return [];
    return props.pickupSchedules.filter(
        (schedule) => String(schedule.tour_date_id) === selectedTourDate.value
    );
});

// Reset pickup selection + clear its error whenever the tour date changes
watch(selectedTourDate, () => {
    selectedPickupSchedule.value = '';
    errors.value.pickupSchedule = undefined;
    if (selectedTourDate.value) {
        errors.value.tourDate = undefined;
    }
});

watch(selectedPickupSchedule, () => {
    if (selectedPickupSchedule.value) {
        errors.value.pickupSchedule = undefined;
    }
});

const handleBookNow = () => {
    // validate inline instead of alert()
    errors.value = {};

    if (!selectedTourDate.value) {
        errors.value.tourDate = 'Please select a tour date.';
    }
    if (!selectedPickupSchedule.value) {
        errors.value.pickupSchedule = 'Please select a pickup location.';
    }
    if (errors.value.tourDate || errors.value.pickupSchedule) {
        return;
    }

    if (isProcessing.value) return;
    isProcessing.value = true;

    const params = new URLSearchParams({
        tour_date_id: selectedTourDate.value,
        pickup_schedule_id: selectedPickupSchedule.value,
        guests: String(numberOfGuests.value),
    });

    router.get(`/tourist/bookings/create?${params.toString()}`, {}, {
        onFinish: () => {
            isProcessing.value = false;
        },
    });
};
</script>

<template>
    <Head :title="package.package_name" />
    <div class="px-4 py-6">
        <!-- Back Button -->
        <Button as-child variant="ghost" size="sm" class="mb-4 -ml-2">
            <Link :href="tourist.packages.index().url">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Packages
            </Link>
        </Button>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Package Image -->
                <div class="relative overflow-hidden rounded-xl">
                    <div class="aspect-video w-full bg-muted">
                        <img
                            v-if="package.image"
                            :src="`/storage/${package.image}`"
                            :alt="package.package_name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full items-center justify-center">
                            <PackageIcon class="h-20 w-20 text-muted-foreground" />
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-6">
                        <h1 class="text-2xl font-bold text-white">{{ package.package_name }}</h1>
                        <p class="flex items-center gap-2 text-white/80">
                            <MapPin class="h-4 w-4" />
                            {{ package.destination }}
                        </p>
                    </div>
                </div>

                <!-- Description -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle>About This Tour</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-wrap text-muted-foreground">
                            {{ package.description }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Inclusions -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CheckCircle class="h-5 w-5 text-green-500" />
                            What's Included
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="package.inclusions && package.inclusions.length > 0" class="space-y-2">
                            <li
                                v-for="inclusion in package.inclusions"
                                :key="inclusion.id"
                                class="flex items-start gap-2 text-sm"
                            >
                                <CheckCircle class="mt-0.5 h-4 w-4 flex-shrink-0 text-green-500" />
                                <span>{{ inclusion.description }}</span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-muted-foreground">No inclusions listed.</p>
                    </CardContent>
                </Card>

                <!-- Schedules / Tour Times -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CalendarClock class="h-5 w-5 text-blue-500" />
                            Schedules
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="tourTimes && tourTimes.length > 0" class="space-y-3">
                            <div
                                v-for="time in tourTimes"
                                :key="time.id"
                                class="flex items-center gap-4 rounded-lg border p-3"
                            >
                                <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                    <Clock class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="font-medium">{{ formatTime(time.time) }}</p>
                                    <p class="text-sm text-muted-foreground">{{ time.description }}</p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">No schedules available.</p>
                    </CardContent>
                </Card>

                <!-- Important Reminders -->
                <Card class="mt-6">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <AlertCircle class="h-5 w-5 text-yellow-500" />
                            Important Reminders
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="package.reminders && package.reminders.length > 0" class="space-y-2">
                            <li
                                v-for="reminder in package.reminders"
                                :key="reminder.id"
                                class="flex items-start gap-2 text-sm"
                            >
                                <AlertCircle class="mt-0.5 h-4 w-4 flex-shrink-0 text-yellow-500" />
                                <span>{{ reminder.description }}</span>
                            </li>
                        </ul>
                        <p v-else class="text-sm text-muted-foreground">No reminders listed.</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Sidebar - Booking -->
            <div class="lg:col-span-1">
                <div class="sticky top-24">
                    <Card>
                        <CardHeader>
                            <CardTitle>Book This Tour</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <!-- Price -->
                            <div class="rounded-lg bg-primary/5 p-4 text-center">
                                <p class="text-3xl font-bold text-primary">
                                    {{ formatPrice(package.price) }}
                                </p>
                                <p class="text-sm text-muted-foreground">per person</p>
                            </div>

                            <!-- Tour Date Selection -->
                            <div>
                                <label class="mb-2 block text-sm font-medium">
                                    Select Tour Date <span class="text-red-500">*</span>
                                </label>
                                <Select
                                    v-model="selectedTourDate"
                                    :disabled="isProcessing"
                                >
                                    <SelectTrigger :class="errors.tourDate ? 'border-red-500 focus:ring-red-500' : ''">
                                        <SelectValue placeholder="Choose a date" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="date in tourDates"
                                            :key="date.id"
                                            :value="String(date.id)"
                                            :disabled="!date.is_available"
                                        >
                                            {{ formatDate(date.tour_date) }}
                                            <span v-if="!date.is_available" class="ml-2 text-red-500">
                                                (Full)
                                            </span>
                                            <span v-else class="ml-2 text-muted-foreground">
                                                ({{ date.available_spots }} spots left)
                                            </span>
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.tourDate" class="mt-1 text-xs text-red-500">
                                    {{ errors.tourDate }}
                                </p>
                            </div>

                            <!-- Pickup Schedule -->
                            <div>
                                <label class="mb-2 block text-sm font-medium">
                                    Pickup Location & Time <span class="text-red-500">*</span>
                                </label>
                                <Select
                                    v-model="selectedPickupSchedule"
                                    :disabled="!selectedTourDate || isProcessing"
                                >
                                    <SelectTrigger :class="errors.pickupSchedule ? 'border-red-500 focus:ring-red-500' : ''">
                                        <SelectValue
                                            :placeholder="selectedTourDate ? 'Select pickup location & time' : 'Select a tour date first'"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="schedule in filteredPickupSchedules"
                                            :key="schedule.id"
                                            :value="String(schedule.id)"
                                        >
                                            {{ schedule.label }}
                                        </SelectItem>
                                        <p v-if="selectedTourDate && filteredPickupSchedules.length === 0" class="px-2 py-1.5 text-xs text-muted-foreground">
                                            No pickup schedules for this tour date.
                                        </p>
                                    </SelectContent>
                                </Select>
                                <p v-if="errors.pickupSchedule" class="mt-1 text-xs text-red-500">
                                    {{ errors.pickupSchedule }}
                                </p>
                            </div>

                            <!-- Number of Guests -->
                            <div>
                                <label class="mb-2 block text-sm font-medium">Number of Guests</label>
                                <input
                                    v-model.number="numberOfGuests"
                                    type="number"
                                    min="1"
                                    :max="maxGuests"
                                    :disabled="isProcessing"
                                    class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm disabled:opacity-50"
                                />
                                <p v-if="selectedTourDateData" class="mt-1 text-xs text-muted-foreground">
                                    Max {{ maxGuests }} spots available
                                </p>
                            </div>

                            <!-- Book Now Button -->
                            <Button
                                class="w-full"
                                size="lg"
                                @click="handleBookNow"
                                :disabled="isProcessing"
                            >
                                <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                                {{ isProcessing ? 'Processing...' : 'Book Now' }}
                            </Button>

                            <p class="text-center text-xs text-muted-foreground">
                                <Clock class="inline h-3 w-3" />
                                Instant confirmation
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>