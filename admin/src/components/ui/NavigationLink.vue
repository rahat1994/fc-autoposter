<template>
  <component
    :is="as"
    :class="cn(navigationLinkVariants({ variant, size }), $attrs.class ?? '')"
    v-bind="$attrs"
  >
    <slot />
  </component>
</template>

<script setup>
import { cva } from 'class-variance-authority'
import { cn } from '@/lib/utils'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'ghost'].includes(value)
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'sm'].includes(value)
  },
  as: {
    type: String,
    default: 'a'
  }
})

const navigationLinkVariants = cva(
  'group inline-flex h-9 w-max items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground focus:outline-none disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        default: 'bg-background text-foreground hover:bg-accent',
        ghost: 'bg-transparent hover:bg-accent hover:text-accent-foreground'
      },
      size: {
        default: 'h-9 px-4 py-2',
        sm: 'h-8 px-3 py-1'
      }
    },
    defaultVariants: {
      variant: 'default',
      size: 'default'
    }
  }
)
</script>