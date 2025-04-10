<template>
  <div class="wrapper">
    <div class="tab-content-title mb-05">
      {{ $t('community.members.setting-modal.payouts.payout-history') }}
    </div>
    <div class="flex mt1">
      <template v-if="payouts.length === 0">
        {{ $t('community.members.setting-modal.payouts.no-payout-history') }}
      </template>

      <template v-if="payouts.length > 0">
        <table class="table is-hoverable is-fullwidth pointer">
          <thead>
            <tr>
              <th>{{ $t('common.date') }}</th>
              <th>{{ $t('common.amount') }}</th>
              <th>{{ $t('common.status') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payout in payouts">
              <td>{{ formatDate(payout.created_at) }}</td>
              <td>{{ formatAmountWithCurrency(payout.currency, payout.amount / 100) }}</td>
              <td>{{ $t('status.' + payout.status) }}</td>
            </tr>
          </tbody>
        </table>
      </template>
    </div>
  </div>
</template>

<script>
import formatAmountWithCurrency from "../../../../mixins/util";
import moment from "moment/moment";

export default {
  name: 'PayoutHistory',
  mixins: [formatAmountWithCurrency],
  computed: {
    community() {
      return this.$store.state.community.data;
    },
    payouts() {
      return this.community.payouts;
    },
  },
  methods: {
    formatDate(date) {
      return moment(date).format("LL")
    }
  }
}
</script>

<style scoped>

</style>
