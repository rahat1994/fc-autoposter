<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
          <span class="text-blue-600 text-lg">{{ icon }}</span>
        </div>
      </div>
      <div class="ml-4">
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p class="text-2xl font-semibold text-gray-900">{{ value }}</p>
        <p v-if="change" :class="changeClass" class="text-sm flex items-center">
          <span class="mr-1">{{ change > 0 ? '↗' : '↘' }}</span>
          {{ Math.abs(change) }}% from last month
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  change: {
    type: Number,
    default: null
  }
})

const changeClass = computed(() => {
  if (props.change === null) return ''
  return props.change > 0 ? 'text-green-600' : 'text-red-600'
})
</script>