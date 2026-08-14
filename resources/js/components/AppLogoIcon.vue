<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Sun -->
        <circle cx="20" cy="10" r="5" fill="currentColor" opacity="0.35" />

        <!-- Sail -->
        <path
            fill="currentColor"
            d="M20.8 6.5v14.3h8.9c-1.1-6.9-4.6-12.1-8.9-14.3Z"
        />

        <!-- Mast -->
        <rect x="19.4" y="6.5" width="1.2" height="18" fill="currentColor" />

        <!-- Hull -->
        <path
            fill="currentColor"
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M6 25h28l-3.6 8.4a3 3 0 0 1-2.76 1.83H12.36a3 3 0 0 1-2.76-1.83L6 25Zm3.15 2 2.36 5.5a1 1 0 0 0 .92.61h15.14a1 1 0 0 0 .92-.61L30.85 27H9.15Z"
        />

        <!-- Waves -->
        <path
            fill="currentColor"
            d="M0 38.5c2.7 0 2.7 2 5.4 2s2.7-2 5.4-2 2.7 2 5.4 2 2.7-2 5.4-2 2.7 2 5.4 2 2.7-2 5.4-2 2.7 2 5.4 2 2.7-2 5.4-2v2.2c-2.7 0-2.7 2-5.4 2s-2.7-2-5.4-2-2.7 2-5.4 2-2.7-2-5.4-2-2.7 2-5.4 2-2.7-2-5.4-2-2.7 2-5.4 2S2.7 42.9 0 42.9V38.5Z"
        />
    </svg>
</template>