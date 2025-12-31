<template>
  <div :class="cn('border-b px-6 py-4', $attrs.class ?? '')">
    <slot />
  </div>
</template>

<script setup>
import { cn } from '@/lib/utils'
</script>