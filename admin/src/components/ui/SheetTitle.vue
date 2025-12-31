<template>
  <h2 :class="cn('text-lg font-semibold text-foreground', $attrs.class ?? '')">
    <slot />
  </h2>
</template>

<script setup>
import { cn } from '@/lib/utils'
</script>