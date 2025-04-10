<template>
  <div class="inner-modal-container">
    <div class="inner-modal-container__body">
      <template v-if="member.subscription.trial_ends_at === null">
        <div style="text-align: center;">
          <font-awesome-icon icon="fa-circle-check" style="color: green; font-size: 50px"/>
          <p style="font-size: 22px; font-weight: 700; margin-top: 10px;">{{ $t('community.community.subscription-purchase-succeeded-modal.subscription-started.title') }}</p>
        </div>
        <div class="text-center">{{ $t('community.community.subscription-purchase-succeeded-modal.subscription-started.description') }}</div>
      </template>

      <template v-else>
        <div style="text-align: center;">
          <font-awesome-icon icon="fa-circle-check" style="color: green; font-size: 50px"/>
          <p style="font-size: 22px; font-weight: 700; margin-top: 10px;">{{ $t('community.community.subscription-purchase-succeeded-modal.trial-started.title') }}</p>
        </div>
        <div class="text-center">
          {{ $t('community.community.subscription-purchase-succeeded-modal.trial-started.description', { date: formatDate(member.subscription.trial_ends_at) }) }}
        </div>
      </template>

      <div>
        <button class="button is-large community-blue-btn" @click="enterCommunity()" style="width: 100%;">
          {{ $t('community.community.subscription-purchase-succeeded-modal.enter-community') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment/moment";

export default {
  name: 'SubscriptionPurchaseSucceeded',
  computed: {
    member() {
      return this.$store.state.member.data;
    },
  },
  created() {
    moment.locale(this.$i18n.locale);
  },
  methods: {
    enterCommunity() {
      this.$store.commit('hideModal');
    },

    /**
     * Returns formatted date
     */
    formatDate(date) {
      return moment(date).format("LL");
    },
  }
}
</script>

<style scoped>
.modal-container {
  padding: 0px!important;
}
  .inner-modal-container {
    padding: 0px!important;
  }
  .inner-modal-container__body {
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 1em 0;
  }
  .inner-modal-container__body > div {
    padding: 1em 2em;
  }
  input:hover, label:hover {
    cursor: pointer;
  }
</style>
