<template>
  <div>
    <div class="tab-content-title mb-05">
      Subscription settings
    </div>

    <InputSwitch
        v-model="newPaymentNotification"
        :title="'New customer notification'"
        :subtitle="'Show a notification when a new payment is captured.'"
    />

    <div class="mt2">
      <div>
        <button
            class="button is-medium w100 community-blue-btn"
            :class="button"
            @click="save"
        >{{ $t('common.save') }}</button>
      </div>
      <div class="mt1">
        <button
            class="button is-medium w100"
            :class="button"
            @click="backToPricing"
        >{{ $t('common.cancel') }}</button>
      </div>
    </div>
  </div>
</template>

<script>
import InputSwitch from "../../../Forms/InputSwitch.vue";

export default {
  name: 'PricingSubscriptionSettings',
  components: {InputSwitch},
  data() {
    return {
      processing: false
    }
  },
  computed: {
    newPaymentNotification: {
      get() {
        return this.$store.state.community.newPaymentNotification;
      },
      set(value) {
        this.$store.commit('setNewPaymentNotification', value);
      }
    },

    /**
     * Returns button status class
     */
    button() {
      return this.processing ? ' is-loading' : '';
    },
  },
  methods: {
    backToPricing() {
      this.$store.commit('setModalExtraData', 'admin-membership');
    },

    async save() {
      this.processing = true;
      await this.$store.dispatch('SAVE_COMMUNITY_PAYMENT_NOTIFICATION', this.custom_notifications);
      this.$store.commit('setModalExtraData', 'admin-membership');
      this.processing = false;
    }
  }
}
</script>

<style scoped>

</style>
