<template>
  <div class="wrapper">
    <div class="tab-content-title mb-05 mt1">
      {{ $t('community.members.setting-modal.payouts.invoice-service-fees.title') }}
    </div>
    <div class="flex mt1">
      <template v-if="invoices.length === 0">
        {{ $t('community.members.setting-modal.payouts.invoice-service-fees.no-history') }}
      </template>

      <template v-if="invoices.length > 0">
        <table class="table is-hoverable is-fullwidth pointer">
          <thead>
            <tr>
              <th>{{ $t('common.date') }}</th>
              <th>{{ $t('common.number') }}</th>
              <th>{{ $t('common.amount') }}</th>
              <th>{{ $t('common.status') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="invoice in invoices">
              <td>{{ formatDate(invoice.created_at) }}</td>
              <td>{{ invoice.number }}</td>
              <td>{{ formatAmountWithCurrency(invoice.currency, invoice.amount / 100) }}</td>
              <td>{{ $t('status.' + invoice.status) }}</td>
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
  name: 'InvoiceServiceFeesHistory',
  mixins: [formatAmountWithCurrency],
  computed: {
    community() {
      return this.$store.state.community.data;
    },
    invoices() {
      return this.community.invoices;
    },
  },
  methods: {
    formatDate(date) {
      return moment(date).format("LL")
    }
  }
}
</script>