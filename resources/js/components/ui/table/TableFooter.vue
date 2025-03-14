<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <tfoot :class="cn('bg-primary-foreground font-medium text-primary', props.class)">
        <slot/>
    </tfoot>
</template>
