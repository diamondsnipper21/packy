<template>
  <div>
    <div style="position: absolute; right: 25px;">
      <font-awesome-icon icon="fa fa-cog" class="link-icon" @click="showSubscriptionSettings" style="cursor: pointer"/>
    </div>

    <div class="tab-content-title">{{ $t('community.members.setting-modal.admin-settings.pricing-panel.title') }}</div>
    <div class="tab-content-desc">{{ $t('community.members.setting-modal.admin-settings.pricing-panel.subtitle') }}</div>

    <div class="highlighted-block highlighted-block--success" v-if="stripeConnected === true">
      <div class="highlighted-block__title">
        <font-awesome-icon :icon="['fa', 'check']"/> {{ $t('community.members.setting-modal.admin-settings.stripe-connect.connected-message') }}
      </div>
      <a class="highlighted-block__link" v-if="stripeLoginLink" :href="stripeLoginLink" target="_blank">
        {{ $t('community.members.setting-modal.admin-settings.stripe-connect.dashboard-link') }}
      </a>
    </div>

    <div class="highlighted-block highlighted-block--warning" v-else>
      <div class="highlighted-block__title">{{ $t('community.members.setting-modal.admin-settings.stripe-connect.title') }}</div>
      <div class="highlighted-block__subtitle">{{ $t('community.members.setting-modal.admin-settings.stripe-connect.subtitle') }}</div>
      <div>
        <button class="button is-medium community-blue-btn" @click="setupStripe">{{ $t('common.continue') }}</button>
      </div>
    </div>

    <AddPrice v-if="displayPriceForm === true" @cancel-form="hidePriceForm()"/>
    <template v-else>

      <div class="flex mt2" v-if="community.price !== null">
        <div class="flex-1">
          <p class="text-left input-label">{{ $t('community.members.setting-modal.admin-settings.pricing-panel.free-trial-days') }}</p>
          <select class="input" name="free_trial_days_input" v-model="trial_days" @change="updateFreeTrialDays" style="margin-top: 5px">
            <option :value="0">{{ $t('community.members.setting-modal.admin-settings.pricing-panel.no-free-trial-days') }}</option>
            <option :value="7">{{ $t('community.members.setting-modal.admin-settings.pricing-panel.7-days-trial') }}</option>
            <option :value="14">{{ $t('community.members.setting-modal.admin-settings.pricing-panel.14-days-trial') }}</option>
            <option :value="30">{{ $t('community.members.setting-modal.admin-settings.pricing-panel.30-days-trial') }}</option>
          </select>
        </div>
      </div>

      <div class="mt1">
        <div class="tab-content-item">
          <div class="tab-content-item-content">
            <div class="flex align-items-center">
              <div class="tab-content-sub-title font-weight-600 mr1">
                <font-awesome-icon icon="fa fa-tag" class="tab-content-action-dropdown-link-icon mr1"/>
                <label style="font-size: 16px;">{{ $t('community.community.free') }}</label>
              </div>
              <div v-if="!community.price" class="item-privacy-mark published">
                {{ $t('community.members.setting-modal.admin-settings.pricing-panel.current-price') }}
              </div>
            </div>

            <div class="tab-content-desc">
              {{ $t('common.members', { count: 0 }) }}
            </div>
          </div>

          <div class="tab-content-item-action no-button" v-if="community.price !== null">
            <div class="tab-content-action-dropdown" @click="showDropdown(0)">
              <div class="dropdown-trigger flex">
                <button class="button is-medium community-btn tab-content-action-dropdown-link" >
                  <font-awesome-icon icon="fa fa-bars" class="tab-content-action-dropdown-link-icon"/>
                </button>
              </div>
              <div class="dropdown-content dropdown-menu-items" :class="dropdown === 0 ? 'show' : ''">
                <div class="tab-content-item-action-item" @click="setCommunityPrice(null)">
                  {{ $t('community.members.setting-modal.admin-settings.pricing-panel.make-current') }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div
            v-for="price in prices"
            :key="price.id"
            class="tab-content-item"
        >
          <div class="tab-content-item-content">
            <div class="flex align-items-center">
              <div class="tab-content-sub-title font-weight-600 mr1">
                <font-awesome-icon icon="fa fa-tag" class="tab-content-action-dropdown-link-icon mr1"/>
                <label style="font-size: 16px;">{{ getPriceName(price) }}</label>
              </div>

              <div v-if="community.price && price.id === community.price.id" class="item-privacy-mark published">
                {{ $t('community.members.setting-modal.admin-settings.pricing-panel.current-price') }}
              </div>
            </div>

            <div class="tab-content-desc">
              {{ $t('common.members', { count: price.members }) }}
            </div>

            <div class="tab-content-desc" style="display: flex; flex-direction: column; gap: 15px;">
              <div>
                <a @click="showDetails(price.id)">
                  <font-awesome-icon icon="fa fa-chevron-down" class="mr1"/> {{ $t('community.members.setting-modal.admin-settings.pricing-panel.details.title') }}
                </a>
              </div>
              <template v-if="displayDetails === price.id">
                <div style="padding: 10px 15px; background-color: rgb(244, 245, 248); border-radius: 10px;" v-if="price.amount_monthly">
                  <PricingDetails :price="price.amount_monthly" />
                </div>
                <div style="padding: 10px 15px; background-color: rgb(244, 245, 248); border-radius: 10px;" v-if="price.amount_yearly">
                  <PricingDetails :price="price.amount_yearly" />
                </div>
              </template>
            </div>
          </div>

          <div class="tab-content-item-action no-button">
            <div @click="showDropdown(price.id)" class="tab-content-action-dropdown" v-show="community.price?.id !== price.id || !price.amount_monthly || !price.amount_yearly">
              <div class="dropdown-trigger flex">
                <button class="button is-medium community-btn tab-content-action-dropdown-link">
                  <font-awesome-icon icon="fa fa-bars" class="tab-content-action-dropdown-link-icon"/>
                </button>
              </div>
              <div class="dropdown-content dropdown-menu-items" :class="dropdown === price.id ? 'show' : ''">
                <div class="tab-content-item-action-item" @click="setCommunityPrice(price.id)" v-if="community.price?.id !== price.id">
                  {{ $t('community.members.setting-modal.admin-settings.pricing-panel.make-current') }}
                </div>
                <div class="tab-content-item-action-item" @click="addCommunityPrice(price, 'monthly')" v-if="!price.amount_monthly">
                  {{ $t('community.members.setting-modal.admin-settings.pricing-panel.add-monthly') }}
                </div>
                <div class="tab-content-item-action-item" @click="addCommunityPrice(price, 'yearly')" v-if="!price.amount_yearly">
                  {{ $t('community.members.setting-modal.admin-settings.pricing-panel.add-annual') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="mt1 mb2" v-if="stripeConnected === true">
        <button class="button is-medium community-btn" @click="showPriceForm()">
          {{ $t('community.members.setting-modal.admin-settings.pricing-panel.add-price') }}
        </button>
      </div>
    </template>
  </div>
</template>

<script>
import AddPrice from "../../classroom/AddPrice.vue";
import PricingDetails from "./PricingDetails.vue";
import formatAmountWithCurrency from "../../../../mixins/util";

export default {
  name: 'PricingSetting',
  components: {PricingDetails, AddPrice},
  mixins: [formatAmountWithCurrency],
  data() {
    return {
      dropdown: null,
      displayDetails: null
    }
  },
  computed: {
    community() {
      return this.$store.state.community.data;
    },

    stripeConnected() {
      return this.$store.state.auth.stripeConnected;
    },

    stripeLoginLink() {
      return this.$store.state.auth.stripeLoginLink;
    },

    product() {
      return this.$store.state.community.data.products[0];
    },

    prices() {
      if (this.product) {
        return this.product.prices;
      } else {
        return [];
      }
    },

    displayPriceForm: {
      get() {
        return this.$store.state.community.displayPriceForm
      },
      set(v) {
        this.$store.commit('setDisplayPriceForm', v);
      }
    },

    trial_days: {
      get() {
        return this.community.trial_days
      },
      set(v) {
        this.$store.commit('setTrialDays', v);
      }
    },

    currency() {
      return this.$store.state.auth.currency;
    },
  },
  methods: {
    /**
     * Show or hide the dropdown
     */
    showDropdown(id) {
        if (this.dropdown === id) {
            this.dropdown = null;
        } else {
            this.dropdown = id;
        }
    },

      hideDropdown() {
        console.log('hideDropdown')
          this.dropdown = null;
      },

    /**
     * When we click outside of the dropdown, close it
     */
    onClickOutside() {
      this.dropdown = null;
    },

    async setCommunityPrice(id) {
      await this.$store.dispatch('SAVE_COMMUNITY_PRICE', id);
    },

    addCommunityPrice(price, type) {
      price.type = type;
      this.$store.commit('setCommunityPrice', price);
      this.displayPriceForm = true;
    },

    showPriceForm() {
      this.$store.commit('resetCommunityPrice');
      this.displayPriceForm = true;
      this.displayDetails = null;
      // this.emitter.emit('hidePriceDetails')
    },

    hidePriceForm() {
      this.displayPriceForm = false;
    },

    showDetails(priceId) {
      if (this.displayDetails === priceId) {
        this.displayDetails = null;
      } else {
        this.displayDetails = priceId;
      }
    },

    getPriceName(price) {
      let name = '';
      if (price.amount_monthly) {
        name += this.formatAmountWithCurrency(this.currency, price.amount_monthly) + '/' + this.$t('common.month');
      }
      if (price.amount_yearly) {
        if (price.amount_monthly) {
          name += ' ' + this.$t('common.or') + ' ';
        }
        name += this.formatAmountWithCurrency(this.currency, price.amount_yearly) + '/' + this.$t('common.year');
      }

      return name;
    },

    setupStripe() {
      window.open('/stripe/refresh', '_blank');
    },

    async updateFreeTrialDays()
    {
      await this.$store.dispatch('SAVE_COMMUNITY_FREE_TRIAL_DAYS');
    },

    showSubscriptionSettings()
    {
      this.$store.commit('setModalExtraData', 'pricing-notifications-settings');
    }
  },
}
</script>

<style scoped>
.tab-content-sub-title {
  max-width: none;
}

.highlighted-block {
  padding: 20px;
  border-radius: 10px;
  background-color: #faeed4;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;

  .highlighted-block__title {
    font-size: 18px;
    font-weight: bold;
  }

  .highlighted-block__link {
    font-size: 14px;
  }
}

.tab-content-item-content {
    width: calc(100% - 100px);
}

@media only screen and (max-width: 600px)
{
  .tab-content-item-content {
    width: calc(100% - 60px);
  }
}

.show {
    display: block!important;
    padding-bottom: 0px;
}
</style>
