<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { ArrowLeft, Edit3, LayoutGrid, Plus } from '@lucide/vue'

interface Category {
  id: number
  name: string
  description: string | null
  is_active: boolean
}

defineProps<{ categories: Category[] }>()
const page = usePage<{ flash?: { success?: string } }>()
</script>

<template>
  <div class="min-h-screen pb-8">
    <header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur">
      <div class="mx-auto flex max-w-3xl items-center justify-between">
        <div class="flex items-center gap-3">
          <Link :href="route('dashboard')" class="rounded-xl p-2 text-muted-foreground hover:bg-muted"><ArrowLeft class="size-5" /></Link>
          <div><p class="text-xs uppercase tracking-wider text-primary">Data Master</p><h1 class="text-xl font-bold">Kategori Barang</h1></div>
        </div>
        <Link :href="route('categories.create')" class="flex size-10 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg"><Plus class="size-5" /></Link>
      </div>
    </header>
    <main class="mx-auto max-w-3xl space-y-4 px-4 py-6">
      <div v-if="page.props.flash?.success" class="rounded-2xl bg-green-50 p-4 text-sm font-medium text-green-700">{{ page.props.flash.success }}</div>
      <div class="rounded-2xl border bg-card p-4"><p class="text-sm text-muted-foreground">Total kategori</p><p class="mt-1 text-3xl font-bold">{{ categories.length }}</p></div>
      <div v-if="categories.length" class="space-y-3">
        <article v-for="category in categories" :key="category.id" class="flex items-start justify-between gap-3 rounded-2xl border bg-card p-4 shadow-sm">
          <div class="flex min-w-0 gap-3"><div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"><LayoutGrid class="size-5" /></div><div class="min-w-0"><h2 class="font-semibold">{{ category.name }}</h2><p class="mt-1 text-sm text-muted-foreground">{{ category.description || 'Tidak ada deskripsi' }}</p><span class="mt-2 inline-flex rounded-full px-2 py-1 text-xs font-medium" :class="category.is_active ? 'bg-green-100 text-green-700' : 'bg-muted text-muted-foreground'">{{ category.is_active ? 'Aktif' : 'Tidak aktif' }}</span></div></div>
          <Link :href="route('categories.edit', category.id)" class="rounded-xl p-2 text-muted-foreground hover:bg-muted"><Edit3 class="size-4" /></Link>
        </article>
      </div>
      <div v-else class="rounded-2xl border border-dashed p-8 text-center text-sm text-muted-foreground">Belum ada kategori.</div>
    </main>
  </div>
</template>
