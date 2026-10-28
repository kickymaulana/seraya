<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Save } from '@lucide/vue'

interface CategoryForm {
  name: string
  description: string
  is_active: boolean
}

const props = withDefaults(defineProps<{ category?: { id: number; name: string; description: string | null; is_active: boolean } }>(), { category: undefined })
const editing = Boolean(props.category)
const form = useForm<CategoryForm>({ name: props.category?.name ?? '', description: props.category?.description ?? '', is_active: props.category?.is_active ?? true })

function uppercase(field: 'name' | 'description'): void {
  form[field] = form[field].toUpperCase()
}

function submit(): void {
  if (editing && props.category) form.put(route('categories.update', props.category.id))
  else form.post(route('categories.store'))
}
</script>

<template>
  <div class="min-h-screen">
    <header class="border-b bg-white/95 px-4 py-4">
      <div class="mx-auto flex max-w-3xl items-center gap-3"><Link :href="route('categories.index')" class="rounded-xl p-2 text-muted-foreground hover:bg-muted"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Data Master</p><h1 class="text-xl font-bold">{{ editing ? 'Edit Kategori' : 'Tambah Kategori' }}</h1></div></div>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-6"><form class="space-y-5 rounded-3xl border bg-card p-5 shadow-sm" @submit.prevent="submit"><div><label for="name" class="mb-2 block text-sm font-medium">Nama kategori</label><input id="name" v-model="form.name" class="w-full rounded-xl border bg-background px-4 py-3 outline-none ring-primary focus:ring-2" placeholder="Contoh: PC RAKITAN" @input="uppercase('name')"><p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p></div><div><label for="description" class="mb-2 block text-sm font-medium">Deskripsi <span class="font-normal text-muted-foreground">(opsional)</span></label><textarea id="description" v-model="form.description" rows="4" class="w-full rounded-xl border bg-background px-4 py-3 outline-none ring-primary focus:ring-2" placeholder="Keterangan kategori" @input="uppercase('description')"></textarea><p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p></div><label class="flex items-center gap-3 rounded-xl border p-3"><input v-model="form.is_active" type="checkbox" class="size-5 accent-primary"><span class="text-sm">Kategori aktif dan dapat dipilih saat input jenis barang</span></label><button type="submit" :disabled="form.processing" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground disabled:opacity-60"><Save class="size-4" />{{ form.processing ? 'Menyimpan...' : 'Simpan kategori' }}</button></form></main>
  </div>
</template>
