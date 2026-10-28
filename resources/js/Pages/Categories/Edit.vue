<script setup lang="ts">
import Form from './Form.vue'

interface Category {
  id: number
  name: string
  description: string | null
  is_active: boolean
}

defineProps<{ category: Category }>()
</script>

<template>
  <Form :category="category" />
</template>
