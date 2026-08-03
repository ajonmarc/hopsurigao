<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ChevronDown } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="flex items-center gap-2 px-2" size="lg">
                <UserInfo :user="user" />
                <ChevronDown class="ml-1 size-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56 rounded-lg" align="end" :side-offset="4">
            <UserMenuContent :user="user" />
        </DropdownMenuContent>
    </DropdownMenu>
</template>