<template>
  <div class="tab-content-label mt1">
    <div class="tab-content-item-content">
      <div class="tab-content-sub-title max-full-width">{{ title }}</div>
      <div class="tab-content-sub-desc">{{ subtitle }}</div>
    </div>

    <div class="flex align-items-center flex-end">
      <Toggle 
        v-model="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
      />
      <div v-if="modelValue" class="font-14px ml-05 mr-05">
        {{ $t('common.on') }}
      </div>
      <div v-else class="font-14px ml-05 mr-05">
        {{ $t('common.off') }}
      </div>

    </div>
  </div>
</template>

<script>

import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'

export default {
  name: 'InputSwitch',
  props: {
    modelValue: Boolean,
    title: String,
    subtitle: String
  },
  components: {
      Toggle
  },
  emits: ["update:modelValue"]
}
</script>

<style scoped>
  .tab-content-label {
      font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    width: 100%;
  }
  .tab-content-sub-desc {
      font-size: 14px;
    padding-left: 0px;
  }
</style>
