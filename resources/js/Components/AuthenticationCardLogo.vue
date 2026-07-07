<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'">
        <div class="flex items-center gap-4">
            <svg width="52" height="62" viewBox="0 0 52 62" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M26 2L4 11V30C4 43.5 13.5 55.8 26 59.5C38.5 55.8 48 43.5 48 30V11L26 2Z" fill="#1D4ED8"/>
                <path d="M26 2L4 11V30C4 43.5 13.5 55.8 26 59.5C38.5 55.8 48 43.5 48 30V11L26 2Z" stroke="#1E40AF" stroke-width="1.5"/>
                <text x="26" y="36" text-anchor="middle" fill="white" font-size="22" font-weight="bold" font-family="Arial, sans-serif">S</text>
            </svg>
            <div class="flex flex-col leading-tight">
                <span class="text-gray-900 font-bold text-3xl tracking-wide">SIASKA</span>
                <span class="text-gray-500 text-xs tracking-widest uppercase">Sistem Audit Keamanan SPBE</span>
            </div>
        </div>
    </Link>
</template>
