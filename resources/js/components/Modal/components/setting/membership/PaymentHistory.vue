<template>
  <div>
    <div class="tab-content-title mb-05">
      {{ $t('community.members.setting-modal.payment-history') }}
    </div>
    <div class="flex mt1">
      <UserTransactionsTable v-if="transactions" :transactions="transactions"/>
    </div>
  </div>
</template>

<script>
import formatAmountWithCurrency from "../../../../../mixins/util";
import UserTransactionsTable from "../../../../Table/UserTransactionsTable.vue";

export default {
  name: 'PaymentHistory',
  components: {UserTransactionsTable},
  mixins: [formatAmountWithCurrency],
  computed: {
      member() {
          return this.$store.state.member.data;
      },
      transactions() {
          return this.member.subscription?.transactions;
      },
  }
}
</script>