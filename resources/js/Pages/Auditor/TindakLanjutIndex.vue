<script setup>
import { ref, computed } from 'vue'
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
</script>

<template>
    <SidebarLayout title="Tindak Lanjut">
        <p class="page-header">Tindak Lanjut Temuan</p>
        <p class="page-sub">Audit yang Anda tangani — lihat thread dan kirim balasan</p>

        <div class="toolbar">
            <input v-model="search" class="search-input" placeholder="🔍 Cari instansi...">
        </div>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:5%">No</th>
                        <th style="width:38%">Instansi</th>
                        <th style="width:17%; text-align:center">Total Temuan</th>
                        <th style="width:17%; text-align:center">Proses</th>
                        <th style="width:15%; text-align:center">Selesai</th>
                        <th style="width:10%; text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(plan, i) in filtered" :key="plan.id">
                        <td>{{ i + 1 }}</td>
                        <td>
                            <div class="td-instansi">{{ plan.instansi }}</div>
                            <div class="td-app">{{ plan.aplikasi }}</div>
                        </td>
                        <td style="text-align:center; font-weight:700">{{ plan.total_temuan }}</td>
                        <td style="text-align:center">
                            <span v-if="plan.proses > 0" class="chip-proses">{{ plan.proses }} proses</span>
                            <span v-else class="chip-none">—</span>
                        </td>
                        <td style="text-align:center">
                            <span v-if="plan.selesai > 0" class="chip-selesai">{{ plan.selesai }} selesai</span>
                            <span v-else class="chip-none">—</span>
                        </td>
                        <td style="text-align:center">
                            <button v-if="plan.total_temuan > 0" class="btn-link"
                                @click="router.visit(`/auditor/tindak-lanjut/${plan.id}`)">Buka</button>
                            <span v-else class="btn-link-off">—</span>
                        </td>
                    </tr>
                    <tr v-if="filtered.length === 0">
                        <td colspan="6" style="text-align:center; color:#aaa; padding:24px">
                            {{ search ? 'Tidak ada hasil pencarian.' : 'Belum ada audit yang ditugaskan kepada Anda.' }}
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
.search-input { flex:1; height:32px; border:1px solid #ddd; border-radius:6px; padding:0 10px; font-size:.8rem; }
.table-wrap   { overflow-x:auto; }
.data-table   { width:100%; border-collapse:collapse; font-size:.8rem; }
.data-table th { background:#1F4E79; color:#fff; padding:8px; text-align:left; font-size:.75rem; }
.data-table td { padding:9px 8px; border-bottom:1px solid #eee; vertical-align:middle; }
.data-table tr:hover td { background:#f8fbfe; }
.td-instansi  { font-weight:600; color:#1F4E79; }
.td-app       { font-size:.72rem; color:#888; }
.chip-proses  { background:#FFF2CC; color:#7B6000; font-size:.7rem; padding:2px 8px; border-radius:8px; font-weight:600; }
.chip-selesai { background:#EAF3DE; color:#375623; font-size:.7rem; padding:2px 8px; border-radius:8px; font-weight:600; }
.chip-none    { color:#bbb; }
.btn-link     { background:none; border:none; color:#1F4E79; font-weight:600; font-size:.75rem; text-decoration:underline; cursor:pointer; }
.btn-link-off { color:#bbb; font-size:.75rem; }
</style>
