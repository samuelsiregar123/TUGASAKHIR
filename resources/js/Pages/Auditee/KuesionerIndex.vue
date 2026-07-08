<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plans: { type: Array, default: () => [] },
})

const search = ref('')
const filtered = computed(() => {
    const q = search.value.toLowerCase()
    return q ? props.plans.filter(p => p.instansi.toLowerCase().includes(q)) : props.plans
})

function statusBadge(plan) {
    if (plan.status_pengisian === 'selesai') return { label: 'Selesai',     cls: 'chip-selesai' }
    if (plan.butir_terisi > 0)              return { label: 'Sedang diisi', cls: 'chip-proses'  }
    return                                         { label: 'Belum mulai',  cls: 'chip-none'    }
}
</script>

<template>
    <SidebarLayout title="Kuesioner Audit">
        <p class="page-header">Kuesioner Audit</p>
        <p class="page-sub">Pilih audit plan untuk mengisi kuesioner</p>

        <div class="toolbar">
            <input v-model="search" class="search-input" placeholder="Cari instansi...">
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:4%">No</th>
                        <th style="width:32%">Instansi</th>
                        <th style="width:26%">URL Aplikasi</th>
                        <th style="width:14%; text-align:center">Status</th>
                        <th style="width:14%; text-align:center">Progress</th>
                        <th style="width:10%; text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(plan, i) in filtered" :key="plan.id">
                        <td>{{ i + 1 }}</td>
                        <td>
                            <div class="td-instansi">{{ plan.instansi }}</div>
                        </td>
                        <td>
                            <div class="td-app">{{ plan.aplikasi }}</div>
                        </td>
                        <td style="text-align:center">
                            <span :class="['chip', statusBadge(plan).cls]">
                                {{ statusBadge(plan).label }}
                            </span>
                        </td>
                        <td style="text-align:center; font-size:.75rem">
                            <span :style="{ color: plan.butir_terisi >= 150 ? '#375623' : '#7B6000', fontWeight: '600' }">
                                {{ plan.butir_terisi }}/150
                            </span>
                            <span style="color:#aaa"> butir</span>
                        </td>
                        <td style="text-align:center">
                            <button
                                class="btn-link"
                                @click="router.visit(`/auditee/kuesioner/${plan.id}`)"
                            >Buka Kuesioner</button>
                        </td>
                    </tr>
                    <tr v-if="filtered.length === 0">
                        <td colspan="6" style="text-align:center; color:#aaa; padding:24px">
                            {{ plans.length === 0 ? 'Belum ada audit plan. Ajukan audit terlebih dahulu.' : 'Tidak ada data yang cocok.' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </SidebarLayout>
</template>

<style scoped>
.page-header  { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub     { font-size:.8rem; color:#888; margin-bottom:16px; }
.toolbar      { display:flex; gap:8px; margin-bottom:12px; }
.search-input { flex:1; max-width:280px; height:32px; border:1px solid #ddd; border-radius:6px; padding:0 10px; font-size:.8rem; }
.table-wrap   { overflow-x:auto; }
.data-table   { width:100%; border-collapse:collapse; font-size:.8rem; }
.data-table th { background:#1F4E79; color:#fff; padding:8px; text-align:left; font-size:.75rem; }
.data-table td { padding:9px 8px; border-bottom:1px solid #eee; vertical-align:middle; }
.data-table tr:hover td { background:#f8fbfe; }
.td-instansi  { font-weight:600; color:#1F4E79; }
.td-app       { font-size:.75rem; color:#666; word-break:break-all; }
.chip         { font-size:.7rem; padding:2px 9px; border-radius:8px; font-weight:600; display:inline-block; }
.chip-selesai { background:#EAF3DE; color:#375623; }
.chip-proses  { background:#FFF8E1; color:#7B6000; }
.chip-none    { background:#f3f4f6; color:#6b7280; }
.btn-link     { background:none; border:none; color:#1F4E79; font-weight:600; font-size:.75rem; text-decoration:underline; cursor:pointer; white-space:nowrap; }
</style>
