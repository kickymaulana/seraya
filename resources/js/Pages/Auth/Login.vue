<script setup lang="ts">
import { LockKeyhole, LogIn } from '@lucide/vue'

</script>
<template><div class="flex min-h-screen items-center justify-center bg-background px-4 py-8"><main class="w-full max-w-md rounded-3xl border bg-card p-6 shadow-xl shadow-primary/5"><div class="flex size-14 items-center justify-center rounded-2xl bg-primary text-primary-foreground"><LockKeyhole class="size-7" /></div><p class="mt-6 text-xs font-semibold uppercase tracking-wider text-primary">Sistem Serah Terima</p><h1 class="mt-1 text-3xl font-bold">SERAYA</h1><p class="mt-2 text-sm text-muted-foreground">Sistem Elektronik Riwayat Administrasi Yang Aman.</p><a :href="route('sso.login')" class="mt-7 flex w-full items-center justify-center gap-2 rounded-xl bg-primary px-4 py-3 font-semibold text-primary-foreground"><LogIn class="size-4" />Masuk dengan sekali_login</a></main></div></template>
