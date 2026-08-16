<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import QrScanner from 'qr-scanner';
import Heading from '@/components/Heading.vue';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, XCircle, RotateCcw, Camera, Upload, ImageOff } from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Scan Booking QR', href: '/admin/bookings/scan' }],
    },
});

type VerifyResult = {
    found: boolean;
    valid?: boolean;
    message?: string;
    booking?: {
        id: number;
        guest_name: string;
        guest_email: string | null;
        package_name: string;
        tour_date: string | null;
        pickup_location: string;
        pickup_time: string | null;
        number_of_guests: number;
        booking_status: string;
    };
};

const videoRef = ref<HTMLVideoElement | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
let scanner: QrScanner | null = null;

const scanning = ref(true);
const loading = ref(false);
const result = ref<VerifyResult | null>(null);
const cameraError = ref<string | null>(null);

// NEW: separate error state for the upload path, so a bad image
// doesn't get confused with a camera-access problem.
const uploadError = ref<string | null>(null);
const uploading = ref(false);

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-700',
    confirmed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
    completed: 'bg-green-100 text-green-700',
};

const formatTime = (timeString: string | null | undefined) => {
    if (!timeString) return '';
    const [hours, minutes] = timeString.split(':');
    const date = new Date();
    date.setHours(parseInt(hours), parseInt(minutes));
    return date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    });
};

const handleScan = async (qrToken: string) => {
    if (loading.value) return;

    // Pause live scanning while we verify, to avoid firing the same
    // token repeatedly while it's still in the camera's view. Not
    // relevant for uploads, but harmless to set either way.
    scanning.value = false;
    loading.value = true;
    uploadError.value = null;

    try {
        const { data } = await axios.post<VerifyResult>('/admin/bookings-scan/verify', {
            qr_token: qrToken,
        });
        result.value = data;
    } catch (err: any) {
        if (err.response?.status === 404) {
            result.value = { found: false, message: 'No booking found for this QR code.' };
        } else {
            console.error('[qr-scan] verify failed', err);
            result.value = { found: false, message: 'Something went wrong verifying this code.' };
        }
    } finally {
        loading.value = false;
    }
};

const scanAgain = () => {
    result.value = null;
    cameraError.value = null;
    uploadError.value = null;
    scanning.value = true;
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Opens the OS file/photo picker. Works whether or not the camera
// stream is running — useful as the primary path on devices with no
// camera, or as a fallback when getUserMedia fails.
const triggerUpload = () => {
    fileInputRef.value?.click();
};

const handleFileChange = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;

    uploadError.value = null;
    uploading.value = true;

    try {
        const qrResult = await QrScanner.scanImage(file, { returnDetailedScanResult: true });
        await handleScan(qrResult.data);
    } catch (err) {
        console.error('[qr-scan] image decode failed', err);
        uploadError.value = 'Could not find a QR code in that image. Try a clearer photo.';
    } finally {
        uploading.value = false;
        input.value = ''; // allow re-selecting the same file
    }
};

onMounted(async () => {
    if (!videoRef.value) return;

    try {
        scanner = new QrScanner(
            videoRef.value,
            (result) => handleScan(result.data),
            {
                highlightScanRegion: true,
                highlightCodeOutline: true,
                preferredCamera: 'environment', // rear camera on mobile
            }
        );
        await scanner.start();
    } catch (err) {
        console.error('[qr-scan] camera init failed', err);
        cameraError.value = 'Could not access the camera. Check browser permissions and try again, or upload a QR code image instead.';
    }
});

onUnmounted(() => {
    scanner?.stop();
    scanner?.destroy();
});
</script>

