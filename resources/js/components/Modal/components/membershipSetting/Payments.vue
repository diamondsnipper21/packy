<template>
  <div class="wrapper">
    <div class="tab-content-title mb-05">
      {{ $t('community.members.setting-modal.payment-history') }}
    </div>
    <div class="flex mt1 min-height-200">
      <UserTransactionsTable :transactions="transactions"/>
    </div>
  </div>
</template>

<script>
import formatAmountWithCurrency from "../../../../mixins/util";
import UserTransactionsTable from "../../../Table/UserTransactionsTable.vue";

export default {
  name: 'Membership',
  mixins: [formatAmountWithCurrency],
  components: {
    UserTransactionsTable,
  },
  computed: {
    community() {
      return this.$store.state.community.data;
    },
    membershipSettings() {
      return this.$store.state.member.membershipSettings;
    },
    selectedMember() {
      return this.membershipSettings?.member;
    },
    transactions() {
      return this.selectedMember && typeof this.selectedMember.transactions !== 'undefined' ? this.selectedMember.transactions?.filter((transaction) => transaction.community_id === this.community.id) : [];
    }
  }
}
</script>

<style scoped>
  .min-height-200 {
      min-height: 200px;
  }
</style>
