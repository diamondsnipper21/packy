<template>
  <template v-if="transactions.length === 0">
    {{ $t('community.members.setting-modal.no-payment-data') }}
  </template>

  <template v-else>
    <table class="table is-hoverable is-fullwidth pointer">
      <thead>
        <tr>
          <th>{{ $t('common.date') }}</th>
          <th class="text-center">{{ $t('common.number') }}</th>
          <th class="text-center">{{ $t('common.amount') }}</th>
          <th class="text-center">{{ $t('common.status') }}</th>
          <th class="text-center">{{ $t('common.invoice') }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="transaction in transactions">
          <td>{{ formatDate(transaction.created_at) }}</td>
          <td class="text-right">{{ transaction.number }}</td>
          <td class="text-right">{{ formatAmountWithCurrency(transaction.currency, transaction.amount / 100) }}</td>
          <td class="text-right">{{ $t('status.' + transaction.status ) }}</td>
          <td class="text-center">
            <font-awesome-icon
                icon="fa fa-file-pdf"
                class="link-icon"
                @click="downloadInvoice(transaction.invoice)"
                v-if="transaction.invoice"/>
          </td>
        </tr>
      </tbody>
    </table>
  </template>
</template>

<script>
import moment from "moment/moment";
import formatAmountWithCurrency from "../../mixins/util";

export default {
  name: 'UserPlansTransactionsTable',
  mixins: [formatAmountWithCurrency],
  props: {
    transactions: {
      type: Array,
      default: []
    }
  },
  computed: {
    community() {
      return this.$store.state.community.data;
    },
  },
  methods: {
    formatDate(date) {
      return moment(date).format("LL")
    },

    async downloadInvoice(invoice) {
      await this.$store.dispatch('DOWNLOAD_COMMUNITY_PLAN_INVOICE', {
        id: this.community.id,
        invoice: invoice
      });
    }
  }
}
</script>

<style scoped>

</style>
