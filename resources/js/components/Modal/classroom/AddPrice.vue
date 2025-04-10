<template>
  <div class="mt2">
    <div class="tab-content-title">
      {{ $t('community.members.setting-modal.admin-settings.pricing-panel.add-price') }}
    </div>

    <div class="flex mt1" style="flex-direction: column; gap: 15px;">
      <div class="flex-1" :class="inputClass('yearly')" v-if="price.type === null || price.type === 'monthly'">
        <div class="input input-group">
          <div class="placeholder-left">€</div>
          <input class="input-content" v-model="amount_monthly" type="number" step="0.01" min="0.01" max="4999.00" :disabled="price.type === null && type === 'yearly'"/>
          <div class="placeholder-right">/{{ $t('common.month') }}</div>
        </div>
      </div>

      <div class="flex-1" :class="inputClass('monthly')" v-if="price.type === null || price.type === 'yearly'">
        <div class="input input-group">
          <div class="placeholder-left">€</div>
          <input class="input-content" v-model="amount_yearly" type="number" step="0.01" min="0.01" max="999.00" :disabled="price.type === null && type === 'monthly'"/>
          <div class="placeholder-right">/{{ $t('common.year') }}</div>
        </div>
      </div>
    </div>

    <div class="flex mt1 font-14px" v-if="type === 'yearly' || type === 'monthly-yearly'">
      {{ $t('community.members.setting-modal.admin-settings.pricing-panel.add-price-tip') }}
    </div>

    <div class="flex mt1" style="flex-direction: column; gap: 10px;" v-if="!price.id">
      <template v-for="(typeOption, index) in typesOptions">
        <div class="flex align-items-center pointer">
          <input type="radio" :value="typeOption.value" v-model="type" :id="index" class="mr-05"/>
          <label class="text-left input-label w100 pointer" :for="index">{{ typeOption.label }}</label>
        </div>
      </template>
    </div>

    <div class="flex align-items-center mt1">
      <button class="button is-medium community-blue-btn mr-05" @click="save">
        {{ $t('common.save') }}
      </button>
      <button class="button is-medium community-btn" @click="cancel">
        {{ $t('common.cancel') }}
      </button>
    </div>

    <div class="mt1 font-14px has-text-danger">
      {{ $t('community.members.setting-modal.admin-settings.pricing-panel.warning') }}
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddPrice',
  data() {
    return {
      type: null,
      typesOptions: [{
        label: this.$t('common.monthly'),
        value: 'monthly'
      },{
        label: this.$t('common.yearly'),
        value: 'yearly'
      },{
        label: this.$t('common.monthly-yearly'),
        value: 'monthly-yearly'
      }]
    }
  },
  computed: {
    type: {
      get() {
        if (this.type === null) {
          if (this.amount_monthly !== null && this.amount_yearly !== null) {
            this.type = 'monthly-yearly'
          } else if (this.amount_yearly !== null) {
            this.type = 'yearly'
          } else {
            this.type = 'monthly'
          }
        }

        return this.type;
      },
      set(v) {
        this.type = v;
      }
    },

    community() {
      return this.$store.state.community.data;
    },

    price() {
      return this.$store.state.community.price;
    },

    id: {
      get() {
        return this.price.id
      },
      set(v) {
        this.$store.commit('setCommunityPriceId', v);
      }
    },

    amount_monthly: {
      get() {
        return this.price.amount_monthly
      },
      set(v) {
        this.$store.commit('setCommunityPriceAmountMonthly', v);
      }
    },

    amount_yearly: {
      get() {
        return this.price.amount_yearly
      },
      set(v) {
        this.$store.commit('setCommunityPriceAmountYearly', v);
      }
    }
  },
  methods: {
    async save() {
      await this.$store.dispatch('SAVE_STRIPE_PRICE');
    },

    cancel() {
      this.$emit('cancel-form')
    },

    inputClass(type) {
      return ((this.price.type === null && this.type === type) ? 'disabled' : '')
    }
  }
}
</script>

<style scoped>
  .input-group {
    display: flex;
    flex-direction: row;

    input {
      flex-grow: 1;
    }
    input:focus {
      outline: none;
    }
  }

  input {
    padding-left: 5px;
    padding-right: 20px;
  }
  .disabled {
    display: none;
  }
</style>
