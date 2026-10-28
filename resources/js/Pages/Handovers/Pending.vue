<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { ArrowLeft, Check, PackageCheck } from '@lucide/vue'

interface Handover {
  id: number
  number: string
  type: string
  transaction_date: string
  sender: { name: string }
  items: { item_type: { name: string; unit: string } | null; manual_name: string | null; manual_unit: string | null; quantity: number }[]
}
interface Pagination { data: Handover[] }

const props = defineProps<{ handovers: Pagination }>()
const page = usePage<{ flash?: { success?: string } }>()
function confirmReceipt(id: number): void { router.post(route('handovers.confirm-receiver', id)) }
</script>

<template>
  <div class="min-h-screen pb-8">
    <header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur"><div class="mx-auto flex max-w-3xl items-center gap-3"><Link :href="route('handovers.index')" class="rounded-xl p-2 text-muted-foreground"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Konfirmasi Saya</p><h1 class="text-xl font-bold">Menunggu Penerimaan</h1></div></div></header>
    <main class="mx-auto max-w-3xl space-y-4 px-4 py-6">
      <div v-if="page.props.flash?.success" class="rounded-2xl bg-green-50 p-4 text-sm font-medium text-green-700">{{ page.props.flash.success }}</div>
      <div v-if="props.handovers.data.length" class="space-y-3">
        <article v-for="handover in props.handovers.data" :key="handover.id" class="rounded-2xl border bg-card p-4 shadow-sm">
          <div class="flex items-start gap-3"><div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><PackageCheck class="size-5" /></div><div class="min-w-0 flex-1"><p class="text-xs font-semibold text-primary">{{ handover.number }}</p><h2 class="mt-1 font-semibold">{{ handover.type }}</h2><p class="mt-1 text-sm text-muted-foreground">Diserahkan oleh {{ handover.sender.name }}</p><p class="mt-1 text-xs text-muted-foreground">{{ handover.transaction_date }}</p></div></div>
          <div class="mt-4 flex flex-wrap gap-2"><span v-for="item in handover.items" :key="`${item.item_type?.name ?? item.manual_name}-${item.quantity}`" class="rounded-full bg-muted px-2 py-1 text-xs">{{ item.item_type?.name ?? item.manual_name }} × {{ item.quantity }} {{ item.item_type?.unit ?? item.manual_unit ?? '' }}</span></div>
          <button type="button" class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground" @click="confirmReceipt(handover.id)"><Check class="size-4" />Saya Sudah Menerima</button>
        </article>
      </div>
      <div v-else class="rounded-2xl border border-dashed p-8 text-center text-sm text-muted-foreground">Tidak ada transaksi yang menunggu konfirmasi.</div>
    </main>
  </div>
</template>
