<template>
  <div class="inactive-bar">
    <span>Your payment has been declined</span>
    <button class="button is-medium community-btn ml1" @click="openPaymentModal">
      Fix payment issue
    </button>
  </div>
</template>

<script>
export default {
  name: 'SubscriptionOverdueBar',
  computed: {
    community() {
      return this.$store.state.community.data;
    },
  },
  methods: {
    openPaymentModal() {
      this.$store.commit('showModal', {
        type: 'SubscriptionPurchase',
        transparent: true
      });
    }
  }
}
</script>
<style lang="scss" scoped>
.inactive-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 60px;
  background-color: #f14668;
  border-color: transparent;
  color: #fff;
  position: fixed;
  bottom: 0;
  left: 0;
  z-index: 100;
  padding: 1em;
  text-align: center;
}

@media only screen and (max-width: 600px) {
  .inactive-bar {
    font-size: 12px;
  }
}
</style>