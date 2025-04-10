<template>
    <div>
      <div class="flex align-items-center ml-min-05">
        <div class="back-arrow" @click="back">
          <font-awesome-icon icon="fa fa-arrow-left" class="link-icon" />
        </div>
        <div class="tab-content-title mb-0">
          {{ $t('community.members.setting-modal.admin-settings.billings.customize-invoices.title') }}
        </div>
      </div>

      <div class="tab-content-desc">
        {{ $t('community.members.setting-modal.admin-settings.billings.customize-invoices.description') }}
      </div>

      <div class="flex mt1">
        <div class="flex-1">
          <p class="text-left input-label">{{ $t('common.business-name') }} ({{ $t('common.optional') }})</p>
          <input class="input" :placeholder="$t('common.business-name')" v-model="businessName"/>
        </div>
      </div>

      <div class="flex mt1">
        <div class="flex-1">
          <p class="text-left input-label">{{ $t('common.address') }}</p>
          <input class="input" :placeholder="$t('common.address')" v-model="address"/>
        </div>
      </div>

      <div class="flex mt1">
        <div class="flex-1">
          <p class="text-left input-label">{{ $t('common.city') }}</p>
          <input class="input" :placeholder="$t('common.city')" v-model="city"/>
        </div>
      </div>

      <div class="flex mt1">
        <div class="flex-1">
          <p class="text-left input-label">{{ $t('common.zipcode') }}</p>
          <input class="input" :placeholder="$t('common.zipcode')" v-model="zipcode"/>
        </div>
      </div>

      <div class="flex mt1">
        <div class="flex-1">
          <p class="text-left input-label">{{ $t('common.country') }}</p>
          <SelectCountry :country="country" @selected-country="updateCountry"/>
        </div>
      </div>

      <div class="flex mt1">
        <div class="flex-1">
          <p class="text-left input-label">{{ $t('common.vat-number') }}</p>
          <input class="input" :placeholder="$t('common.vat-number')" v-model="vatNumber"/>
        </div>
      </div>

      <div class="flex mt2">
        <button class="button community-blue-btn mr-05" :class="button" @click="save">{{ $t('common.save') }}</button>
        <button class="button is-medium" @click="back">{{ $t('common.cancel') }}</button>
      </div>
    </div>
</template>

<script>
import { BillingSectionType } from "../../../../../data/enums";
import SelectCountry from "../../../../Forms/SelectCountry.vue";
export default {
  name: 'CustomizeInvoices',
  components: {SelectCountry},
  props: {
    changeSection: {
      type: Function
    },
  },
  data() {
    return {
      processing: false
    };
  },
  computed: {
    user() {
      return this.$store.state.auth.user;
    },

    community() {
      return this.$store.state.community.data;
    },

    invoiceData() {
      return JSON.parse(this.community.invoice_data ?? '{}');
    },

    businessName: {
      get() {
        return this.invoiceData.businessName ?? this.user.name;
      },
      set(v) {
        this.$store.commit('setCommunityInvoiceData', { key: 'businessName', value: v });
      }
    },

    address: {
      get() {
        return this.invoiceData ? this.invoiceData.address : null;
      },
      set(v) {
        this.$store.commit('setCommunityInvoiceData', { key: 'address', value: v });
      }
    },

    city: {
      get() {
        return this.invoiceData ? this.invoiceData.city : null;
      },
      set(v) {
        this.$store.commit('setCommunityInvoiceData', { key: 'city', value: v });
      }
    },

    zipcode: {
      get() {
        return this.invoiceData ? this.invoiceData.zipcode : null;
      },
      set(v) {
        this.$store.commit('setCommunityInvoiceData', { key: 'zipcode', value: v });
      }
    },

    country: {
      get() {
        return this.invoiceData ? this.invoiceData.country : null;
      },
      set(v) {
        this.$store.commit('setCommunityInvoiceData', { key: 'country', value: v });
      }
    },

    vatNumber: {
      get() {
        return this.invoiceData ? this.invoiceData.vatNumber : null;
      },
      set(v) {
        this.$store.commit('setCommunityInvoiceData', { key: 'vatNumber', value: v });
      }
    },

    button() {
      return this.processing ? ' is-loading' : '';
    }
  },
  methods: {
    updateCountry(country) {
      this.country = country;
    },
    back() {
      this.changeSection(BillingSectionType.OVERVIEW)
    },
    async save() {
      this.processing = true;
      await this.$store.dispatch('UPDATE_USER_INVOICE_DATA', {
        'businessName': this.businessName,
        'address': this.address,
        'city': this.city,
        'zipcode': this.zipcode,
        'country': this.country,
        'vatNumber': this.vatNumber
      });
      this.processing = false;
      this.changeSection(BillingSectionType.OVERVIEW)
    }
  }
}
</script>

<style scoped>

</style>
