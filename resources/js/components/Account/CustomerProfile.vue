<template>
    <div class="box cc-settings-container">
        <p class="title is-4 mb1">{{ $t('community.profile.title') }}</p>
        <p class="subtitle is-6 mb1">{{ $t('community.profile.subtitle') }}</p>

        <div v-if="Object.keys(this.errors).length > 0" class="notification is-danger mb1">
            <p class="mb1">{{ $t('account.error') }}</p>
            <div v-for="(key, index) in Object.keys(this.errors)" :key="index">
                <p>{{ getFirstError(key) }}</p>
            </div>
        </div>

        <form method="post" @submit.prevent="submit">

            <!-- First name -->
            <div class="control">
                <p class="mb-05">{{ $t('community.profile.first-name') }}</p>
                <input
                    class="input mb1 is-medium"
                    :placeholder="this.$t('community.profile.first-name')"
                    type="text"
                    v-model="first"
                />
            </div>

            <!-- Last name -->
            <div class="control">
                <p class="mb-05">{{ $t('community.profile.last-name') }}</p>
                <input
                    class="input mb1 is-medium"
                    :placeholder="this.$t('community.profile.last-name')"
                    type="text"
                    v-model="last"
                />
            </div>

            <!-- Email -->
            <div class="control">
                <p :class="errorsExist('email') ? 'mb-05 cc-error' : 'mb-05'">{{ $t('community.profile.email') }}</p>
                <input
                    :class="errorsExist('email') ? 'input mb1 is-medium is-danger' : 'input mb1 is-medium'"
                    :placeholder="this.$t('community.profile.email')"
                    type="email"
                    v-model="email"
                    required
                    @input="clearErrors('email')"
                />
                <span class="cc-error"
                    v-if="errorsExist('email')"
                    v-text="getFirstError('email')"
                />
            </div>

            <!-- Language -->
            <div class="control mb1">
                <p class="mb-05">{{ $t('general.language.label') }}</p>
                <select v-model="language" class="input">
                    <option v-for="lang in languages" :value="lang">
                        {{ translateLanguageKey(lang) }}
                    </option>
                </select>
            </div>

            <!-- How customer wants to get paid if they are a partner -->
            <div class="mb1" v-show="ifPartner">
                <p class="mb1">{{ $t('account.commissions-payment-method') }}</p>

                <div class="flex mb1">
                    <input type="checkbox" v-model="paypalSelected" class="cc-checkbox" id="cc-paypal" />
                    <label for="cc-paypal" class="pointer">PayPal</label>
                </div>

                <div class="flex mb1">
                    <input type="checkbox" v-model="ibanSelected" class="cc-checkbox" id="cc-cb" />
                    <label for="cc-cb" class="pointer">IBAN</label>
                </div>

                <div v-show="paypalSelected">
                    <p class="mb1">{{ $t('account.enter-paypal') }}</p>

                    <input class="input" type="email" v-model="paypal" :placeholder="$t('auth.your-email')" />
                </div>

                <div v-show="ibanSelected">
                    <p class="mb1">{{ $t('account.bank-transfer') }}</p>

                    <input class="input" type="text" placeholder="IBAN" v-model="iban" />
                </div>
            </div>

            <button
                class="button is-large community-blue-btn fl-r cc-hov"
                :class="processing ? 'is-loading' : ''"
                :disabled="processing"
            >{{ $t('common.save') }}</button>
        </form>
    </div>
</template>

<script>
import { languages } from "../../data/languages";

export default {
    name: 'CustomerProfile',
    data () {
        return {
            languages,
            processing: false
        };
    },
    computed: {
        /**
         * Shortcut for customer
         */
        customer ()
        {
            return this.$store.state.communitycenter.customer;
        },

        /**
         * The users email address
         */
        email: {
            get () {
                return this.customer.email;
            },
            set (v) {
                this.$store.commit('customerEmail', v);
            }
        },

        /**
         * Any errors from backend form validation
         */
        errors ()
        {
            return this.$store.state.communitycenter.errors;
        },

        /**
         * The users first name
         */
        first: {
            get () {
                return this.customer.firstname;
            },
            set (v) {
                this.$store.commit('customerFirstName', v);
            }
        },

        /**
         * Check if the customer is an affiliate partner
         */
        ifPartner ()
        {
            return (this.customer.partner === "on");
        },

        /**
         * The contacts language
         */
        language: {
            get () {
                return this.customer.contact?.language;
            },
            set (v) {
                this.$store.commit('changeCustomerContactLanguage', v);
            }
        },

        /**
         * The users last name
         */
        last: {
            get () {
                return this.customer.lastname;
            },
            set (v) {
                this.$store.commit('customerLastName', v);
            }
        },

        /**
         * The customer.partners IBAN for payouts
         */
        iban: {
            get () {
                return this.$store.state.communitycenter.affSettings.iban;
            },
            set (v) {
                this.$store.commit('customerIban', v);
            }
        },

        /**
         * Select/de-select Iban checkbox
         */
        ibanSelected: {
            get () {
                return this.$store.state.communitycenter.affSettings.ibanSelected;
            },
            set (v) {
                this.$store.commit('customerIbanSelected', v);
            }
        },

        /**
         * Email value of the customer.partners paypal account
         */
        paypal: {
            get () {
                return this.$store.state.communitycenter.affSettings.paypal;
            },
            set (v) {
                this.$store.commit('customerPayPalEmail', v);
            }
        },

        /**
         * Select/de-select PayPal checkbox
         */
        paypalSelected: {
            get () {
                return this.$store.state.communitycenter.affSettings.paypalSelected;
            },
            set (v) {
                this.$store.commit('customerPayPalSelected', v);
            }
        },
    },
    methods: {
        /**
         * Clear any errors
         */
        clearErrors (key)
        {
            this.$store.commit('clearCommunityCenterErrors', key);
        },

        /**
         * Check if any errors exist for this key
         */
        errorsExist (key)
        {
            return this.errors.hasOwnProperty(key);
        },

        /**
         * Get specific errors for this error key
         */
        getFirstError (key)
        {
            return this.errors[key][0];
        },

        /**
         * Update the customers data
         *
         * and contact language
         */
        async submit ()
        {
            this.processing = true;

            await this.$store.dispatch('UPDATE_CUSTOMER_ACCOUNT');

            this.processing = false;
        },

        /**
         * Translate one of the keys into its respective value
         */
        translateLanguageKey (languageKey)
        {
            return (languageKey !== null)
                ? this.$t('general.language.' + languageKey)
                : this.$t('general.language.' + languageKey, {
                    default: this.$t('general.language.' + this.$store.state.communitycenter.settings.language)
                });
        }
    }
}
</script>
