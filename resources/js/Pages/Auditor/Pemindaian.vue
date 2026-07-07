<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    plan  : { type: Object, required: true },
    scans : { type: Array,  default: () => [] },
})

const TOOLS = ['curl', 'testssl', 'nmap', 'nikto', 'zap']

const TOOL_STYLE = {
    curl    : { bg: '#FFF8E1', border: '#F9A825', label: 'cURL / Guzzle' },
    testssl : { bg: '#E3F2FD', border: '#1976D2', label: 'testssl.sh' },
    nmap    : { bg: '#f5f5f4', border: '#78716c', label: 'Nmap' },
    nikto   : { bg: '#FFEBEE', border: '#D32F2F', label: 'Nikto' },
    zap     : { bg: '#FFEBEE', border: '#B71C1C', label: 'OWASP ZAP' },
}

const CANCEL_MSG = 'Dibatalkan oleh auditor'

const targetUrl        = ref(props.plan.url_target ?? '')
const scanMap          = ref(Object.fromEntries(props.scans.map(s => [s.tool, s])))
const showStopAllModal = ref(false)

// polling
let pollTimer = null

function allDone() {
    return TOOLS.every(t => {
        const s = scanMap.value[t]
        return !s || ['selesai', 'gagal'].includes(s.status)
    })
}

async function pollStatus() {
    try {
        const { data } = await axios.get(`/auditor/pemindaian/${props.plan.id}/scan/status`)
        data.scans.forEach(s => { scanMap.value[s.tool] = s })
        if (allDone()) stopPoll()
    } catch {}
}

function startPoll() {
    if (pollTimer) return
    pollTimer = setInterval(pollStatus, 5000)
}

function stopPoll() {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
}

onMounted(() => {
    if (!allDone()) startPoll()
})
onUnmounted(stopPoll)

function isRunning(tool) {
    return ['menunggu', 'berjalan'].includes(scanMap.value[tool]?.status)
}

function isCancelled(scan) {
    return scan.status === 'gagal' && scan.error_message === CANCEL_MSG
}

const hasAnyRunning = computed(() => TOOLS.some(t => isRunning(t)))

const statusBadge = {
    menunggu : { label: 'Menunggu',        cls: 'st-yellow' },
    berjalan : { label: 'Sedang berjalan', cls: 'st-blue'   },
    selesai  : { label: 'Selesai',         cls: 'st-green'  },
    gagal    : { label: 'Gagal',           cls: 'st-red'    },
}

function getStatusInfo(scan) {
    if (isCancelled(scan)) return { label: 'Dibatalkan', cls: 'st-orange' }
    return statusBadge[scan.status] ?? { label: scan.status, cls: 'st-gray' }
}

async function runScan(tool) {
    if (!targetUrl.value) { alert('Isi URL target terlebih dahulu.'); return }
    try {
        await axios.post(`/auditor/pemindaian/${props.plan.id}/scan/start`, {
            tool, target_url: targetUrl.value,
        })
        TOOLS.forEach(t => {
            if (tool === 'semua' || tool === t) {
                if (!scanMap.value[t] || !['menunggu','berjalan'].includes(scanMap.value[t].status)) {
                    scanMap.value[t] = { tool: t, status: 'menunggu', started_at: null, finished_at: null }
                }
            }
        })
        startPoll()
    } catch (e) {
        alert('Gagal memulai scan: ' + (e?.response?.data?.message ?? e.message))
    }
}

async function rerun(scan) {
    try {
        await axios.post(`/auditor/scan/${scan.id}/rerun`)
        scanMap.value[scan.tool] = { ...scan, status: 'menunggu', started_at: null, finished_at: null, error_message: null }
        startPoll()
    } catch (e) {
        alert('Gagal menjalankan ulang: ' + (e?.response?.data?.error ?? e.message))
    }
}

async function cancelScan(scan) {
    try {
        await axios.post(`/auditor/scan/${scan.id}/cancel`)
        scanMap.value[scan.tool] = {
            ...scanMap.value[scan.tool],
            status        : 'gagal',
            error_message : CANCEL_MSG,
            finished_at   : new Date().toLocaleString('id-ID'),
        }
        if (allDone()) stopPoll()
    } catch (e) {
        alert('Gagal membatalkan scan: ' + (e?.response?.data?.error ?? e.message))
    }
}

async function cancelAll() {
    showStopAllModal.value = false
    try {
        await axios.post(`/auditor/pemindaian/${props.plan.id}/cancel-all`)
        TOOLS.forEach(t => {
            if (isRunning(t)) {
                scanMap.value[t] = {
                    ...scanMap.value[t],
                    status        : 'gagal',
                    error_message : CANCEL_MSG,
                    finished_at   : new Date().toLocaleString('id-ID'),
                }
            }
        })
        if (allDone()) stopPoll()
    } catch (e) {
        alert('Gagal membatalkan semua scan: ' + (e?.response?.data?.error ?? e.message))
    }
}

