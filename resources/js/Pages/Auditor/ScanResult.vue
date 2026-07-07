<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ScanResultCard from '@/Components/ScanResultCard.vue'

const props = defineProps({
    scan    : { type: Object, required: true },
    fkButir : { type: Array,  default: () => [] },
})
</script>

<template>
    <AppLayout title="Hasil Scan">
        <template #header>
            <div class="page-header">
                <button class="btn-back" @click="router.back()">← Kembali</button>
                <h2 class="page-title">Hasil Pemindaian</h2>
            </div>
        </template>

        <ScanResultCard
            :scan="scan"
            :fk-butir="fkButir"
            :plan-id="scan.audit_plan_id ?? 0"
        />
    </AppLayout>
</template>

<style scoped>
.page-header { display: flex; align-items: center; gap: 16px; }
.page-title  { font-size: 1.25rem; font-weight: 700; color: var(--navy, #1F4E79); margin: 0; }
.btn-back    { background: none; border: 1px solid #d1d5db; border-radius: 6px; padding: 5px 12px; cursor: pointer; font-size: .875rem; color: #374151; }
.btn-back:hover { background: #f9fafb; }
</style>
