<script setup lang="ts">
import { SelectValue as SelectValueRoot, type SelectValueProps } from 'radix-vue';

const props = defineProps<SelectValueProps>();
</script>

<template>
    <SelectValueRoot v-bind="props">
        <slot />
    </SelectValueRoot>
</template>
