<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Plus, Save, Search, Trash2 } from '@lucide/vue'

interface Option { id: number; name: string }
interface User { id: number; name: string; nik: string | null; departments: Option[]; factories: Option[] }
interface ItemType { id: number; name: string; unit: string; category: { name: string } | null }

const props = defineProps<{ departments: Option[]; factories: Option[]; users: User[] }>()
const departmentId = ref('')
const factoryId = ref('')
const itemSearches = ref<string[]>([''])
const itemResults = ref<ItemType[][]>([[]])
const itemLoading = ref<boolean[]>([false])
const selectedItemType = ref<boolean[]>([false])
const itemSearchTimeouts: Record<number, ReturnType<typeof setTimeout>> = {}
const form = useForm({ type: 'PENYERAHAN', receiver_ids: [] as number[], transaction_date: new Date().toISOString().slice(0, 10), purpose: '', notes: '', items: [{ item_type_id: '', manual_name: '', manual_unit: '', quantity: 1, condition: 'BAIK', notes: '' }] })

const visibleUsers = computed(() => props.users.filter((user) => (!departmentId.value || user.departments.some((department) => department.id === Number(departmentId.value))) && (!factoryId.value || user.factories.some((factory) => factory.id === Number(factoryId.value)))))
const allVisibleSelected = computed(() => visibleUsers.value.length > 0 && visibleUsers.value.every((user) => form.receiver_ids.includes(user.id)))

function handleItemSearch(index: number): void { const search = itemSearches.value[index]; selectedItemType.value[index] = false; if (itemSearchTimeouts[index]) clearTimeout(itemSearchTimeouts[index]); itemResults.value[index] = []; form.items[index].item_type_id = ''; if (search.trim().length < 2) return; itemSearchTimeouts[index] = setTimeout(() => searchItemTypes(search.trim(), index), 300) }
async function searchItemTypes(search: string, index: number): Promise<void> { itemLoading.value[index] = true; try { const response = await fetch(`${route('handovers.items.search')}?search=${encodeURIComponent(search)}`, { headers: { Accept: 'application/json' } }); if (response.ok) itemResults.value[index] = (await response.json()).itemTypes } finally { itemLoading.value[index] = false } }
function selectItemType(item: ItemType, index: number): void { selectedItemType.value[index] = true; form.items[index].item_type_id = String(item.id); form.items[index].manual_name = ''; form.items[index].manual_unit = ''; itemSearches.value[index] = `${item.name} — ${item.category?.name ?? 'Tanpa kategori'} (${item.unit})`; itemResults.value[index] = [] }
function toggleUser(id: number): void {
  form.receiver_ids = form.receiver_ids.includes(id) ? form.receiver_ids.filter((userId) => userId !== id) : [...form.receiver_ids, id]
}
function toggleAllVisible(): void {
  if (allVisibleSelected.value) {
    form.receiver_ids = form.receiver_ids.filter((id) => !visibleUsers.value.some((user) => user.id === id))
    return
  }
  form.receiver_ids = [...new Set([...form.receiver_ids, ...visibleUsers.value.map((user) => user.id)])]
}
function addItem(): void { form.items.push({ item_type_id: '', manual_name: '', manual_unit: '', quantity: 1, condition: 'BAIK', notes: '' }); itemSearches.value.push(''); itemResults.value.push([]); itemLoading.value.push(false); selectedItemType.value.push(false) }
function removeItem(index: number): void { if (form.items.length > 1) { form.items.splice(index, 1); itemSearches.value.splice(index, 1); itemResults.value.splice(index, 1); itemLoading.value.splice(index, 1); selectedItemType.value.splice(index, 1) } }
function submit(): void { form.post(route('handovers.batch.store')) }
</script>

