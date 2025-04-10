<template>
  <div class="wrapper">
    <div class="tab-content-title mb-05">
      {{ $t('community.members.setting-modal.cancel-membership.title') }}
    </div>

    <div class="mt1">
      <p>{{ $t('community.members.setting-modal.cancel-membership.are-you-sure') }}</p>
      <ul>
        <li>- {{ $t('community.members.setting-modal.cancel-membership.consequence-1') }}</li>
        <li>- {{ $t('community.members.setting-modal.cancel-membership.consequence-2') }}</li>
      </ul>
      <div class="mt1">
        <p><b class="has-text-danger">{{ $t('common.warning') }}</b></p>
        <p>
          {{ $t('community.members.setting-modal.cancel-membership.consequence-3', { amount: formatAmountWithCurrency(currency, member.subscription.amount) + getSubscriptionPeriod(member.subscription.period) }) }}
        </p>
      </div>
    </div>

    <div class="mt1">
      <div>
        <button
            class="button is-medium mt1 community-blue-btn is-fullwidth"
            @click="keepMembership()"
            :disabled="processing">
          {{ $t('community.members.setting-modal.cancel-membership.buttons.keep-membership') }}
        </button>
      </div>
      <div>
        <button
            class="button is-medium mt1 community-btn is-fullwidth"
            @click="cancelMembership"
            :class="buttonClass">
          {{ $t('community.members.setting-modal.cancel-membership.buttons.finish-cancellation') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import formatAmountWithCurrency from "../../../../../mixins/util";

export default {
  name: 'CancelMembership',
  mixins: [formatAmountWithCurrency],
  data() {
    return {
      processing: false
    }
  },
  computed: {
    currency() {
      return this.$store.state.auth.currency;
    },

    /**
     * Returns member
     */
    member() {
      return this.$store.state.member.data;
    },

    /**
     * Returns button status class
     */
    buttonClass() {
      return this.processing ? ' is-loading' : '';
    },
  },
  methods: {
    keepMembership() {
      this.$store.commit('setModalExtraData', 'membership');
    },

    async cancelMembership() {
      this.processing = true;
      await this.$store.dispatch('LEAVE_FROM_COMMUNITY');
      this.$store.commit('setModalExtraData', 'membership-cancelled');
      this.processing = false;
    },

    getSubscriptionPeriod(period) {
      if (period === 'yearly') {
        return this.$t('common.per-year')
      } else {
        return this.$t('common.per-month')
      }
    }
  }
}
</script>

<style scoped>

</style>
