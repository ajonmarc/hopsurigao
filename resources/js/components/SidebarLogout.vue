<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { LogOut } from '@lucide/vue';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarSeparator } from '@/components/ui/sidebar';
import { logout } from '@/routes';
</script>

<template>
    <SidebarSeparator class="mx-0 mb-1 w-full" />
    <SidebarMenu>
        <SidebarMenuItem>
            <SidebarMenuButton as-child size="lg">
                <Link :href="logout()" method="post" as="button" class="w-full">
                    <LogOut class="size-4" />
                    <span>Log out</span>
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    </SidebarMenu>
</template>