<template>
    <div class="inner-modal-container">

        <p class="new-community-title">
            {{ $t('community.community.create-modal.title') }}
        </p>

        <div class="new-community-desc">
            {{ $t('community.community.create-modal.desc-1') }}
        </div>

        <div class="flex mt1">
            <!-- Name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.create-modal.group-name') }}
                </p>
                <input
                    class="input"
                    required
                    :placeholder="$t('community.community.create-modal.group-name')"
                    v-model="name"
                    ref="name" />
            </div>
        </div>

        <!--
        <div class="flex mt1">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.members.setting-modal.admin-settings.url') }}
                </p>
                <input
                    class="input"
                    :placeholder="$t('community.members.setting-modal.admin-settings.url')"
                    v-model="url"
                    @keypress="validateUrl($event)" />
            </div>
        </div>
        -->

        <div class="flex mt1">
            <!-- Card number -->
            <div class="flex-1">
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
                    v-show="isNewMethod || paymentMethods.length === 0"
                    ref="cardRef"
                    :pk="publishableKey"
                    @token="tokenCreated"
                    @cardElement="cardElementCreated"
                    @stripeInstance="stripeInstanceCreated"/>

                <select
                    v-show="!isNewMethod && paymentMethods.length > 0"
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
        </div>

        <div class="terms-section text-center" v-html="$t('community.community.create-modal.term-desc', { date: date, amount: '79€', termLink: termLink })"></div>

        <button
            class="button is-large community-blue-btn w100 mt1"
            :class="btnClass"
            @click="createCommunity"
            :disabled="loading || !canUpdate">
          {{ $t('community.community.create-modal.start-trial-btn') }}
        </button>
    </div>
</template>

<script>
import CardElement from '../Stripe/CardElement.vue'
import slugify from '@sindresorhus/slugify'
import validateUrl from "../../mixins/util";
import { IncubateurStartStep } from '../../data/enums';
import moment from "moment/moment";

export default {
    name: 'NewCommunity',
    mixins: [validateUrl],
    components: {
        CardElement
    },
    data() {
        return {
            publishableKey: stripeKey,
            disabledConfirm: true,
            groupName: '',
            groupUrl: '',
            isNewMethod: true,
            paymentMethodId: 0,
            incubateurStart: false,
            IncubateurStartStep,
            termLink: null,
            newPaymentMethod: null,
            cardElement: null,
            stripeInstance: null,
        }
    },
    mounted() {
        // For only 'INCUBATEUR' start process
        let params = this.$route.path.split("/");
        if (typeof params[1] !== 'undefined' && params[1].toUpperCase() === 'INCUBATEUR' && typeof params[2] !== 'undefined' && params[2] === 'start') {
            this.incubateurStart = true;
        }
        if (this.paymentMethods.length > 0 && !this.paymentMethodId) {
            this.paymentMethodId = this.paymentMethods[0].id;
            this.isNewMethod = false;
        }

        this.termLink = '<a href="/legal" target="_blank">' + this.$t('community.community.create-modal.terms') + '</a>';
    },
    computed: {

        /**
         * Get | Set name
         */
        name: {
            get() {
                return this.groupName;
            },
            set(v) {
                this.groupName = v
                this.groupUrl = slugify(v);
                if (v === '') {
                    this.disabledConfirm = true;
                } else if (this.url !== null && this.url !== '') {
                    this.disabledConfirm = false;
                }
            }
        },

        loading() {
            return this.$store.state.community.loading;
        },

        btnClass() {
            return this.loading ? 'is-loading' : '';
        },

        date() {
            return moment().add(14, 'days').format('LL')
        },

        user() {
          return this.$store.state.auth.user;
        },

        paymentMethods() {
          return this.user.payment_methods ?? [];
        },

        /**
         * Get | Set url
         */
        url: {
            get() {
                return this.groupUrl;
            },
            set(v) {
                this.groupUrl = v;
                if (v === '') {
                    this.disabledConfirm = true;
                } else if (this.name !== null && this.name !== '') {
                    this.disabledConfirm = false;
                }
            }
        },

        canUpdate() {
            if (!this.groupName) return false;
            // if (!this.groupUrl) return false;

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

        createCommunity() {
            this.$store.commit('setCommunityLoading', true);

            this.$store.dispatch('CREATE_COMMUNITY', {
                incubateurStart: this.incubateurStart,
                name: this.name,
                url: this.url,
                cardElement: this.cardElement,
                stripeInstance: this.stripeInstance,
                type: this.type,
                paymentMethod: this.paymentMethod
            });
        },

        toggleNewMethod() {
            this.isNewMethod = !this.isNewMethod
            if (!this.isNewMethod) {
                this.newPaymentMethod = null;
            } else {
                this.paymentMethodId = null;
            }
        }
    }
}
</script>

<style scoped>
.new-community-title {
    font-size: 24px;
    font-weight: bold;
}

.privacy-section {
    display: flex;
    border-radius: 4px;
    border: 1px solid rgb(228, 228, 228);
}

.privacy-section-item {
    width: 100%;
    padding: 15px;
    background-color: rgb(248, 247, 245);
    cursor: pointer;
}

.privacy-section-item.private {
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
}

.privacy-section-item.public {
    border-left: 1px solid rgb(228, 228, 228);
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}

.privacy-section-item.selected-item {
    background: transparent;
}

.privacy-title-section {
    display: flex;
    align-items: center;
}

.privacy-desc {
    font-size: 13px;
    margin-top: 10px;
    text-align: left;
}

.terms-section {
    font-size: 14px;
    text-align: left;
    margin-top: 20px;
}

.card-selection {
    display: flex;
    justify-content: space-between;
}

@media only screen and (max-width: 600px) {
    .new-community-title {
        font-size: 18px;
    }

    .privacy-section-item {
        padding: 10px;
    }

    .privacy-desc {
        font-size: 12px;
        margin-top: 5px;
    }

    .terms-section {
        font-size: 13px;
        margin-top: 10px;
    }
}
</style>
