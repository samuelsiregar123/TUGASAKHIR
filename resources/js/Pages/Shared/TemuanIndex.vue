<script setup>
import { router } from '@inertiajs/vue3'
import SidebarLayout from '@/Layouts/SidebarLayout.vue'

const props = defineProps({
    plans      : { type: Array,   default: () => [] },
    isKetuaTim : { type: Boolean, default: false },
})

function bukaPlan(plan) {
    router.visit(`${plan.base_url}?plan=${plan.id}`)
}
</script>

<template>
    <SidebarLayout title="Temuan Audit">
        <p class="page-header">Temuan Audit</p>
        <p class="page-sub">
            {{ isKetuaTim
                ? 'Semua audit di sistem — pilih untuk melihat dan mengelola temuan'
                : 'Audit yang ditugaskan kepada Anda — pilih untuk melihat dan mengelola temuan' }}
        </p>

        <div v-if="plans.length === 0" class="empty-state">
            {{ isKetuaTim ? 'Belum ada audit plan di sistem.' : 'Anda belum ditugaskan pada audit plan mana pun.' }}
        </div>

        <div v-for="p in plans" :key="p.id" class="plan-card">
            <div class="plan-info">
                <div class="plan-instansi">{{ p.instansi }}</div>
                <div class="plan-meta">{{ p.url_target }}
                    <span v-if="p.waktu_mulai">&nbsp;·&nbsp; Mulai {{ p.waktu_mulai }}</span>
                </div>
            </div>

            <div class="plan-right">
                <span :class="['temuan-count', p.total_temuan > 0 ? 'count-ada' : 'count-kosong']">
                    {{ p.total_temuan }} temuan
                </span>
                <button class="btn-buka" @click="bukaPlan(p)">
                    {{ p.total_temuan > 0 ? 'Kelola Temuan' : 'Tambah Temuan' }}
                </button>
            </div>
        </div>
    </SidebarLayout>
</template>

<style scoped>
.page-header { font-size:1.1rem; font-weight:700; color:#1F4E79; margin-bottom:2px; }
.page-sub    { font-size:.8rem; color:#888; margin-bottom:16px; }
.empty-state { text-align:center; color:#aaa; padding:40px 0; font-size:.85rem; }

.plan-card {
    display:flex; justify-content:space-between; align-items:center;
    border:1px solid #e5e7eb; border-radius:8px; padding:14px 16px;
    margin-bottom:10px; background:#fff; flex-wrap:wrap; gap:12px;
}
.plan-info     { flex:1; min-width:200px; }
.plan-instansi { font-size:.9rem; font-weight:700; color:#1F4E79; }
.plan-meta     { font-size:.75rem; color:#888; margin-top:2px; }

.plan-right    { display:flex; align-items:center; gap:12px; }

.temuan-count  { font-size:.78rem; font-weight:700; padding:3px 10px; border-radius:8px; }
.count-ada     { background:#dbeafe; color:#1d4ed8; }
.count-kosong  { background:#f3f4f6; color:#6b7280; }

.btn-buka {
    background:#1F4E79; color:#fff; border:none; border-radius:6px;
    padding:7px 16px; font-size:.8rem; font-weight:600; cursor:pointer; white-space:nowrap;
}
.btn-buka:hover { background:#16396a; }
</style>
