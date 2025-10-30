<template>
  <nav :class="cn('flex space-x-2 lg:flex-col lg:space-x-0 lg:space-y-1', $attrs.class ?? '')">
    <slot />
  </nav>
</template>

<script setup>
import { cn } from '@/lib/utils'
</script>