<template>
  <div class="min-h-screen pb-8">
    <header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur"><div class="mx-auto flex max-w-3xl items-center gap-3"><Link :href="route('handovers.index')" class="rounded-xl p-2 text-muted-foreground"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Batch Serah Terima</p><h1 class="text-xl font-bold">Buat Batch Serah Terima</h1></div></div></header>
    <main class="mx-auto max-w-3xl px-4 py-6">
      <form class="space-y-5" @submit.prevent="submit">
        <section class="space-y-4 rounded-3xl border bg-card p-5">
          <div><label class="mb-2 block text-sm font-medium">Jenis transaksi</label><select v-model="form.type" class="w-full rounded-xl border bg-background px-4 py-3"><option>PENYERAHAN</option><option>PENGEMBALIAN</option><option>PEMINJAMAN</option><option>PENGGANTIAN</option><option>MUTASI</option></select><p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p></div>
          <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-2 block text-sm font-medium">Filter departemen</label><select v-model="departmentId" class="w-full rounded-xl border bg-background px-4 py-3"><option value="">Semua departemen</option><option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option></select></div><div><label class="mb-2 block text-sm font-medium">Filter pabrik</label><select v-model="factoryId" class="w-full rounded-xl border bg-background px-4 py-3"><option value="">Semua pabrik</option><option v-for="factory in factories" :key="factory.id" :value="factory.id">{{ factory.name }}</option></select></div></div>
          <div><div class="mb-2 flex items-center justify-between gap-3"><label class="text-sm font-medium">Penerima aktif</label><button type="button" class="text-sm font-semibold text-primary" @click="toggleAllVisible">{{ allVisibleSelected ? 'Batalkan semua' : 'Pilih semua yang tampil' }}</button></div><div class="max-h-72 space-y-2 overflow-y-auto rounded-xl border p-3"><label v-for="user in visibleUsers" :key="user.id" class="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 hover:bg-muted"><input type="checkbox" :checked="form.receiver_ids.includes(user.id)" class="size-4 rounded" @change="toggleUser(user.id)"><span class="text-sm">{{ user.name }}<span v-if="user.nik" class="text-muted-foreground"> — {{ user.nik }}</span></span></label><p v-if="!visibleUsers.length" class="p-3 text-sm text-muted-foreground">Tidak ada user aktif yang sesuai filter.</p></div><p class="mt-2 text-sm text-muted-foreground">{{ form.receiver_ids.length }} penerima dipilih</p><p v-if="form.errors.receiver_ids" class="mt-1 text-sm text-red-600">{{ form.errors.receiver_ids }}</p></div>
          <div class="grid gap-4 sm:grid-cols-2"><div><label class="mb-2 block text-sm font-medium">Tanggal transaksi</label><input v-model="form.transaction_date" type="date" class="w-full rounded-xl border bg-background px-4 py-3"><p v-if="form.errors.transaction_date" class="mt-1 text-sm text-red-600">{{ form.errors.transaction_date }}</p></div><div><label class="mb-2 block text-sm font-medium">Ringkasan</label><div class="rounded-xl bg-muted px-4 py-3 text-sm">{{ form.receiver_ids.length }} transaksi × {{ form.items.length }} item</div></div></div>
          <div><label class="mb-2 block text-sm font-medium">Keperluan <span class="font-normal text-muted-foreground">(opsional)</span></label><textarea v-model="form.purpose" rows="2" class="w-full rounded-xl border bg-background px-4 py-3"></textarea></div><div><label class="mb-2 block text-sm font-medium">Catatan <span class="font-normal text-muted-foreground">(opsional)</span></label><textarea v-model="form.notes" rows="2" class="w-full rounded-xl border bg-background px-4 py-3"></textarea></div>
        </section>
        <section class="space-y-4 rounded-3xl border bg-card p-5"><div class="flex items-center justify-between"><h2 class="font-semibold">Item yang sama untuk semua penerima</h2><button type="button" class="flex items-center gap-1 text-sm font-semibold text-primary" @click="addItem"><Plus class="size-4" />Tambah item</button></div><div v-for="(item, index) in form.items" :key="index" class="grid min-w-0 gap-3 rounded-2xl bg-muted/50 p-3 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_7rem_7rem_8rem_auto]"><div class="relative min-w-0"><Search class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" /><input v-model="itemSearches[index]" type="search" @input="handleItemSearch(index)" placeholder="Cari barang" class="min-w-0 w-full rounded-xl border bg-background py-2 pl-9 pr-3" autocomplete="off"><span v-if="itemLoading[index]" class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">...</span><div v-if="itemSearches[index].trim().length >= 2 && !item.item_type_id && !selectedItemType[index]" class="absolute inset-x-0 top-full z-20 mt-1 overflow-hidden rounded-xl border bg-background shadow-lg"><p v-if="!itemLoading[index] && !itemResults[index].length" class="p-3 text-sm text-muted-foreground">Barang tidak ditemukan.</p><button v-for="itemType in itemResults[index]" :key="itemType.id" type="button" class="block w-full border-b px-3 py-2 text-left last:border-0 hover:bg-muted" @click="selectItemType(itemType, index)"><span class="block text-sm font-medium">{{ itemType.name }}</span><span class="block text-xs text-muted-foreground">{{ itemType.category?.name ?? 'Tanpa kategori' }} · {{ itemType.unit }}</span></button></div></div><input v-model="item.manual_name" type="text" placeholder="Atau nama barang manual" class="min-w-0 rounded-xl border bg-background px-3 py-2"><input v-model="item.manual_unit" type="text" placeholder="Satuan" class="min-w-0 rounded-xl border bg-background px-3 py-2"><input v-model="item.quantity" type="number" min="1" class="min-w-0 rounded-xl border bg-background px-3 py-2"><select v-model="item.condition" class="min-w-0 rounded-xl border bg-background px-3 py-2"><option>BAIK</option><option>RUSAK_RINGAN</option><option>RUSAK_BERAT</option></select><button type="button" class="flex items-center justify-center rounded-xl p-2 text-red-600 disabled:opacity-40" :disabled="form.items.length === 1" @click="removeItem(index)"><Trash2 class="size-4" /></button></div><p v-if="form.errors.items" class="text-sm text-red-600">{{ form.errors.items }}</p><p v-if="Object.entries(form.errors).some(([key]) => key.startsWith('items.'))" class="text-sm text-red-600">Setiap item harus memilih barang master atau mengisi nama barang manual.</p></section>
        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground disabled:opacity-50" :disabled="form.processing"><Save class="size-4" />Simpan {{ form.receiver_ids.length }} transaksi batch</button>
      </form>
    </main>
  </div>
</template>
