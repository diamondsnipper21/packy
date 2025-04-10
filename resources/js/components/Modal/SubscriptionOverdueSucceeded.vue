<template>
  <div class="inner-modal-container">
    <div class="inner-modal-container__body">
      <div class="text-center">
        Your credit card has been updated.
      </div>
      <div>
      <button class="button is-medium community-blue-btn" @click="close()" style="width: 100%;">
        {{ $t('common.on') }}
      </button>
      </div>
    </div>
  </div>
</template>

<script>
import moment from "moment/moment";

export default {
  name: 'SubscriptionOverdueSucceeded',
  created() {
    moment.locale(this.$i18n.locale);
  },
  methods: {
    close() {
      this.$store.commit('hideModal');
    }
  }
}
</script>