<script setup lang="ts">
import SimpleMasterIndex from '@/components/SimpleMasterIndex.vue'
interface Position { id: number; name: string; is_active: boolean }
defineProps<{ positions: Position[] }>()
</script>
<template><SimpleMasterIndex :items="positions" resource="positions" title="Jabatan" /></template>
