<template>
  <div 
    v-if="isOpen" 
    class="fixed inset-0 z-50 bg-background/80 backdrop-blur-sm"
    @click="close"
  >
    <div 
      :class="cn(sheetVariants({ side }), $attrs.class ?? '')"
      @click.stop
    >
      <slot />
    </div>
  </div>
</template>

<script setup>
import { cva } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  side: {
    type: String,
    default: 'right',
    validator: (value) => ['top', 'right', 'bottom', 'left'].includes(value)
  }
})

const emit = defineEmits(['close'])

const close = () => {
  emit('close')
}

const sheetVariants = cva(
  'fixed z-50 gap-4 bg-background p-6 shadow-lg transition ease-in-out',
  {
    variants: {
      side: {
        top: 'inset-x-0 top-0 border-b',
        bottom: 'inset-x-0 bottom-0 border-t',
        left: 'inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm',
        right: 'inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm'
      }
    },
    defaultVariants: {
      side: 'right'
    }
  }
)
</script>