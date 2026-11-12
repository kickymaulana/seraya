<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ArrowLeft, Check, ShieldCheck, X } from '@lucide/vue'

interface Item { id: number; quantity: number; item_type: { name: string; unit: string } | null; manual_name: string | null; manual_unit: string | null }
interface Handover { id: number; number: string; receiver: { name: string }; items: Item[] }
interface Batch { id: number; number: string; transaction_date: string; purpose: string | null; notes: string | null; sender: { name: string }; handovers: Handover[] }
interface Pagination { data: Batch[] }

const props = defineProps<{ batches: Pagination }>()
const page = usePage<{ flash?: { success?: string } }>()
const reasons = ref<Record<number, string>>({})
const rejectedItemIds = ref<number[]>([])
const selectedBatchId = ref<number | null>(null)
const selectedBatch = computed(() => props.batches.data.find((batch) => batch.id === selectedBatchId.value) ?? null)

function toggleRejected(itemId: number): void {
  rejectedItemIds.value = rejectedItemIds.value.includes(itemId)
    ? rejectedItemIds.value.filter((id) => id !== itemId)
    : [...rejectedItemIds.value, itemId]
}

function isRejected(itemId: number): boolean {
  return rejectedItemIds.value.includes(itemId)
}

function approve(batch: Batch): void {
  const rejectedItems = rejectedItemIds.value.map((id) => ({ id, reason: reasons.value[id] ?? '' }))
  router.put(route('handovers.approvals.update', batch.id), { rejected_items: rejectedItems }, {
    onSuccess: () => {
      selectedBatchId.value = null
      rejectedItemIds.value = []
      reasons.value = {}
    },
  })
}
</script>

<template>
  <div class="min-h-screen pb-8">
    <header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur"><div class="mx-auto flex max-w-4xl items-center gap-3"><Link :href="route('handovers.index')" class="rounded-xl p-2 text-muted-foreground"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Persetujuan HSE</p><h1 class="text-xl font-bold">Batch APD Menunggu Persetujuan</h1></div></div></header>
    <main class="mx-auto max-w-4xl space-y-4 px-4 py-6">
      <div v-if="page.props.flash?.success" class="rounded-2xl bg-green-50 p-4 text-sm font-medium text-green-700">{{ page.props.flash.success }}</div>
      <article v-for="batch in props.batches.data" :key="batch.id" class="rounded-3xl border bg-card p-5 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-semibold text-primary">{{ batch.number }}</p><h2 class="mt-1 text-lg font-bold">{{ batch.handovers.length }} penerima · {{ batch.sender.name }}</h2><p class="mt-1 text-sm text-muted-foreground">{{ batch.transaction_date }}<span v-if="batch.purpose"> · {{ batch.purpose }}</span></p></div><button type="button" class="flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground" @click="selectedBatchId = selectedBatchId === batch.id ? null : batch.id"><ShieldCheck class="size-4" />{{ selectedBatchId === batch.id ? 'Tutup Detail' : 'Tinjau Batch' }}</button></div>
        <div v-if="selectedBatchId === batch.id" class="mt-5 space-y-4 border-t pt-5">
          <p class="text-sm text-muted-foreground">Secara default seluruh item disetujui. Pilih hanya item yang perlu ditolak, lalu tuliskan alasannya.</p>
          <section v-for="handover in batch.handovers" :key="handover.id" class="rounded-2xl bg-muted/50 p-4"><h3 class="font-semibold">{{ handover.receiver.name }}</h3><div class="mt-3 space-y-3"><div v-for="item in handover.items" :key="item.id" class="rounded-xl border bg-background p-3"><div class="flex flex-wrap items-center justify-between gap-3"><p class="text-sm font-medium">{{ item.item_type?.name ?? item.manual_name }} × {{ item.quantity }} {{ item.item_type?.unit ?? item.manual_unit ?? '' }}</p><button type="button" class="flex items-center gap-1 rounded-lg border px-3 py-2 text-xs font-semibold" :class="isRejected(item.id) ? 'border-red-300 bg-red-50 text-red-700' : 'text-muted-foreground'" @click="toggleRejected(item.id)"><X class="size-3" />{{ isRejected(item.id) ? 'Batalkan Tolak' : 'Tolak Item' }}</button></div><textarea v-if="isRejected(item.id)" v-model="reasons[item.id]" rows="2" placeholder="Alasan penolakan wajib diisi" class="mt-3 w-full rounded-xl border bg-background px-3 py-2 text-sm" /></div></div></section>
          <button type="button" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground" @click="approve(batch)"><Check class="size-4" />{{ rejectedItemIds.length ? 'Simpan Persetujuan dan Penolakan' : 'Setujui Semua Item' }}</button>
        </div>
      </article>
      <div v-if="!props.batches.data.length" class="rounded-2xl border border-dashed p-8 text-center text-sm text-muted-foreground">Tidak ada batch APD yang menunggu persetujuan.</div>
    </main>
  </div>
</template>
