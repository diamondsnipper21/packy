<template>
    <div class="wrapper">
        <div class="flex align-items-center ml-min-05">
            <div class="back-arrow" @click="back">
                <font-awesome-icon icon="fa fa-arrow-left" class="link-icon" />
            </div>
            <div class="tab-content-title mb-0">
                {{ $t('community.members.setting-modal.admin-settings.billings.manage-subscription') }}
            </div>
        </div>

        <div v-if="loading" class="setting-content-loading"></div>
        <div v-if="!loading">
            <div v-if="!plan.id">
                {{ $t('community.members.setting-modal.admin-settings.billings.no-plan') }}
            </div>
            <div v-if="plan?.id">
                <div>
                    {{ cardText }}
                    <br />
                    {{ nextPaymentDateText }}
                </div>
                <div class="mt1 text-left mb-05">
                    <button class="button is-medium community-blue-btn" @click="handleUpdateMethod">
                        {{ $t('community.members.setting-modal.admin-settings.billings.update-payment-method') }}
                    </button>
                </div>
                <div class="mb-05">
                    <button class="button is-medium community-blue-btn" @click="handleHistory">
                        {{ $t('community.members.setting-modal.admin-settings.billings.view-history') }}
                    </button>
                </div>
                <div class="mb-05">
                    <button class="button is-medium community-blue-btn" @click="handleCancel">
                        {{ $t('community.members.setting-modal.admin-settings.billings.cancel-subscription') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import { SubscriptionStatus, BillingSectionType } from "../../../../../data/enums";
import formatAmountWithCurrency from "../../../../../mixins/util";
export default {
    name: 'ManageSubscription',
    props: {
        changeSection: {
            type: Function
        },
    },
    mixins: [
      formatAmountWithCurrency
    ],
    data() {
        return {
            loading: false,
            cardText: '',
            nextPaymentDateText: ''
        };
    },
    mounted() {
        this.loadBilling()
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        plan() {
            return this.$store.state.community.plan;
        },
    },
    methods: {
        loadBilling() {
            if (this.plan.id) {
                this.cardText = this.$t('community.members.setting-modal.admin-settings.billings.payment-method-detail', {
                  brand: (this.plan.payment_method?.card_brand || '').toUpperCase(),
                  last4: this.plan.payment_method?.last4 || ''
                })

                if (this.plan.subscription) {
                    const startDate = moment(this.plan.subscription.current_period_start);
                    const endDate = moment(this.plan.subscription.current_period_end);

                    if (this.plan.subscription.status === SubscriptionStatus.TRIALING) {
                        this.nextPaymentDateText = this.$t('community.members.setting-modal.admin-settings.billings.trial-end', {
                          days: endDate.diff(startDate, 'days'),
                          date: endDate.format('LL')
                        })
                    } else {
                        this.nextPaymentDateText = this.$t('community.members.setting-modal.admin-settings.billings.next-payment', {
                          amount: this.formatAmountWithCurrency(this.plan.subscription.plan.currency, this.plan.subscription.plan.amount / 100),
                          date: endDate.format('LL')
                        })
                    }
                    this.nextPaymentDateText += ' (' + this.$t('common.' + this.plan.subscription.plan.currency) + (this.plan.subscription.plan.amount / 100) / this.$t('common.month') + ')';
                }
            }
        },
        handleUpdateMethod() {
            this.changeSection(BillingSectionType.UPDATE_CARD)
        },
        handleHistory() {
            this.changeSection(BillingSectionType.VIEW_HISTORY)
        },
        handleCancel() {
            this.changeSection(BillingSectionType.CANCEL_SUBSCRIPTION)
        },
        back() {
            this.changeSection(BillingSectionType.OVERVIEW)
        },
    }
}
</script>

<style scoped>
.wrapper {
    position: relative;
    min-height: 200px;
}
</style>
