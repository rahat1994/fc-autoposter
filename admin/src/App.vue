<template>
  <AppLayout>
    <template #default>
      <RouterView />
    </template>
  </AppLayout>
</template>

<script setup>
import { RouterView } from 'vue-router'
import AppLayout from './components/AppLayout.vue'
</script>
