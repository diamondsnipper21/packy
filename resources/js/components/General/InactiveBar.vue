<template>
    <div class="inactive-bar" v-if="barType !== BarType.NONE">
        <span>{{ description }}</span>
        <button
            class="button is-medium is-dark ml1"
            @click="handleAction"
            v-if="displayButton">
            {{ buttonText }}
        </button>
    </div>
</template>

<script>
import { CommunityStatus, SubscriptionStatus } from '../../data/enums';
import moment from "moment";
const BarType = {
    NONE: 0,
    INACTIVE: 1,
    PLAN_CANCELED: 2,
    PLAN_PAUSED: 3,
    PLAN_INCOMPLETE: 4,
    PLAN_PAST_DUE: 5,
    PLAN_NOT_FOUND: 6
}
export default {
    name: 'InactiveBar',
    components: {
    },
    data() {
        return {
            CommunityStatus,
            SubscriptionStatus,
            BarType
        };
    },
    computed: {
        displayButton() {
          return [BarType.INACTIVE, BarType.PLAN_CANCELED, BarType.PLAN_PAUSED, BarType.PLAN_INCOMPLETE, BarType.PLAN_PAST_DUE, BarType.PLAN_NOT_FOUND].includes(this.barType);
        },

        community() {
            return this.$store.state.community.data;
        },
        plan() {
            return this.$store.state.community.plan;
        },
        /**
         * Returns user
         */
        user() {
            return this.$store.state.auth.user;
        },
        barType() {
            if (this.user.id === 1) {
                return BarType.NONE;
            }
            if (!this.plan.id) {
                return BarType.PLAN_NOT_FOUND;
            }
            if (this.community.status === CommunityStatus.INACTIVE) {
                return BarType.INACTIVE;
            }

            if (this.community.status === CommunityStatus.PUBLISHED || this.community.status === CommunityStatus.SUSPENDED) {
                switch (this.plan?.status) {
                    case SubscriptionStatus.CANCELED:
                    case SubscriptionStatus.UNPAID:
                        return BarType.PLAN_CANCELED;
                    case SubscriptionStatus.PAUSED:
                        return BarType.PLAN_PAUSED;
                    case SubscriptionStatus.INCOMPLETE:
                    case SubscriptionStatus.INCOMPLETE_EXPIRED:
                        return BarType.PLAN_INCOMPLETE;
                    case SubscriptionStatus.PAST_DUE:
                        return BarType.PLAN_PAST_DUE;
                }
            }

            return BarType.NONE;
        },
        description() {
            switch (this.barType) {
                case BarType.INACTIVE:
                    return this.$t('community.inactive.desc');
                case BarType.PLAN_CANCELED:
                    const endDate = moment(this.plan.current_period_end);
                    return this.$t('community.members.setting-modal.admin-settings.billings.cancelled-plan', {
                      name: this.community.name,
                      date: endDate.format("LL")
                    })
                case BarType.PLAN_INCOMPLETE:
                case BarType.PLAN_PAUSED:
                    return this.$t('community.members.setting-modal.admin-settings.billings.card-issue');
                case BarType.PLAN_PAST_DUE:
                    return this.$t('community.members.setting-modal.admin-settings.billings.past-due');
                case BarType.PLAN_NOT_FOUND:
                    return this.$t('community.members.setting-modal.admin-settings.billings.no-plan');
            }
            return ''
        },
        buttonText() {
            switch (this.barType) {
                case BarType.INACTIVE:
                    return this.$t('community.inactive.button');
                case BarType.PLAN_INCOMPLETE:
                case BarType.PLAN_PAUSED:
                case BarType.PLAN_PAST_DUE:
                case BarType.PLAN_NOT_FOUND:
                    return this.$t('community.members.setting-modal.admin-settings.billings.click-here');

                case BarType.PLAN_CANCELED:
                  return this.$t('community.members.setting-modal.admin-settings.billings.reactivate-button');
            }
            return ''
        }
    },
    methods: {
        reInitSettings() {
            this.$store.commit('cloneCommunity', JSON.parse(JSON.stringify(this.$store.state.community.data)));
            this.$store.commit('resetDropzoneError');
        },
        handleAction() {
            this.reInitSettings();
            this.$store.commit('showModal', {
                type: 'CommunitySetting',
                extraData: 'billing',
                transparent: true
            });
        }
    }
}
</script>
<style lang="scss" scoped>
.inactive-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 60px;
    background-color: #f14668;
    border-color: transparent;
    color: #fff;
    position: fixed;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 1em;
    text-align: center;
}

@media only screen and (max-width: 600px) {
    .inactive-bar {
        font-size: 12px;
    }
}
</style>