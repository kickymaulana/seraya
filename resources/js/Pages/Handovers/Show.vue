<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { ArrowLeft, Check, Eye, PackageCheck } from '@lucide/vue'

interface Person { id: number; name: string; nik: string | null }
interface Item { quantity: number; condition: string; notes: string | null; manual_name: string | null; manual_unit: string | null; item_type: { name: string; unit: string } | null }
interface Handover { id: number; number: string; type: string; status: string; transaction_date: string; purpose: string | null; notes: string | null; created_at: string; sender_confirmed_at: string | null; receiver_confirmed_at: string | null; sender: Person; receiver: Person; items: Item[] }
interface TimelineEntry { label: string; timestamp: string | null; completed: boolean }

const props = defineProps<{ handover: Handover; timeline: TimelineEntry[] }>()
const page = usePage<{ flash?: { success?: string }; auth: { user: { id: number; role: string } } }>()
const userId = page.props.auth.user.id
const canConfirmSender = userId === props.handover.sender.id && props.handover.status === 'MENUNGGU_KONFIRMASI'
const canConfirmReceiver = userId === props.handover.receiver.id && props.handover.status === 'MENUNGGU_KONFIRMASI_PENERIMA'

function confirmSender(): void { router.post(route('handovers.confirm-sender', props.handover.id)) }
function confirmReceiver(): void { router.post(route('handovers.confirm-receiver', props.handover.id)) }
function formatTimestamp(timestamp: string | null): string { return timestamp ? new Date(timestamp).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : 'Menunggu' }
</script>

<template>
  <div class="min-h-screen pb-8">
    <header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur"><div class="mx-auto flex max-w-3xl items-center justify-between gap-3"><div class="flex items-center gap-3"><Link :href="route('handovers.index')" class="rounded-xl p-2 text-muted-foreground"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Detail Serah Terima</p><h1 class="text-xl font-bold">{{ handover.number }}</h1></div></div><a :href="route('handovers.pdf', handover.id)" class="flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-semibold text-primary"><Eye class="size-4" />Lihat PDF</a></div></header>
    <main class="mx-auto max-w-3xl space-y-4 px-4 py-6">
      <div v-if="page.props.flash?.success" class="rounded-2xl bg-green-50 p-4 text-sm font-medium text-green-700">{{ page.props.flash.success }}</div>
      <section class="rounded-3xl bg-primary p-5 text-primary-foreground"><div class="flex items-start justify-between gap-3"><div><p class="text-sm opacity-80">{{ handover.type }}</p><h2 class="mt-1 text-2xl font-bold">{{ handover.status }}</h2></div><PackageCheck class="size-8 opacity-80" /></div><p class="mt-3 text-sm opacity-90">Tanggal transaksi: {{ handover.transaction_date }}</p></section>
      <section class="rounded-2xl border bg-card p-5"><h2 class="font-semibold">Timeline Status</h2><div class="mt-5 space-y-5"><div v-for="(entry, index) in timeline" :key="entry.label" class="relative flex gap-3"><div v-if="index < timeline.length - 1" class="absolute left-3 top-7 h-full w-px bg-border" /><div class="relative flex size-6 shrink-0 items-center justify-center rounded-full" :class="entry.completed ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"><Check v-if="entry.completed" class="size-4" /><span v-else class="size-2 rounded-full bg-current" /></div><div><p class="text-sm font-medium">{{ entry.label }}</p><p class="text-xs text-muted-foreground">{{ formatTimestamp(entry.timestamp) }}</p></div></div></div></section>
      <section class="grid gap-4 sm:grid-cols-2"><div class="rounded-2xl border bg-card p-5"><p class="text-xs uppercase tracking-wider text-muted-foreground">Pengirim</p><p class="mt-2 font-semibold">{{ handover.sender.name }}</p><p v-if="handover.sender.nik" class="text-sm text-muted-foreground">NIK {{ handover.sender.nik }}</p></div><div class="rounded-2xl border bg-card p-5"><p class="text-xs uppercase tracking-wider text-muted-foreground">Penerima</p><p class="mt-2 font-semibold">{{ handover.receiver.name }}</p><p v-if="handover.receiver.nik" class="text-sm text-muted-foreground">NIK {{ handover.receiver.nik }}</p></div></section>
      <section class="rounded-2xl border bg-card p-5"><h2 class="font-semibold">Detail Barang</h2><div class="mt-4 divide-y"><div v-for="item in handover.items" :key="`${item.item_type?.name ?? item.manual_name}-${item.quantity}`" class="flex flex-wrap justify-between gap-2 py-3 first:pt-0 last:pb-0"><div><p class="font-medium">{{ item.item_type?.name ?? item.manual_name }}</p><p class="text-xs text-muted-foreground">{{ item.condition }}<span v-if="item.notes"> · {{ item.notes }}</span></p></div><p class="text-sm font-semibold">{{ item.quantity }} {{ item.item_type?.unit ?? item.manual_unit ?? '' }}</p></div></div></section>
      <section v-if="handover.purpose || handover.notes" class="rounded-2xl border bg-card p-5"><h2 class="font-semibold">Catatan Transaksi</h2><p v-if="handover.purpose" class="mt-3 text-sm"><span class="font-medium">Keperluan:</span> {{ handover.purpose }}</p><p v-if="handover.notes" class="mt-2 whitespace-pre-line text-sm text-muted-foreground">{{ handover.notes }}</p></section>
      <div v-if="canConfirmSender || canConfirmReceiver" class="flex flex-col gap-2 sm:flex-row"><button v-if="canConfirmSender" type="button" class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground" @click="confirmSender"><Check class="size-4" />Konfirmasi Pengirim</button><button v-if="canConfirmReceiver" type="button" class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground" @click="confirmReceiver"><Check class="size-4" />Saya Sudah Menerima</button></div>
    </main>
  </div>
</template>
