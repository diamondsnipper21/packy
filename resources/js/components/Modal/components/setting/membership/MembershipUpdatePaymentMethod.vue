<template>
  <div class="wrapper">
    <div class="tab-content-title mb-05">
      {{ $t('community.members.setting-modal.membership-update-payment-method.title') }}
    </div>

    <div class="mt1">
      <div class="flex">
        <!-- Card number -->
        <div class="flex-1">
          <div class="card-selection">
            <p class="text-left input-label font-weight-700">
              {{ $t('community.community.create-modal.card-number') }}
            </p>
          </div>

          <CardElement
              v-if="isNewMethod || paymentMethods.length === 0"
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
              {{ method.card_brand.toUpperCase() }} **** {{ method.last4 }}
            </option>
          </select>
          <div>
            <p class="input-label" v-if="paymentMethods.length > 0">
              <a class="font-14px" @click="toggleNewMethod">
                {{ isNewMethod ? $t('community.community.create-modal.select-card') : $t('community.community.create-modal.use-new-card') }}
              </a>
            </p>
          </div>
        </div>
      </div>

      <div>
        <button class="button is-medium mt1 community-blue-btn is-fullwidth"
                @click="saveNewPaymentMethod()"
                :disabled="processing || !stripeToken">
          {{ $t('common.save') }}
        </button>
      </div>

      <div>
        <button class="button is-medium mt1 community-blue-btn is-fullwidth"
                @click="backToMembership()"
                :disabled="processing">
          {{ $t('common.go-back') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import CardElement from "../../../../Stripe/CardElement.vue";

export default {
  name: 'MembershipUpdatePaymentMethod',
  components: {CardElement},
  data() {
    return {
      publishableKey: stripeKeyMarketplace,
      processing: false,
      stripeToken: null,
        cardElement: null,
        stripeInstance: null,
      isNewMethod: false,
      paymentMethodId: 0
    }
  },
  computed: {
    community() {
      return this.$store.state.community.data;
    },

    member() {
      return this.$store.state.member.data;
    },

    paymentMethods() {
      return this.member.payment_methods ?? [];
    },

    stripeAccountId() {
      return this.community.user.stripe_account?.account_id
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
      this.stripeToken = response.id;
    },
      async cardElementCreated(response) {
          this.cardElement = response;
      },
      async stripeInstanceCreated(response) {
          this.stripeInstance = response;
      },

    async saveNewPaymentMethod() {
      this.processing = true;

        await this.$store.dispatch('UPDATE_SUBSCRIPTION_PAYMENT_METHOD', {
            communityId: this.community.id,
            subscriptionId: this.member.subscription.id,
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

      backToMembership() {
          this.$store.commit('setModalExtraData', 'membership');
      },
  }
}
</script>

<style scoped>

</style>
