<script setup lang="ts">
import { SelectRoot, type SelectRootEmits, type SelectRootProps, useForwardPropsEmits } from 'radix-vue';
import { computed, useVModel } from '@vueuse/core';

const props = defineProps<SelectRootProps & {
  modelValue?: string | number;
}>();

const emits = defineEmits<SelectRootEmits & {
  'update:modelValue': [value: string | number];
}>();

const model = useVModel(props, 'modelValue', emits);

const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
  <SelectRoot v-bind="forwarded" :model-value="model" @update:model-value="model = $event">
    <slot />
  </SelectRoot>
</template>
