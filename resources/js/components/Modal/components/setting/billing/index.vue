<template>
    <div>
        <Overview v-if="section === BillingSectionType.OVERVIEW" :changeSection="handleSection" />
        <UpdateCard v-if="section === BillingSectionType.UPDATE_CARD" :changeSection="handleSection"/>
        <Subscriptions v-if="section === BillingSectionType.MANAGE_SUBSCRIPTION" :changeSection="handleSection"/>
        <CancelSubscription v-if="section === BillingSectionType.CANCEL_SUBSCRIPTION" :changeSection="handleSection"/>
        <ViewHistory v-if="section === BillingSectionType.VIEW_HISTORY" :changeSection="handleSection"/>
        <Reactivate v-if="section === BillingSectionType.REACTIVATE" :changeSection="handleSection"/>
        <CustomizeInvoices v-if="section === BillingSectionType.CUSTOMIZE_INVOICES" :changeSection="handleSection"/>
    </div>
</template>

<script>
import Overview from "./Overview";
import Subscriptions from "./Subscriptions";
import UpdateCard from "./UpdateCard";
import CancelSubscription from "./CancelSubscription";
import ViewHistory from "./ViewHistory";
import Reactivate from "./Reactivate";
import { BillingSectionType } from "../../../../../data/enums";
import CustomizeInvoices from "./CustomizeInvoices.vue";

export default {
    name: 'Billing',
    components: {
      CustomizeInvoices,
        Overview,
        UpdateCard,
        Subscriptions,
        CancelSubscription,
        ViewHistory,
        Reactivate,
    },
    data() {
        return {
            BillingSectionType
        };
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns community community settings
         */
        communitySettings ()
        {
            return this.$store.state.communitycenter.communitySettings;
        },

        /**
         * Returns setting group show
         */
        section ()
        {
            return this.communitySettings.billingSection;
        },
    },
    methods: {
        handleSection(section) {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'billingSection',
                v: section
            });
        }
    }
}
</script>

<style scoped></style>
