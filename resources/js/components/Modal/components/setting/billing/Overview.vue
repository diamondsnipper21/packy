<template>
    <div class="wrapper">
        <div class="tab-content-title mb-05">
            {{ $t('community.members.setting-modal.admin-settings.billing') }}
        </div>

        <div v-if="loading" class="setting-content-loading"></div>
        <div class="mb-05" v-else>
            <div class="mb-05" v-if="!plan.id">
                <div class="danger-text mb1">
                    {{ $t('community.members.setting-modal.admin-settings.billings.no-plan') }}
                </div>

                <button class="button is-medium community-blue-btn" @click="handleReactivate">
                    {{ $t('community.members.setting-modal.admin-settings.billings.activate-membership') }}
                </button>
            </div>
            <div v-else>
                <div v-if="community.status === CommunityStatus.PUBLISHED">
                    <div
                        v-if="plan.status === SubscriptionStatus.ACTIVE || plan.status === SubscriptionStatus.TRIALING">
                        {{ cardText }}
                        <br/>
                        {{ nextPaymentDateText }}
                    </div>
                    <div class="danger-text" v-if="[
                        SubscriptionStatus.PAUSED,
                        SubscriptionStatus.INCOMPLETE,
                        SubscriptionStatus.INCOMPLETE_EXPIRED].includes(plan.status)">
                        ⚠️{{ $t('common.warning') }}
                        <br/>
                        {{ $t('community.members.setting-modal.admin-settings.billings.card-issue') }}
                    </div>
                    <div class="danger-text" v-if="[SubscriptionStatus.PAST_DUE].includes(plan.status)">
                        ⚠️{{ $t('common.warning') }}
                        <br/>
                        {{ $t('community.members.setting-modal.admin-settings.billings.past-due') }}
                    </div>

                    <div class="danger-text"
                         v-if="[SubscriptionStatus.CANCELED, SubscriptionStatus.UNPAID].includes(plan.status)">
                        ⚠️{{ $t('common.warning') }}
                        <br/>
                        {{ cancelPlanText }}
                    </div>

                    <div class="mt1 text-left mb1 flex">
                        <template
                            v-if="![SubscriptionStatus.CANCELED, SubscriptionStatus.UNPAID, SubscriptionStatus.INCOMPLETE_EXPIRED, SubscriptionStatus.INCOMPLETE].includes(plan.status)">
                            <button class="button is-medium community-blue-btn" @click="handleUpdateMethod">
                                {{ $t('community.members.setting-modal.admin-settings.billings.update-payment-method') }}
                            </button>
                            <button class="button is-medium community-blue-btn" @click="handleManageSubscription">
                                {{ $t('community.members.setting-modal.admin-settings.billings.manage-subscription') }}
                            </button>
                        </template>
                    </div>
                </div>

                <div v-else class="danger-text mb1">
                    <p>
                        {{ $t('community.members.setting-modal.admin-settings.billings.community-archived') }}
                    </p>
                </div>

                <div class="mt1 text-left mb1 flex"
                     v-if="plan.status === SubscriptionStatus.CANCELED || plan.status === SubscriptionStatus.INCOMPLETE_EXPIRED || plan.status === SubscriptionStatus.INCOMPLETE">
                    <button class="button is-medium community-blue-btn" @click="handleReactivate">
                        {{ $t('community.members.setting-modal.admin-settings.billings.reactivate') }}
                    </button>
                </div>
            </div>

            <button class="button is-medium community-blue-btn mb1" @click="customizeInvoices">
                {{ $t('community.members.setting-modal.admin-settings.billings.customize-invoices.title') }}
            </button>

            <div class="mt-5">
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.payment-history') }}
                </div>
                <div class="flex mt-05">
                    <UserPlansTransactionsTable :transactions="transactions"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import {SubscriptionStatus, BillingSectionType, CommunityStatus, CommunityPrivacy} from "../../../../../data/enums";
import Button from "../../../../Forms/Button.vue";
import OnboardingStripe from "../../../../OnboardingStripe.vue";
import formatAmountWithCurrency from "../../../../../mixins/util";
import UserPlansTransactionsTable from "../../../../Table/UserPlansTransactionsTable.vue";

export default {
    name: 'BillingOverview',
    props: {
        changeSection: {
            type: Function
        },
    },
    components: {
        UserPlansTransactionsTable,
        OnboardingStripe,
        Button
    },
    mixins: [
        formatAmountWithCurrency
    ],
    data() {
        return {
            SubscriptionStatus,
            CommunityStatus,
            CommunityPrivacy
        }
    },
    mounted() {
        this.loadBilling();
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },
        plan() {
            return this.$store.state.community.plan;
        },
        transactions() {
            return (this.plan ? this.plan.transactions : []);
        },
        loading() {
            return this.$store.state.community.loading;
        },
        cardText() {
            if (!this.plan.id) {
                return '';
            }

            return this.$t('community.members.setting-modal.admin-settings.billings.payment-method-detail', {
                brand: (this.plan.payment_method?.card_brand || '').toUpperCase(),
                last4: this.plan.payment_method?.last4 || ''
            })
        },
        nextPaymentDateText() {
            if (!this.plan.id) return '';
            let text = '';
            const startDate = moment(this.plan.current_period_start);
            const endDate = moment(this.plan.current_period_end);

            if (this.plan.status === SubscriptionStatus.TRIALING) {
                text = this.$t('community.members.setting-modal.admin-settings.billings.trial-end', {
                    days: endDate.diff(startDate, 'days'),
                    date: endDate.format('LL')
                })
            } else {
                text = this.$t('community.members.setting-modal.admin-settings.billings.next-payment', {
                    amount: this.formatAmountWithCurrency(this.plan.currency, this.plan.amount / 100),
                    date: endDate.format('LL')
                })
            }
            text += ' (' + this.formatAmountWithCurrency(this.plan.currency, this.plan.amount / 100) + '/' + this.$t('common.month') + ')';

            return text;
        },
        cancelPlanText() {
            if (!this.plan.id) {
                return ''
            }

            return this.$t('community.members.setting-modal.admin-settings.billings.cancelled-plan', {
                name: this.community.name,
                date: moment(this.plan.current_period_end).format("LL")
            })
        }
    },
    methods: {
        async loadBilling() {
            this.$store.dispatch('GET_COMMUNITY_PLAN', {
                id: this.community.id,
            });
        },
        handleUpdateMethod() {
            this.changeSection(BillingSectionType.UPDATE_CARD)
        },
        handleManageSubscription() {
            this.changeSection(BillingSectionType.MANAGE_SUBSCRIPTION)
        },
        handleReactivate() {
            this.changeSection(BillingSectionType.REACTIVATE)
        },
        customizeInvoices() {
            this.changeSection(BillingSectionType.CUSTOMIZE_INVOICES)
        }
    }
}
</script>

<style scoped>
.wrapper {
    position: relative;
    min-height: 200px;
}

.danger-text {
    color: #e74c3c;
}
</style>