<template>
    <Head title="Scan Booking QR" />
    <div class="px-4 py-6">
        <Heading title="Scan Booking QR" description="Scan a tourist's booking QR code to verify and check them in" />

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <!-- Camera -->
            <Card>
                <CardContent class="p-4">
                    <div class="relative aspect-square w-full overflow-hidden rounded-lg bg-black">
                        <video ref="videoRef" class="h-full w-full object-cover" />

                        <div v-if="cameraError" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-black/80 p-4 text-center text-sm text-white">
                            <ImageOff class="h-8 w-8 text-white/70" />
                            <p>{{ cameraError }}</p>
                        </div>

                        <div v-if="!scanning && !cameraError && !uploading" class="absolute inset-0 flex items-center justify-center bg-black/60">
                            <Button variant="secondary" @click="scanAgain">
                                <RotateCcw class="mr-2 h-4 w-4" />
                                Scan Next
                            </Button>
                        </div>

                        <div v-if="uploading" class="absolute inset-0 flex items-center justify-center bg-black/60 text-sm text-white">
                            Reading image...
                        </div>
                    </div>

                    <p class="mt-3 flex items-center gap-1.5 text-xs text-muted-foreground">
                        <Camera class="h-3.5 w-3.5" />
                        Point the camera at the guest's QR code.
                    </p>

                    <!-- Upload fallback, always available regardless of camera state -->
                    <div class="mt-3 border-t pt-3">
                        <input
                            ref="fileInputRef"
                            type="file"
                            accept="image/*"
                            capture="environment"
                            class="hidden"
                            @change="handleFileChange"
                        />
                        <Button variant="outline" size="sm" class="w-full" :disabled="uploading" @click="triggerUpload">
                            <Upload class="mr-2 h-4 w-4" />
                            {{ uploading ? 'Reading...' : 'Upload QR Code Image' }}
                        </Button>
                        <p v-if="uploadError" class="mt-2 text-center text-xs text-red-500">
                            {{ uploadError }}
                        </p>
                        <p v-else class="mt-2 text-center text-xs text-muted-foreground">
                            Camera not working? Upload a photo or screenshot of the QR code instead.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Result -->
            <Card>
                <CardContent class="p-4">
                    <div v-if="loading" class="flex h-full items-center justify-center py-12 text-sm text-muted-foreground">
                        Verifying...
                    </div>

                    <div v-else-if="!result" class="flex h-full items-center justify-center py-12 text-center text-sm text-muted-foreground">
                        Scan a QR code to see booking details here.
                    </div>

                    <div v-else-if="!result.found" class="space-y-3 py-6 text-center">
                        <XCircle class="mx-auto h-10 w-10 text-red-500" />
                        <p class="font-medium">{{ result.message || 'Booking not found.' }}</p>
                        <Button size="sm" @click="scanAgain">Scan Next</Button>
                    </div>

                    <div v-else-if="result.found && !result.valid" class="space-y-3 py-6 text-center">
                        <XCircle class="mx-auto h-10 w-10 text-amber-500" />
                        <p class="font-medium">{{ result.message }}</p>
                        <Button size="sm" @click="scanAgain">Scan Next</Button>
                    </div>

                    <div v-else-if="result.booking" class="space-y-4">
                        <div class="flex items-center gap-2 text-green-600">
                            <CheckCircle class="h-6 w-6" />
                            <span class="font-semibold">Valid Booking</span>
                        </div>

                        <div class="space-y-2 rounded-lg border p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Guest</span>
                                <span class="text-sm font-medium">{{ result.booking.guest_name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Package</span>
                                <span class="text-sm font-medium">{{ result.booking.package_name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Tour Date</span>
                                <span class="text-sm font-medium">{{ result.booking.tour_date }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Pickup</span>
                                <span class="text-sm font-medium">
                                    {{ result.booking.pickup_location }}
                                    <span v-if="result.booking.pickup_time" class="text-muted-foreground">
                                        &middot; {{ formatTime(result.booking.pickup_time) }}
                                    </span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Guests</span>
                                <span class="text-sm font-medium">{{ result.booking.number_of_guests }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Status</span>
                                <Badge :class="statusColors[result.booking.booking_status] || 'bg-neutral-100 text-neutral-600'">
                                    {{ result.booking.booking_status }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Booking ID</span>
                                <span class="text-sm font-medium">#{{ result.booking.id }}</span>
                            </div>
                        </div>

                        <Button class="w-full" @click="scanAgain">Scan Next</Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>