<template>
  <div class="inner-modal-container">

    <div class="relative">
      <p class="title is-4 font-weight-600">
        Complete payout
      </p>
    </div>

    <div class="mt1">
      <div class="font-16px">
        Do you want to complete this payout ?
      </div>
    </div>

    <div class="flex-1 mt1">
      <div style="display: flex; flex-direction: column;">
        <div style="display: flex; flex-direction: row; justify-content: space-between;">
          <div>
            {{ $t('common.user') }}
          </div>
          <div class="text-right">
            {{ selectedPayout.user.name }}<br/>
            <span class="font-16px">{{ selectedPayout.user.email }}</span>
          </div>
        </div>

        <div style="display: flex; flex-direction: row; justify-content: space-between;" class="mt1">
          <div>
            {{ $t('common.amount') }}
          </div>
          <div>
            {{ formatAmountWithCurrency(selectedPayout.currency, selectedPayout.amount / 100) }}
          </div>
        </div>
      </div>
    </div>

    <div class="mt1">
      <div class="font-14px">
        This amount will be instantly taken from your Stripe balance and sent to the user.<br/>
        Amount available after payout: {{ formatAmountWithCurrency('EUR', balanceAfterPayout / 100) }}.
      </div>
    </div>

    <div class="mt1">
      <VerificationCode v-if="!twoFactorExpires || dateNow > twoFactorExpires"/>
      <button
          v-else
          class="button is-large w100 community-blue-btn"
          :class="button"
          @click="confirm">
        {{ $t('common.confirm') }}
      </button>

      <button v-if="processing === false"
          class="button is-medium w100 mt1"
          :class="button"
          @click="cancel">
        {{ $t('common.cancel') }}
      </button>
    </div>
  </div>
</template>

<script>
import formatAmountWithCurrency from "../../mixins/util";
import VerificationCode from "../Forms/VerificationCode.vue";

export default {
  name: 'CompletePayout',
  components: {VerificationCode},
  mixins: [formatAmountWithCurrency],
  data() {
    return {
      processing: false
    };
  },
  created() {
    //
  },
  computed: {
    selectedPayout: {
      get() {
        return this.$store.state.superadmin.selected_payout;
      },
      set(v) {
        this.$store.commit('setSuperAdminSelectedPayout', v);
      }
    },
    button() {
      return this.processing ? ' is-loading' : '';
    },
    totals() {
      return this.$store.state.superadmin.payouts.totals;
    },
    balanceAfterPayout() {
      return this.totals.balance - this.selectedPayout.amount;
    },
    verificationCode() {
      return this.$store.state.communitycenter.verificationCode;
    },
    twoFactorExpires() {
      return this.$store.state.communitycenter.twoFactorExpires;
    },
    dateNow() {
      let dateNow = Date.now()/1000;

      return parseInt(dateNow);
    }
  },
  methods: {
    async confirm() {
      this.processing = true;
      await this.$store.dispatch('SUPER_ADMIN_PAYOUT_COMPLETE', this.selectedPayout);
      this.processing = false;
    },

    cancel() {
      this.$store.commit('hideModal');
      this.selectedPayout = null;
    }
  }
}
</script>

<style scoped>

</style>
