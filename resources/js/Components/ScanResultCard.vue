<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    scan    : { type: Object, required: true },  // formatScan(includeFindings=true)
    fkButir : { type: Array,  default: () => [] },
    planId  : { type: Number, required: true },
})

const TOOL_STYLE = {
    curl    : { bg: '#FFF8E1', border: '#F9A825', label: 'cURL / Guzzle' },
    testssl : { bg: '#E3F2FD', border: '#1976D2', label: 'testssl.sh' },
    nmap    : { bg: '#f5f5f4', border: '#78716c', label: 'Nmap' },
    nikto   : { bg: '#FFEBEE', border: '#D32F2F', label: 'Nikto' },
    zap     : { bg: '#FFEBEE', border: '#B71C1C', label: 'OWASP ZAP' },
}

const SEV_COLOR = {
    Critical : '#7f1d1d',
    High     : '#991b1b',
    Medium   : '#92400e',
    Low      : '#1e3a5f',
    Info     : '#374151',
}
const SEV_BG = {
    Critical : '#fee2e2',
    High     : '#fee2e2',
    Medium   : '#fef3c7',
    Low      : '#dbeafe',
    Info     : '#f3f4f6',
}

const showRaw    = ref(false)
const tagSuccess = ref({})  // finding_index → butir_kode
const tagError   = ref({})
const selectedButir = ref({}) // finding_index → butir_id

const findings = props.scan.findings ?? []

const severityCounts = ['Critical', 'High', 'Medium', 'Low', 'Info'].map(sev => ({
    sev,
    count: findings.filter(f => f.severity === sev).length,
})).filter(x => x.count > 0)

const style = TOOL_STYLE[props.scan.tool] ?? { bg: '#f9fafb', border: '#9ca3af', label: props.scan.tool }

async function tagBukti(findingIndex) {
    const butirId = selectedButir.value[findingIndex]
    if (!butirId) { tagError.value[findingIndex] = 'Pilih butir terlebih dahulu.'; return }
    tagError.value[findingIndex] = null
    try {
        const { data } = await axios.post(`/auditor/scan/${props.scan.id}/tag-bukti`, {
            butir_id      : butirId,
            finding_index : findingIndex,
        })
        tagSuccess.value[findingIndex] = data.butir_kode ?? '✓'
    } catch (e) {
        tagError.value[findingIndex] = e?.response?.data?.error ?? 'Gagal menyimpan bukti.'
    }
}
</script>

<template>
    <div class="card" :style="{ borderLeftColor: style.border }">
        <!-- Header -->
        <div class="card-head">
            <span class="tool-badge" :style="{ background: style.bg, borderColor: style.border }">
                {{ style.label }}
            </span>
            <span class="scan-meta">{{ scan.target_url }} · {{ scan.scanned_at }}</span>
        </div>

        <!-- Severity summary -->
        <div class="sev-row">
            <span
                v-for="s in severityCounts"
                :key="s.sev"
                class="sev-chip"
                :style="{ background: SEV_BG[s.sev], color: SEV_COLOR[s.sev] }"
            >{{ s.sev }} {{ s.count }}</span>
            <span v-if="severityCounts.length === 0" class="sev-chip" style="background:#f3f4f6;color:#6b7280">Tidak ada temuan</span>
        </div>

        <!-- Error display -->
        <div v-if="scan.status === 'gagal' && scan.error_message" class="error-box">
            <strong>Error:</strong> {{ scan.error_message }}
        </div>

        <!-- Findings -->
        <div
            v-for="(finding, idx) in findings"
            :key="idx"
            class="scan-box"
            :style="{ borderLeftColor: style.border }"
        >
            <div class="finding-header">
                <span
                    class="sev-badge"
                    :style="{ background: SEV_BG[finding.severity], color: SEV_COLOR[finding.severity] }"
                >{{ finding.severity }}</span>
                <strong class="finding-title">{{ finding.title }}</strong>
            </div>

            <p class="finding-desc">{{ finding.description }}</p>

            <div v-if="finding.evidence" class="evidence-block">
                <div class="evidence-label">Evidence</div>
                <pre class="mono">{{ finding.evidence }}</pre>
            </div>

            <!-- Butir terkait -->
            <div v-if="finding.butir_id" class="butir-row">
                <span class="butir-chip">
                    {{ fkButir.find(b => b.id === finding.butir_id)?.kode ?? `Butir #${finding.butir_id}` }}
                </span>
            </div>

            <!-- Tag sebagai bukti -->
            <div class="tag-row">
                <select v-model="selectedButir[idx]" class="butir-select">
                    <option :value="undefined" disabled>— Pilih butir FK untuk dijadikan bukti —</option>
                    <option v-for="b in fkButir" :key="b.id" :value="b.id">
                        {{ b.kode }}: {{ b.judul_butir }}
                    </option>
                </select>
                <button
                    class="btn-tag"
                    :style="{ borderColor: style.border, color: style.border }"
                    @click="tagBukti(idx)"
                    :disabled="!!tagSuccess[idx]"
                >
                    <template v-if="tagSuccess[idx]">✓ Disimpan ke {{ tagSuccess[idx] }}</template>
                    <template v-else>Gunakan sebagai bukti EFK</template>
                </button>
                <span v-if="tagError[idx]" class="tag-error">{{ tagError[idx] }}</span>
            </div>
        </div>

        <!-- Raw output -->
        <div class="raw-toggle">
            <button class="btn-raw" @click="showRaw = !showRaw">
                {{ showRaw ? '▲ Sembunyikan' : '▼ Tampilkan' }} Raw Output
            </button>
            <div v-if="showRaw" class="raw-block">
                <pre class="mono">{{ scan.raw_output }}</pre>
            </div>
        </div>
    </div>
