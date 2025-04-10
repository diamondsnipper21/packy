<template>
  <div class="inner-modal-container">
    <div class="inner-modal-container__header">
      <div>
        <img v-if="community.logo" :src="community.logo" class="community-logo-placeholder"/>
        <div v-else class="community-logo-placeholder">{{ logoPlaceholder(community.name) }}</div>
      </div>
      <div style="font-size: 22px; font-weight: 700;">{{ community.name }}</div>
    </div>

      <div class="inner-modal-container__body">
        <div v-if="!member.subscription">
          <div class="font-weight-700">
            {{ $t('community.members.setting-modal.membership') }}
          </div>
          <div style="border: 1px solid #eee; border-radius: 5px; display: flex; flex-direction: row; height: 100%;">
            <div style="padding: 15px; flex: 1; border-right: 2px solid #eee;" v-if="price.amount_monthly">
              <div>
                <input id="monthly" type="radio" value="monthly" class="mr-2" v-model="period">
                <label for="monthly" class="font-weight-700">{{ $t('common.monthly') }}</label>
              </div>
              <div class="font-weight-700">{{ formatAmountWithCurrency(currency, price.amount_monthly) }}{{ $t('common.per-month') }}</div>
              <div class="font-14px"></div>
              <div class="font-12px" style="color: #bbb;" v-if="country">
                  <template v-if="getAmountVat(price.amount_monthly) > 0">
                      {{ $t('community.community.subscription-purchase-modal.vat-included') }} {{ formatAmountWithCurrency(currency, getAmountVat(price.amount_monthly)) }} ({{ country }})
                  </template>
                  <template v-else>
                      {{ $t('community.community.subscription-purchase-modal.vat-excluded') }}
                  </template>
              </div>
            </div>
            <div style="padding: 15px; flex: 1" v-if="price.amount_yearly">
              <div style="background: #8cc268; color: white; font-size:12px; padding: 5px 10px; border-radius: 5px; position: absolute; margin-top: -29px; right: 45px;" v-if="discount > 0">
                {{ $t('community.community.subscription-purchase-modal.save-x-percent', { discount: discount }) }}
              </div>
              <div>
                <input id="yearly" type="radio" value="yearly" class="mr-2" v-model="period">
                <label for="yearly" class="font-weight-700">{{ $t('common.yearly') }}</label>
              </div>
              <div class="font-weight-700">{{ formatAmountWithCurrency(currency, getMonthlyAmount(price.amount_yearly)) }}{{ $t('common.per-month') }}</div>
              <div class="font-14px">{{ formatAmountWithCurrency(currency, price.amount_yearly) }} {{ $t('community.community.subscription-purchase-modal.x-billed-yearly') }}</div>
              <div class="font-12px" style="color: #bbb;" v-if="country">
                {{ $t('community.community.subscription-purchase-modal.vat-included') }} {{ formatAmountWithCurrency(currency, getAmountVat(price.amount_yearly)) }} ({{ country }})
              </div>
            </div>
          </div>
        </div>

        <div v-if="member.subscription && member.subscription.status === 'overdue'">
          <div style="color:#f14668; font-weight: bold;">
            {{ $t('community.community.subscription-purchase-modal.payment-declined') }}
          </div>
        </div>

        <div class="flex">
          <!-- Card number -->
          <div class="flex-1">
            <div class="card-selection">
              <p class="text-left input-label font-weight-700">
                {{ $t('community.community.create-modal.card-number') }}
              </p>
            </div>

            <CardElement
                v-if="isNewMethod || !paymentMethods.length === 0"
                ref="cardRef"
                :pk="publishableKey"
                @token="tokenCreated"
                @cardElement="cardElementCreated"
                @stripeInstance="stripeInstanceCreated"
                :stripeAccount="stripeAccountId"/>

            <select
                v-if="!isNewMethod && paymentMethods.length > 0"
                v-model="paymentMethodId"
                class="input">
              <option value="0" disabled>{{ $t('community.community.create-modal.select-card') }}</option>
              <option
                  v-for="method in paymentMethods"
                  :value="method.id"
                  style="text-transform: capitalize;">
                {{ method.card_brand }} **** {{ method.last4 }}
              </option>
            </select>

            <div v-show="paymentMethods.length > 0">
              <p class="input-label">
                <a class="font-14px" @click="toggleNewMethod">
                  {{ isNewMethod ? $t('community.community.create-modal.select-card') : $t('community.community.create-modal.use-new-card') }}
                </a>
              </p>
            </div>

          </div>
        </div>

        <div class="flex" v-if="!user.country">
          <!-- Country -->
          <div class="flex-1">
            <p class="text-left input-label font-weight-700">
              {{ $t('signup.country') }}
            </p>
            <select v-model="country" class="input">
              <option disabled v-bind:value="null">{{ $t('signup.country') }}</option>
              <option value="FR">France</option>
              <option value="BE">Belgium</option>
              <option value="CH">Switzerland</option>
              <option value="GB">United Kingdom</option>
              <option value="IE">Ireland</option>
              <option value="US">USA</option>
              <option disabled v-bind:value="null">------</option>
              <option v-for="item in countries" :value="item.value">
                {{ item.text }}
              </option>
            </select>
          </div>
        </div>

        <div>
          <button
              class="button is-large community-blue-btn"
              :class="button"
              @click="doSubscriptionCheckout"
              :disabled="(!stripeToken && !paymentMethodId) || processing || (!user.country)"
              style="width: 100%;" v-html="member.subscription ? $t('common.update') : $t('common.pay')"/>
          <div class="font-14px text-center mt1" v-html="termsText" v-if="!member.subscription"/>
          <div class="mt1 text-center" v-if="country">
            <a class="font-12px" @click="goToProfile()">
              {{ $t('community.community.subscription-purchase-modal.vat-country-link', { country: country }) }}
            </a>
          </div>
        </div>
      </div>

  </div>
