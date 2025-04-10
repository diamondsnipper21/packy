<template>
  <table style="width: 100%;">
    <tbody>
      <tr>
        <td>{{ $t('community.members.setting-modal.admin-settings.pricing-panel.details.your-member-pays') }}</td>
        <td style="text-align: right">{{ formatAmountWithCurrency(currency, price) }}</td>
      </tr>
      <!--<tr>
        <td>{{ $t('community.members.setting-modal.admin-settings.pricing-panel.details.credit-card-fees') }} (2.9% + {{ formatAmountWithCurrency(currency, 0.30) }})</td>
        <td style="text-align: right">-{{ formatAmountWithCurrency(currency, getCreditCardFees(price)) }}</td>
      </tr>-->
      <tr>
        <td>{{ $t('community.members.setting-modal.admin-settings.pricing-panel.details.packie-fees') }} (5% + {{ formatAmountWithCurrency(currency, 0.30) }}) + VAT (23% IE)</td>
        <td style="text-align: right">-{{ formatAmountWithCurrency(currency, getPackieFees(price)) }}</td>
      </tr>
      <tr>
        <td>{{ $t('community.members.setting-modal.admin-settings.pricing-panel.details.vat') }} (20% FR)</td>
        <td style="text-align: right">-{{ formatAmountWithCurrency(currency, getVat(price)) }}</td>
      </tr>
      <tr style="border-top: 1px solid #ddd; font-weight: 700;">
        <td>{{ $t('community.members.setting-modal.admin-settings.pricing-panel.details.you-receive') }}</td>
        <td style="text-align: right">+{{ formatAmountWithCurrency(currency, getNetPrice(price)) }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script>
import formatAmountWithCurrency from "../../../../mixins/util";

export default {
  name: 'PricingDetails',
  mixins: [formatAmountWithCurrency],
  props: ['price'],
  data() {
    return {
      dropdown: 0,
      displayPriceForm: false
    }
  },
  computed: {
    currency() {
      return this.$store.state.auth.currency;
    },
  },
  methods: {
    getCreditCardFees(amount) {
      // return amount * 0.029 + 0.30;
      return 0;
    },

    getPackieFees(amount) {
      // return amount * 0.01;
      return (amount * 0.05 + 0.30) * 1.23;
    },

    getVat(amount) {
      return amount - (amount / 1.20);
    },

    getNetPrice(amount) {
      return amount - this.getCreditCardFees(amount) - this.getPackieFees(amount) - this.getVat(amount);
    }
  },
}
</script>

<style scoped>
  td {
    padding: 5px;
  }
</style>