function lihatHasil(scan) {
    router.visit(`/auditor/scan/${scan.id}/result`)
}
</script>

<template>
    <AppLayout title="Pemindaian Keamanan">
        <template #header>
            <div class="page-header">
                <button class="btn-back" @click="router.visit('/auditor/pemindaian')">← Kembali</button>
                <h2 class="page-title">Pemindaian — {{ plan.instansi }}</h2>
            </div>
        </template>

        <div class="content-card">
            <!-- URL Target -->
            <div class="url-row">
                <label class="url-label">URL Target</label>
                <input
                    v-if="plan.is_ketua"
                    v-model="targetUrl"
                    type="url"
                    class="url-input"
                    placeholder="https://..."
                />
                <span v-else class="url-display mono">{{ targetUrl }}</span>
            </div>

            <!-- Tool buttons -->
            <div class="tool-buttons">
                <button
                    v-for="tool in TOOLS"
                    :key="tool"
                    class="btn-tool"
                    :style="{ background: TOOL_STYLE[tool].bg, borderColor: TOOL_STYLE[tool].border }"
                    :disabled="isRunning(tool)"
                    @click="runScan(tool)"
                >
                    <span class="tool-dot" :style="{ background: TOOL_STYLE[tool].border }"></span>
                    {{ TOOL_STYLE[tool].label }}
                    <span v-if="isRunning(tool)" class="spinner"></span>
                </button>

                <button
                    class="btn-scan-all"
                    :disabled="hasAnyRunning"
                    @click="runScan('semua')"
                >
                    ▶ Scan Semua
                </button>

                <button
                    class="btn-stop-all"
                    :disabled="!hasAnyRunning"
                    @click="showStopAllModal = true"
                >
                    ■ Stop Semua
                </button>
            </div>

            <!-- Status table -->
            <div class="table-wrapper mt-20">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tool</th>
                            <th>Status</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Temuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="TOOLS.every(t => !scanMap[t])">
                            <td colspan="6" class="empty-row">Belum ada scan yang dijalankan.</td>
                        </tr>
                        <template v-for="tool in TOOLS" :key="tool">
                            <tr v-if="scanMap[tool]">
                                <td>
                                    <span
                                        class="tool-badge"
                                        :style="{ background: TOOL_STYLE[tool].bg, borderColor: TOOL_STYLE[tool].border }"
                                    >
                                        {{ TOOL_STYLE[tool].label }}
                                    </span>
                                </td>
                                <td>
                                    <span :class="['status-badge', getStatusInfo(scanMap[tool]).cls]">
                                        {{ getStatusInfo(scanMap[tool]).label }}
                                        <span v-if="scanMap[tool].status === 'berjalan'" class="spinner-inline"></span>
                                    </span>
                                </td>
                                <td class="text-sm">{{ scanMap[tool].started_at ?? '—' }}</td>
                                <td class="text-sm">{{ scanMap[tool].finished_at ?? '—' }}</td>
                                <td class="text-sm text-center">
                                    <span v-if="scanMap[tool].finding_count != null">
                                        {{ scanMap[tool].finding_count }}
                                    </span>
                                    <span v-else>—</span>
                                </td>
                                <td>
                                    <div class="action-row">
                                        <!-- Running / pending: tombol Stop -->
                                        <button
                                            v-if="isRunning(tool)"
                                            class="btn-action btn-stop"
                                            @click="cancelScan(scanMap[tool])"
                                        >■ Stop</button>

                                        <!-- Selesai: Lihat Hasil -->
                                        <button
                                            v-if="scanMap[tool].status === 'selesai'"
                                            class="btn-action btn-green"
                                            @click="lihatHasil(scanMap[tool])"
                                        >Lihat Hasil</button>

                                        <!-- Gagal biasa (bukan dibatalkan): Lihat Error + Jalankan Ulang -->
                                        <template v-if="scanMap[tool].status === 'gagal' && !isCancelled(scanMap[tool])">
                                            <button
                                                class="btn-action btn-red"
                                                @click="lihatHasil(scanMap[tool])"
                                            >Lihat Error</button>
                                            <button
                                                class="btn-action btn-gray"
                                                @click="rerun(scanMap[tool])"
                                            >Jalankan Ulang</button>
                                        </template>

                                        <!-- Dibatalkan: hanya Jalankan Ulang -->
                                        <button
                                            v-if="isCancelled(scanMap[tool])"
                                            class="btn-action btn-gray"
                                            @click="rerun(scanMap[tool])"
                                        >Jalankan Ulang</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal konfirmasi Stop Semua -->
        <div v-if="showStopAllModal" class="modal-overlay" @click.self="showStopAllModal = false">
            <div class="modal-box">
                <p class="modal-title">Batalkan Semua Pemindaian?</p>
                <p class="modal-body">Semua scan yang sedang berjalan atau menunggu akan dihentikan. Scan yang sudah selesai tidak terpengaruh.</p>
                <div class="modal-actions">
                    <button class="btn-modal-cancel" @click="showStopAllModal = false">Batal</button>
                    <button class="btn-modal-confirm" @click="cancelAll">Ya, Batalkan Semua</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.content-card { background: #fff; border-radius: 8px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
.page-header  { display: flex; align-items: center; gap: 16px; }
.page-title   { font-size: 1.25rem; font-weight: 700; color: var(--navy, #1F4E79); margin: 0; }
.btn-back     { background: none; border: 1px solid #d1d5db; border-radius: 6px; padding: 5px 12px; cursor: pointer; font-size: .875rem; color: #374151; }
.btn-back:hover { background: #f9fafb; }

.url-row   { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.url-label { font-weight: 600; color: #374151; font-size: .875rem; white-space: nowrap; }
.url-input { flex: 1; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: .875rem; font-family: monospace; }
.url-display { font-family: monospace; font-size: .875rem; color: #374151; }
.mono { font-family: monospace; }

.tool-buttons { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 8px; }
.btn-tool {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 8px; border: 1.5px solid;
    cursor: pointer; font-size: .875rem; font-weight: 500;
    transition: opacity .15s;
}
.btn-tool:disabled { opacity: .55; cursor: not-allowed; }
.tool-dot   { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

.btn-scan-all {
    padding: 8px 20px; border-radius: 8px; background: var(--navy, #1F4E79);
    color: #fff; border: none; cursor: pointer; font-size: .875rem; font-weight: 600;
}
.btn-scan-all:disabled { opacity: .55; cursor: not-allowed; }

.btn-stop-all {
    padding: 8px 20px; border-radius: 8px; background: #dc2626;
    color: #fff; border: none; cursor: pointer; font-size: .875rem; font-weight: 600;
}
.btn-stop-all:disabled { opacity: .4; cursor: not-allowed; }
.btn-stop-all:not(:disabled):hover { background: #b91c1c; }

/* Spinner */
@keyframes spin { to { transform: rotate(360deg); } }
.spinner { width: 14px; height: 14px; border: 2px solid rgba(0,0,0,.2); border-top-color: #333; border-radius: 50%; animation: spin .7s linear infinite; }
.spinner-inline { display: inline-block; width: 12px; height: 12px; border: 2px solid rgba(0,0,0,.15); border-top-color: #555; border-radius: 50%; animation: spin .7s linear infinite; vertical-align: middle; margin-left: 4px; }

.mt-20 { margin-top: 20px; }
.table-wrapper { overflow-x: auto; }
.data-table   { width: 100%; border-collapse: collapse; font-size: .875rem; }
.data-table th { background: #f8fafc; padding: 10px 14px; text-align: left; font-weight: 600; border-bottom: 2px solid #e5e7eb; color: #374151; }
.data-table td { padding: 10px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.data-table tr:hover td { background: #f9fafb; }
.empty-row { text-align: center; color: #9ca3af; padding: 32px !important; }
.text-sm { font-size: .875rem; }
.text-center { text-align: center; }

.tool-badge { display: inline-block; padding: 3px 10px; border-radius: 6px; border: 1.5px solid; font-size: .8rem; font-weight: 500; }

.status-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 999px; font-size: .8rem; font-weight: 600; }
.st-yellow { background: #fef9c3; color: #854d0e; }
.st-blue   { background: #dbeafe; color: #1d4ed8; }
.st-green  { background: #dcfce7; color: #166534; }
.st-red    { background: #fee2e2; color: #991b1b; }
.st-orange { background: #ffedd5; color: #9a3412; }
.st-gray   { background: #f3f4f6; color: #6b7280; }

.action-row { display: flex; gap: 6px; flex-wrap: wrap; }
.btn-action { padding: 4px 12px; border-radius: 6px; border: none; cursor: pointer; font-size: .8rem; font-weight: 500; }
.btn-green  { background: #dcfce7; color: #166534; }
.btn-red    { background: #fee2e2; color: #991b1b; }
.btn-gray   { background: #f3f4f6; color: #374151; }
.btn-stop   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
.btn-action:hover { opacity: .8; }

/* Modal */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center; z-index: 9999;
}
.modal-box {
    background: #fff; border-radius: 10px; padding: 28px 32px;
    max-width: 420px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,.2);
}
.modal-title  { font-size: 1rem; font-weight: 700; color: #1F4E79; margin: 0 0 10px; }
.modal-body   { font-size: .875rem; color: #4b5563; margin: 0 0 20px; line-height: 1.5; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-modal-cancel  { padding: 7px 18px; border-radius: 6px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: .875rem; font-weight: 500; cursor: pointer; }
.btn-modal-cancel:hover { background: #f9fafb; }
.btn-modal-confirm { padding: 7px 18px; border-radius: 6px; border: none; background: #dc2626; color: #fff; font-size: .875rem; font-weight: 600; cursor: pointer; }
.btn-modal-confirm:hover { background: #b91c1c; }
</style>
