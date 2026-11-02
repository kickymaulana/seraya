<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, Edit3, X } from '@lucide/vue'
import { ref } from 'vue'

interface User { id: number; nik: string | null; name: string; email: string; avatar_url: string | null; active: boolean; tanggal_masuk: string | null; role: string; position: { name: string } | null; departments: { name: string }[]; factories: { name: string }[] }
const props = defineProps<{ users: User[] }>()
const selectedUser = ref<User | null>(null)
function initials(name: string): string { return name.split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() }
function openAvatar(user: User): void { if (user.avatar_url) selectedUser.value = user }
function closeAvatar(): void { selectedUser.value = null }
</script>
<template>
  <div class="min-h-screen pb-8">
    <header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur"><div class="mx-auto flex max-w-6xl items-center gap-3"><Link :href="route('master.index')" class="rounded-xl p-2 text-muted-foreground hover:bg-muted"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Data Master</p><h1 class="text-xl font-bold">Manajemen User</h1></div></div></header>
    <main class="mx-auto max-w-6xl space-y-5 px-4 py-6">
      <section class="rounded-3xl bg-primary p-5 text-primary-foreground shadow-lg shadow-primary/15"><p class="text-sm opacity-80">{{ users.length }} user terdaftar</p><h2 class="mt-1 text-2xl font-bold">Kelola akses user</h2><p class="mt-2 text-sm opacity-80">Identitas user berasal dari SSO dan HRD. Admin dapat mengatur akses serta data organisasi.</p></section>
      <section class="overflow-hidden rounded-3xl border bg-card shadow-sm"><div class="overflow-x-auto"><table class="w-full min-w-[1000px] text-left text-sm"><thead class="bg-muted"><tr><th class="px-4 py-3">User</th><th class="px-4 py-3">NIK</th><th class="px-4 py-3">Tanggal masuk</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr></thead><tbody class="divide-y"><tr v-for="user in users" :key="user.id"><td class="px-4 py-3"><div class="flex items-center gap-3"><button type="button" class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary/10 font-semibold text-primary" :class="user.avatar_url ? 'cursor-zoom-in' : 'cursor-default'" @click="openAvatar(user)"><img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" class="size-full object-cover"><span v-else>{{ initials(user.name) }}</span></button><div><p class="font-semibold">{{ user.name }}</p><p class="text-xs text-muted-foreground">{{ user.email }}</p></div></div></td><td class="px-4 py-3">{{ user.nik ?? '-' }}</td><td class="px-4 py-3">{{ user.tanggal_masuk ?? '-' }}</td><td class="px-4 py-3"><span class="rounded-full bg-primary/10 px-2 py-1 text-xs font-medium capitalize text-primary">{{ user.role }}</span></td><td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-medium" :class="user.active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">{{ user.active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="px-4 py-3 text-right"><Link :href="route('users.edit', user.id)" class="inline-flex rounded-xl p-2 text-muted-foreground hover:bg-muted"><Edit3 class="size-4" /></Link></td></tr><tr v-if="!users.length"><td colspan="6" class="px-4 py-10 text-center text-muted-foreground">Belum ada user.</td></tr></tbody></table></div></section>
    </main>
    <div v-if="selectedUser" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="closeAvatar"><div class="relative max-h-[90vh] max-w-2xl rounded-3xl bg-card p-3 shadow-2xl"><button type="button" class="absolute right-4 top-4 rounded-full bg-black/60 p-2 text-white" aria-label="Tutup foto" @click="closeAvatar"><X class="size-5" /></button><img :src="selectedUser.avatar_url ?? ''" :alt="selectedUser.name" class="max-h-[80vh] max-w-full rounded-2xl object-contain"><p class="px-2 pb-1 pt-3 text-center font-semibold">{{ selectedUser.name }}</p></div></div>
  </div>
</template>