</template>

<script>
import moment from 'moment'
import CardElement from "../Stripe/CardElement.vue";
import formatAmountWithCurrency from "../../mixins/util";
import countries from "../../data/countries";

export default {
  name: 'SubscriptionPurchase',
  mixins: [formatAmountWithCurrency],
  components: {CardElement},
  data() {
    return {
      publishableKey: stripeKeyMarketplace,
      period: 'monthly',
      processing: false,
      paymentMethodId: 0,
      isNewMethod: ((this.$store.state.member.data?.payment_methods || []).length) ? false : true,
      stripeToken: null,
        cardElement: null,
        stripeInstance: null,
      countries: []
    }
  },
  mounted() {
    if (!this.price.amount_monthly) {
      this.period = 'yearly';
    }
  },
  created() {
    this.countries = countries;
  },
  computed: {
    country() {
      if (this.user?.country) {
        return this.user.country
      }

      return null
    },
    user() {
      return this.$store.state.auth.user;
    },

    community() {
      return this.$store.state.community.data;
    },

    member() {
      return this.$store.state.member.data;
    },

    price() {
      return this.community.price;
    },

    currency() {
      return this.$store.state.auth.currency;
    },

    stripeAccountId() {
      return this.community.user.stripe_account?.account_id
    },

    /**
     * Returns button status class
     */
    button() {
      return this.processing ? ' is-loading' : '';
    },

    paymentMethods() {
      return this.member.payment_methods ?? [];
    },

    termsText() {
      let text = '';
      let date = new Date();

      if (this.community.trial_days) {
        text = this.$t('community.community.subscription-purchase-modal.day-free-trial', { days: this.community.trial_days }) + ' ';
        date.setDate(date.getDate() + this.community.trial_days);
      }

      let firstChargeAmount = this.formatAmountWithCurrency(this.currency, this.price.amount_monthly) + this.$t('common.per-month');
      if (this.period === 'yearly') {
        firstChargeAmount = this.formatAmountWithCurrency(this.currency, this.price.amount_yearly) + this.$t('common.per-year');
      }

      text += this.$t('community.community.subscription-purchase-modal.first-charge-description', {
        date: moment(date).format('LL'),
        amount: firstChargeAmount,
          link1: '<a target="_blank" href="/legal/terms">',
          link2: '</a>'
      })
      return text;
    },

    vatRate() {
      return this.$store.state.member.data.vatRate;
    },

    discount() {
      let discount = 0;

      if (this.price.amount_monthly && this.price.amount_yearly) {
        let yearlyMonthly = this.price.amount_monthly * 12;
        discount = 1 - (this.price.amount_yearly / yearlyMonthly);
      }

      if (discount < 0) {
        discount = 0
      }

      return parseInt(discount * 100);
    },

      type() {
          return this.isNewMethod ? 'stripe_payment_method' : 'payment_method_id';
      },
      paymentMethod() {
          return this.isNewMethod ? this.newPaymentMethod : this.paymentMethodId;
      }
  },
  methods: {
    async tokenCreated(response) {
      this.stripeToken = response;
    },
      async cardElementCreated(response) {
          this.cardElement = response;
      },
      async stripeInstanceCreated(response) {
          this.stripeInstance = response;
      },

    async doSubscriptionCheckout() {
      this.processing = true;

        await this.$store.dispatch('SUBSCRIPTION_CHECKOUT', {
            communityId: this.community.id,
            period: this.period,
            stripeInstance: this.stripeInstance,
            type: this.type,
            paymentMethod: this.paymentMethod
        });

      this.processing = false;
    },

      toggleNewMethod() {
          this.isNewMethod = !this.isNewMethod
          if (!this.isNewMethod) {
              this.newPaymentMethod = null;
          } else {
              this.paymentMethodId = null;
          }
      },

    getMonthlyAmount(amount) {
      return amount/12;
    },

    getAmountVat(amount) {
      return amount - amount / (1 + (this.vatRate / 100));
    },

    goToProfile()
    {
      this.$store.commit('showModal', {
        type: 'CommunitySetting',
        extraData: 'profile',
        transparent: true
      });
    },

    /**
     * Get first characters from every words in string
     */
    logoPlaceholder(name) {
      const matches = name.match(/\b(\w)/g);
      const acronym = matches.join('');

      return acronym.slice(0, 2).toUpperCase();
    },
  }
}
</script>

<style scoped>
  .modal-container {
    padding: 0px!important;
  }
  .inner-modal-container {
    padding: 0px!important;
  }
  .inner-modal-container__header {
    padding: 30px;
    background-color: rgb(244, 245, 248);
    border-bottom: 1px solid #eee;
  }
  .inner-modal-container__body {
    display: flex;
    flex-direction: column;
    text-align: left;
    padding: 1em 0;
  }
  .inner-modal-container__body > div {
    padding: 1em 2em;
  }
  input:hover, label:hover {
    cursor: pointer;
  }

  .community-logo-placeholder {
    margin: 0 auto 10px;
  }
</style>
