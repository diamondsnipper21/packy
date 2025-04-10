<template>
  <div>
    <p class="tab-content-title">
      {{ $t('community.members.setting-modal.payouts.title') }}
    </p>

    <div style="display: flex;" class="mt1">
      <div style="display: flex; flex-direction: column; row-gap: 3px; border-radius: 5px; background-color: rgb(244, 245, 248); padding: 15px 20px; text-align: center; width: 200px;">
        <div style="font-weight: 700; font-size: 20px">{{ formatAmountWithCurrency(currency, payoutsBalance) }}</div>
        <div class="font-14px">{{ $t('community.members.setting-modal.payouts.balance') }}</div>
      </div>
      <div class="font-14px" style="display: flex; flex-direction: column; padding: 20px 30px; gap: 5px;">
        <div v-if="community.next_payout">
          {{ $t('community.members.setting-modal.payouts.next-payout', { amount: formatAmountWithCurrency(community.next_payout.currency, community.next_payout.payable / 100), days : community.next_payout.daysUntilPayable }) }}
        </div>
        <div>{{ $t('community.members.setting-modal.payouts.pending-balance', { amount: formatAmountWithCurrency(currency, pendingPayoutsBalance) }) }}</div>
      </div>
    </div>

    <div class="mt1">
      <button
          class="button is-medium community-btn"
          :class="button"
          :disabled="payoutsBalance <= 0"
          @click="askForPayout">
        {{ $t('community.members.setting-modal.payouts.ask-for-payout') }}
      </button>
    </div>

    <div class="flex mt2" v-if="years.length > 1">
      <div class="flex-1">
        <p class="text-left input-label">
          {{ $t('common.year') }}
        </p>
        <select id="year" v-model="payoutFilterYear" class="input" style="width: 100px;">
          <option v-for="year in years" :value="year">{{ year }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="setting-content-loading"></div>
    <div class="mt1" v-else>
      <PayoutHistory/>

      <div class="tab-content-title mb-05 mt1">
        {{ $t('community.members.setting-modal.payouts.transactions-history') }}
      </div>
      <UserTransactionsTable :transactions="transactions"/>
      <!--<InvoiceServiceFeesHistory/> todo later -->
    </div>
  </div>
</template>

<script>
import formatAmountWithCurrency from "../../../../mixins/util";
import moment from "moment/moment";
import UserTransactionsTable from "../../../Table/UserTransactionsTable.vue";
import PayoutHistory from "./PayoutHistory.vue";
import slugify from "@sindresorhus/slugify";

export default {
  name: 'Payouts',
  components: {PayoutHistory, UserTransactionsTable},
  mixins: [formatAmountWithCurrency],
  data() {
    return {
      processing: false,
      loading: false
    };
  },
  computed: {
    button() {
      return this.processing ? ' is-loading' : '';
    },
    community() {
      return this.$store.state.community.data;
    },
    currency() {
      return this.$store.state.auth.currency;
    },
    payouts() {
      return this.community.payouts;
    },
    payoutsBalance() {
      return this.community.payoutsBalance;
    },
    transactions() {
      return this.community.transactions;
    },
    payoutFilterYear: {
      get() {
        return this.$store.state.community.payoutFilterYear
      },
      async set(v) {
        this.$store.commit('setCommunityPayoutFilterYear', v);
        this.loading = true;
        await this.$store.dispatch('GET_PAYOUTS_DATA', v);
        this.loading = false;
      }
    },

    pendingPayoutsBalance() {
      return this.community.pendingPayoutsBalance;
    },

    years() {
      let currentYear = new Date().getFullYear();
      let years = [];
      for (let y = 2025; y <= currentYear; y++) {
        years.push(y);
      }

      return years;
    },
  },
  methods: {
    formatDate(date) {
      return moment(date).format("LL")
    },
    async askForPayout() {
      this.processing = true;
      await this.$store.dispatch('ASK_FOR_PAYOUT');
      this.processing = false;
    }
  }
}
</script>