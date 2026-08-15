<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import QRCode from 'qrcode';

const props = withDefaults(defineProps<{
    value: string;
    size?: number;
}>(), {
    size: 220,
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
const dataUrl = ref<string | null>(null);
const error = ref<string | null>(null);

const render = async () => {
    if (!props.value) return;

    try {
        error.value = null;

        // Render to a data URL (used for the download link) and also
        // draw directly to the canvas (crisper on high-DPI screens
        // than scaling an <img> would be).
        dataUrl.value = await QRCode.toDataURL(props.value, {
            width: props.size,
            margin: 1,
        });

        if (canvasRef.value) {
            await QRCode.toCanvas(canvasRef.value, props.value, {
                width: props.size,
                margin: 1,
            });
        }
    } catch (err) {
        console.error('[qr-code] render failed', err);
        error.value = 'Could not generate QR code.';
    }
};

const download = () => {
    if (!dataUrl.value) return;
    const link = document.createElement('a');
    link.href = dataUrl.value;
    link.download = 'booking-qr-code.png';
    link.click();
};

onMounted(render);
watch(() => [props.value, props.size], render);

defineExpose({ download });
</script>

<template>
    <div class="flex flex-col items-center gap-3">
        <div v-if="error" class="text-sm text-destructive">{{ error }}</div>
        <canvas v-else ref="canvasRef" class="rounded-md border" />
    </div>
</template>