</template>

<style scoped>
.card { background: #fff; border-radius: 8px; border-left: 4px solid; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 16px; }
.card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.tool-badge { display: inline-block; padding: 4px 12px; border-radius: 6px; border: 1.5px solid; font-size: .875rem; font-weight: 600; }
.scan-meta  { font-size: .8rem; color: #6b7280; font-family: monospace; }

.sev-row    { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
.sev-chip   { padding: 2px 10px; border-radius: 999px; font-size: .75rem; font-weight: 700; }

.error-box  { background: #fee2e2; border-radius: 6px; padding: 12px; color: #991b1b; font-size: .875rem; margin-bottom: 16px; }

.scan-box   { border-left: 3px solid; background: #fafafa; border-radius: 6px; padding: 14px 16px; margin-bottom: 12px; }
.finding-header { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.sev-badge  { padding: 2px 8px; border-radius: 4px; font-size: .75rem; font-weight: 700; white-space: nowrap; }
.finding-title  { font-weight: 600; font-size: .9rem; }
.finding-desc   { color: #374151; font-size: .875rem; margin: 6px 0 10px; line-height: 1.5; white-space: pre-wrap; }

.evidence-block { background: #f1f5f9; border-radius: 4px; padding: 8px 12px; margin-bottom: 10px; }
.evidence-label { font-size: .75rem; font-weight: 600; color: #6b7280; margin-bottom: 4px; }
.mono { font-family: 'Courier New', monospace; font-size: .8rem; white-space: pre-wrap; word-break: break-all; margin: 0; color: #1e293b; }

.butir-row  { margin-bottom: 8px; }
.butir-chip { display: inline-block; padding: 2px 8px; background: #e0f2fe; color: #0c4a6e; border-radius: 4px; font-size: .75rem; font-weight: 600; }

.tag-row    { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.butir-select { flex: 1; min-width: 200px; padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 6px; font-size: .8rem; }
.btn-tag    { padding: 5px 14px; border-radius: 6px; border: 1.5px solid; background: transparent; cursor: pointer; font-size: .8rem; font-weight: 500; white-space: nowrap; }
.btn-tag:disabled { opacity: .7; cursor: default; }
.tag-error  { font-size: .8rem; color: #991b1b; }

.raw-toggle { margin-top: 16px; }
.btn-raw    { background: none; border: 1px solid #d1d5db; border-radius: 6px; padding: 5px 12px; cursor: pointer; font-size: .8rem; color: #6b7280; }
.btn-raw:hover { background: #f9fafb; }
.raw-block  { margin-top: 8px; background: #111827; border-radius: 6px; padding: 12px 14px; max-height: 400px; overflow: auto; }
.raw-block .mono { color: #e5e7eb; }
</style>
