<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Save } from '@lucide/vue'

interface Option { id: number; name: string; level?: number | null }
interface ProfileUser { name: string; email: string; tanggal_masuk: string | null; position_id: number | null; department_ids: number[]; factory_ids: number[] }

const props = defineProps<{ user: ProfileUser; positions: Option[]; departments: Option[]; factories: Option[] }>()
const form = useForm({
  position_id: props.user.position_id ?? '',
  department_ids: [...props.user.department_ids],
  factory_ids: [...props.user.factory_ids],
})

function submit(): void {
  form.put(route('profile.update'))
}
</script>

<template>
  <div class="min-h-screen pb-8">
    <header class="sticky top-0 z-10 border-b bg-white/95 px-4 py-4 backdrop-blur">
      <div class="mx-auto flex max-w-3xl items-center gap-3">
        <Link :href="route('dashboard')" class="rounded-xl p-2 text-muted-foreground hover:bg-muted"><ArrowLeft class="size-5" /></Link>
        <div><p class="text-xs uppercase tracking-wider text-primary">Akun</p><h1 class="text-xl font-bold">Profil Organisasi</h1></div>
      </div>
    </header>
    <main class="mx-auto max-w-3xl space-y-5 px-4 py-6">
      <section class="rounded-3xl border bg-card p-5 shadow-sm">
        <p class="text-sm text-muted-foreground">Nama</p><p class="font-semibold">{{ user.name }}</p>
        <p class="mt-4 text-sm text-muted-foreground">Email</p><p class="font-semibold">{{ user.email }}</p>
         <p class="mt-4 text-sm text-muted-foreground">Tanggal masuk</p><p class="font-semibold">{{ user.tanggal_masuk ?? 'Belum tersedia' }}</p>
        <p class="mt-3 text-xs text-muted-foreground">Data identitas berasal dari sekali_login.</p>
      </section>
      <form class="space-y-5" @submit.prevent="submit">
        <section class="space-y-4 rounded-3xl border bg-card p-5 shadow-sm">
          <div><label for="position_id" class="mb-2 block text-sm font-medium">Jabatan utama</label><select id="position_id" v-model="form.position_id" class="w-full rounded-xl border bg-background px-4 py-3"><option value="">Pilih jabatan</option><option v-for="position in positions" :key="position.id" :value="position.id">{{ position.name }}</option></select><p v-if="form.errors.position_id" class="mt-1 text-sm text-red-600">{{ form.errors.position_id }}</p></div>
          <div><p class="mb-2 block text-sm font-medium">Departemen</p><div class="grid gap-2 sm:grid-cols-2"><label v-for="department in departments" :key="department.id" class="flex items-center gap-3 rounded-xl border px-3 py-3 text-sm"><input v-model="form.department_ids" type="checkbox" :value="department.id" class="size-4 accent-primary">{{ department.name }}</label></div><p v-if="form.errors.department_ids" class="mt-1 text-sm text-red-600">{{ form.errors.department_ids }}</p></div>
          <div><p class="mb-2 block text-sm font-medium">Pabrik</p><div class="grid gap-2 sm:grid-cols-3"><label v-for="factory in factories" :key="factory.id" class="flex items-center gap-3 rounded-xl border px-3 py-3 text-sm"><input v-model="form.factory_ids" type="checkbox" :value="factory.id" class="size-4 accent-primary">{{ factory.name }}</label></div><p v-if="form.errors.factory_ids" class="mt-1 text-sm text-red-600">{{ form.errors.factory_ids }}</p></div>
        </section>
        <button type="submit" :disabled="form.processing" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground disabled:opacity-60"><Save class="size-4" />{{ form.processing ? 'Menyimpan...' : 'Simpan profil' }}</button>
      </form>
    </main>
  </div>
</template>
