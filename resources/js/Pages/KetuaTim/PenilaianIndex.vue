<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plans: { type: Array, default: () => [] },
})

function bukaPenilaian(planId) {
    router.visit(`/auditor/penilaian?plan=${planId}`)
}
</script>

<template>
    <SidebarLayout title="Penilaian">
        <p class="page-header">Penilaian Butir</p>
        <p class="page-sub">Pilih audit untuk melihat dan menilai butir penilaian</p>

        <div v-if="plans.length === 0" class="empty-state">
            Belum ada audit plan di sistem.
        </div>

        <div v-for="p in plans" :key="p.id" class="plan-card">
            <div class="plan-info">
                <div class="plan-instansi">{{ p.instansi }}</div>
                <div class="plan-meta">{{ p.url_target }} &nbsp;·&nbsp; Mulai {{ p.waktu_mulai }}</div>
                <div class="auditor-list" v-if="p.auditors.length">
                    Auditor: {{ p.auditors.join(', ') }}
                </div>
            </div>

            <div class="plan-right">
                <!-- Progress bar -->
                <div class="progress-wrap">
                    <div class="progress-label">
                        {{ p.dinilai }}/{{ p.total_butir }} butir dinilai
                        <span class="persen">{{ p.persen }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div
                            class="progress-fill"
                            :style="{ width: p.persen + '%', background: p.persen === 100 ? '#166534' : '#1F4E79' }"
                        ></div>
                    </div>
                </div>

                <button class="btn-buka" @click="bukaPenilaian(p.id)">
                    {{ p.persen === 100 ? 'Lihat Penilaian' : 'Buka Penilaian' }}
                </button>
            </div>
        </div>
    </SidebarLayout>
</template>

<style scoped>
.page-header  { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub     { font-size:.8rem; color:#888; margin-bottom:16px; }
.empty-state  { text-align:center; color:#aaa; padding:40px 0; font-size:.85rem; }

.plan-card {
    display:flex; justify-content:space-between; align-items:center;
    border:1px solid #e5e7eb; border-radius:8px; padding:14px 16px;
    margin-bottom:10px; background:#fff; flex-wrap:wrap; gap:12px;
}
.plan-info     { flex:1; min-width:200px; }
.plan-instansi { font-size:.9rem; font-weight:700; color:#1F4E79; }
.plan-meta     { font-size:.75rem; color:#888; margin-top:2px; }
.auditor-list  { font-size:.72rem; color:#6b7280; margin-top:4px; }

.plan-right    { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }

.progress-wrap { min-width:160px; }
.progress-label{ font-size:.72rem; color:#374151; margin-bottom:4px; display:flex; justify-content:space-between; }
.persen        { font-weight:700; color:#1F4E79; }
.progress-bar  { height:6px; background:#e5e7eb; border-radius:4px; overflow:hidden; }
.progress-fill { height:100%; border-radius:4px; transition:width .3s; }

.btn-buka {
    background:#1F4E79; color:#fff; border:none; border-radius:6px;
    padding:7px 16px; font-size:.8rem; font-weight:600; cursor:pointer;
    white-space:nowrap;
}
.btn-buka:hover { background:#16396a; }
</style>
