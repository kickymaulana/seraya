<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, ClipboardCheck, Layers3, Monitor, Package, Users } from '@lucide/vue'

interface Counts { categories: number; itemTypes: number; departments: number; positions: number; users: number }
defineProps<{ counts: Counts }>()
const masters = [
  { key: 'categories', title: 'Kategori Barang', description: 'Kelompok barang serah terima', route: 'categories.index', icon: Layers3 },
  { key: 'itemTypes', title: 'Jenis Barang', description: 'Barang yang dapat diserahterimakan', route: 'item-types.index', icon: Package },
  { key: 'departments', title: 'Departemen', description: 'Departemen organisasi', route: 'departments.index', icon: Monitor },
  { key: 'positions', title: 'Jabatan', description: 'Jabatan organisasi', route: 'positions.index', icon: ClipboardCheck },
  { key: 'users', title: 'User', description: 'Kelola akses dan organisasi user', route: 'users.index', icon: Users },
] as const
</script>
<template><div class="min-h-screen pb-8"><header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur"><div class="mx-auto flex max-w-3xl items-center gap-3"><Link :href="route('dashboard')" class="rounded-xl p-2 text-muted-foreground hover:bg-muted"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Pengaturan Data</p><h1 class="text-xl font-bold">Master Data</h1></div></div></header><main class="mx-auto max-w-3xl space-y-5 px-4 py-6"><section class="rounded-3xl bg-primary p-5 text-primary-foreground shadow-lg shadow-primary/15"><p class="text-sm opacity-80">Admin Area</p><h2 class="mt-1 text-2xl font-bold">Kelola data master Seraya</h2><p class="mt-2 text-sm opacity-80">Data master aktif akan muncul sebagai pilihan saat mencatat serah terima.</p></section><section class="space-y-3"><Link v-for="master in masters" :key="master.key" :href="route(master.route)" class="flex items-center gap-4 rounded-2xl border bg-card p-4 shadow-sm transition hover:border-primary/40 hover:shadow-md"><div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-primary/10 text-primary"><component :is="master.icon" class="size-6" /></div><div class="min-w-0 flex-1"><h2 class="font-semibold">{{ master.title }}</h2><p class="mt-1 text-sm text-muted-foreground">{{ master.description }}</p></div><div class="text-right"><p class="text-2xl font-bold">{{ counts[master.key] }}</p><p class="text-xs text-muted-foreground">data</p></div></Link></section></main></div></template>
