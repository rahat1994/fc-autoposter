<template>
  <component :is="icon" :class="cn('h-4 w-4', $attrs.class)" v-bind="$attrs" />
</template>

<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps({
  name: {
    type: String,
    required: true
  }
})

const icon = computed(() => {
  const iconName = props.name.charAt(0).toUpperCase() + props.name.slice(1)
  return LucideIcons[iconName] || LucideIcons.HelpCircle
})
</script>