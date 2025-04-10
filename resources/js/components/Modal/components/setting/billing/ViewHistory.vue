<template>
    <div class="wrapper">
        <div class="tab-content-title mb-05">
            {{ $t('community.members.setting-modal.admin-settings.billings.view-history') }}
        </div>
        <div>
            <a class="" @click="handleBackToBilling">
                {{ $t('community.members.setting-modal.admin-settings.billings.back-to-billing') }}
            </a>
        </div>

        <div v-if="loading" class="setting-content-loading"></div>
        <div v-else>
            <table class="table is-hoverable is-fullwidth pointer">
                <thead>
                    <tr style="cursor: default;">
                        <th>{{ $t('community.members.setting-modal.admin-settings.billings.amount') }}</th>
                        <th></th>
                        <th></th>
                        <th>{{ $t('community.members.setting-modal.admin-settings.billings.invoice-number') }}</th>
                        <th>{{ $t('community.members.setting-modal.admin-settings.billings.customer-email') }}</th>
                        <th>{{ $t('community.members.setting-modal.admin-settings.billings.created') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in invoices">
                        <td class="no-border">
                            {{ $t(`common.${item.currency}`) }}{{ item.amount_paid / 100 }}
                        </td>
                        <td class="no-border">
                            {{ item.currency.toUpperCase() }}
                        </td>
                        <td class="no-border">
                            {{ item.status.toUpperCase() }}
                        </td>
                        <td class="no-border">
                            {{ item.number }}
                        </td>
                        <td class="no-border">
                            {{ item.customer_email }}
                        </td>
                        <td class="no-border">
                            {{ convertDate(item.created) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import { BillingSectionType, InvoicePageLimit } from "../../../../../data/enums";
export default {
    name: 'BillingOverview',
    props: {
        changeSection: {
            type: Function
        },
    },
    data() {
        return {
            invoices: [],
            page: '',
            limit: InvoicePageLimit,
        };
    },
    mounted() {
        this.loadInvoices()
    },  
    computed: {
        community() {
            return this.$store.state.community.data;
        },
        plan() {
            return this.$store.state.community.plan;
        },
        loading() {
            return this.$store.state.community.loading;
        },
    },
    methods: {
        async loadInvoices() {
            const { data, next_page } = await this.$store.dispatch('GET_PLAN_INVOICES', {
                communityId: this.community.id,
                page: this.page,
                limit: this.limit
            });
            this.invoices = data;
            this.page = next_page;
        },
        handleUpdateMethod() {
            this.changeSection(BillingSectionType.UPDATE_CARD)
        },
        handleManageSubscription() {
            this.changeSection(BillingSectionType.MANAGE_SUBSCRIPTION)
        },
        handleBackToBilling() {
            this.changeSection(BillingSectionType.OVERVIEW)
        },
        convertDate(time){
            return moment(time * 1000).format("LL")
        }
    }
}
</script>

<style scoped>
.wrapper {
    position: relative;
    min-height: 200px;
}
table{
    font-size: 14px;
}
</style>
