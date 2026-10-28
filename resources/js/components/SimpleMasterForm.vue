<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Save } from '@lucide/vue'

interface Item { id: number; name: string; is_active: boolean }
const props = withDefaults(defineProps<{ item?: Item; resource: string; title: string }>(), { item: undefined })
const editing = Boolean(props.item)
const form = useForm({ name: props.item?.name ?? '', is_active: props.item?.is_active ?? true })
function uppercase(): void { form.name = form.name.toUpperCase() }
function submit(): void { if (editing && props.item) form.put(route(`${props.resource}.update`, props.item.id)); else form.post(route(`${props.resource}.store`)) }
</script>
<template><div class="min-h-screen"><header class="border-b bg-white/95 px-4 py-4"><div class="mx-auto flex max-w-3xl items-center gap-3"><Link :href="route(`${resource}.index`)" class="rounded-xl p-2 text-muted-foreground hover:bg-muted"><ArrowLeft class="size-5" /></Link><div><p class="text-xs uppercase tracking-wider text-primary">Data Master</p><h1 class="text-xl font-bold">{{ editing ? 'Edit' : 'Tambah' }} {{ title }}</h1></div></div></header><main class="mx-auto max-w-3xl px-4 py-6"><form class="space-y-5 rounded-3xl border bg-card p-5 shadow-sm" @submit.prevent="submit"><div><label for="name" class="mb-2 block text-sm font-medium">Nama {{ title.toLowerCase() }}</label><input id="name" v-model="form.name" class="w-full rounded-xl border bg-background px-4 py-3 outline-none ring-primary focus:ring-2" @input="uppercase"><p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p></div><label class="flex items-center gap-3 rounded-xl border p-3"><input v-model="form.is_active" type="checkbox" class="size-5 accent-primary"><span class="text-sm">{{ title }} aktif dan dapat dipilih saat input aset</span></label><button type="submit" :disabled="form.processing" class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground disabled:opacity-60"><Save class="size-4" />{{ form.processing ? 'Menyimpan...' : `Simpan ${title.toLowerCase()}` }}</button></form></main></div></template>
