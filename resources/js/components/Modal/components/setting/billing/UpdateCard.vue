<template>
    <div>
        <div class="flex align-items-center ml-min-05">
            <div class="back-arrow" @click="back">
                <font-awesome-icon icon="fa fa-arrow-left" class="link-icon" />
            </div>
            <div class="tab-content-title mb-0">
                {{ $t('community.members.setting-modal.admin-settings.billings.update-card') }}
            </div>
        </div>

        <!-- Card number -->
        <div class="mt1">
            <div class="card-selection">
                <p class="text-left input-label">
                    {{ $t('community.community.create-modal.card-number') }}
                </p>
                <p class="input-label" v-show="paymentMethods.length > 0">
                    <a class="" @click="toggleNewMethod">
                        {{ isNewMethod ? $t('community.community.create-modal.select-card') : $t('community.community.create-modal.use-new-card') }}
                    </a>
                </p>
            </div>

            <CardElement
                v-if="isNewMethod || paymentMethods.length === 0"
                ref="cardRef"
                :pk="publishableKey"
                @token="tokenCreated"
                @cardElement="cardElementCreated"
                @stripeInstance="stripeInstanceCreated" />

            <select
                v-if="!isNewMethod && paymentMethods.length > 0"
                v-model="paymentMethodId"
                class="input">
                <option
                    v-for="method in paymentMethods"
                    :value="method.id"
                    style="text-transform: capitalize;">
                    {{ method.card_brand }} **** {{ method.last4 }}
                </option>
            </select>
        </div>

        <div class="mt1 text-left mb1 flex">
            <button
                class="button is-medium community-blue-btn"
                :class="updateClass"
                :disabled="loading || !canUpdate"
                @click="handleUpdate">
                {{ $t('common.update') }}
            </button>

            <button
                class="button is-medium ml1"
                :disabled="loading"
                @click="back">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
import { BillingSectionType } from "../../../../../data/enums";
import CardElement from "../../../../Stripe/CardElement";
export default {
    name: 'UpdateCard',
    components: {
        CardElement
    },
    props: {
        changeSection: {
            type: Function
        },
    },
    data() {
        this.publishableKey = stripeKey;
        return {
          isNewMethod: true,
          paymentMethodId: this.$store.state.community.plan.payment_method?.id,
            newPaymentMethod: null,
            cardElement: null,
            stripeInstance: null,
        };
    },
    mounted() {
      if (this.paymentMethods.length > 0 && !this.paymentMethodId) {
        this.paymentMethodId = this.paymentMethods[0].id;
          this.isNewMethod = false;
      }
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },
        paymentMethods() {
          return this.$store.state.auth.user.payment_methods ?? [];
        },
        loading() {
            return this.$store.state.community.loading;
        },
        canUpdate() {
            if (this.isNewMethod === false && !this.paymentMethodId) return false;
            if (this.isNewMethod === true && !this.newPaymentMethod) return false;
            return true;
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
            this.newPaymentMethod = response;
        },
        async cardElementCreated(response) {
            this.cardElement = response;
        },
        async stripeInstanceCreated(response) {
            this.stripeInstance = response;
        },

        async handleUpdate() {
            await this.$store.dispatch('UPDATE_PLAN_PAYMENT_METHOD', {
                communityId: this.community.id,
                cardElement: this.cardElement,
                stripeInstance: this.stripeInstance,
                type: this.type,
                paymentMethod: this.paymentMethod
            });
            this.back();
        },

        toggleNewMethod() {
            this.isNewMethod = !this.isNewMethod
            if (!this.isNewMethod) {
                this.newPaymentMethod = null;
            } else {
                this.paymentMethodId = null;
            }
        },

        back() {
            this.changeSection(BillingSectionType.OVERVIEW)
        },

        /**
         * Returns button status class
         */
        updateClass() {
            return this.loading ? ' is-loading' : '';
        },
    }
}
</script>

<style scoped>
.card-selection {
    display: flex;
    justify-content: space-between;
}
</style>
