<template>
  <button class="button is-medium community-blue-btn">
    {{ text }}
  </button>
</template>

<script>
export default {
  name: 'Button',
  props: {
    text: String,
  }
}
</script>

<style scoped>

</style>
