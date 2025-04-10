<template>
    <div>
        <div class="stats-title">
            <p class="tab-content-title">
                {{ $t('community.members.setting-modal.admin-settings.stats-content.title') }}
            </p>
            <div class="tab-content-action">
                <label class="mr1">
                    {{ updateTimeText }}
                </label>
                <div class="refresh-action" @click="refreshStats">
                    <span class="refresh-span" title="">
                        <font-awesome-icon icon="fa fa-arrows-rotate" class="tooltip refresh-icon" />
                    </span>
                </div>
            </div>
        </div>
            
        <div class="mt1 font-weight-700">
            {{ $t('community.members.setting-modal.admin-settings.stats-content.subscriptions') }}
        </div>
        <div class="columns ml-min-075 mr-min-075" style="flex-wrap: wrap;">
            <div v-for="subscriptionItem, i in subscriptionItems" :key="subscriptionItem.key" class="column is-one-third" >
                <div class="stats-item-section">
                    <div>
                        <div class="stats-item-value">
                            {{ subscriptionItem.value }}
                        </div>
                        <div class="stats-item-label">
                            {{ $t(`community.members.setting-modal.admin-settings.stats-content.${subscriptionItem.key}`) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt1 font-weight-700">
            {{ $t('community.members.setting-modal.admin-settings.stats-content.audience') }}
            <span class="font-14px">
                ({{ $t('community.members.setting-modal.admin-settings.stats-content.last-7-days') }})
            </span>
        </div>
        <div class="columns ml-min-075 mr-min-075" style="flex-wrap: wrap;">
            <div v-for="trafficItem, i in trafficItems" :key="trafficItem.key" class="column is-one-third" >
                <div class="stats-item-section">
                    <div>
                        <div class="stats-item-value">
                            {{ trafficItem.value }}
                        </div>
                        <div class="stats-item-label">
                            {{ $t(`community.members.setting-modal.admin-settings.stats-content.${trafficItem.key}`) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment'
import formatAmountWithCurrency from "../../../../mixins/util";

export default {
    name: 'StatsSetting',
    mixins: [formatAmountWithCurrency],
    data() {
        return {
            dayOfWeek: '',
            now: 0,
            diff: 0
        };
    },
    created() {
        this.self = this;
    },
    mounted() {
        const d = new Date();
        this.dayOfWeek = this.$t(`common.day-of-week.${d.getDay()}`);

        setInterval(() => {
            this.now = moment().unix();
            this.diff = this.now - this.lastUpdated;
        }, 1000);
    },

    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        currentMember() {
            return this.$store.state.member.data;
        },

        /**
         * Returns community community settings
         */
        communitySettings ()
        {
            return this.$store.state.communitycenter.communitySettings;
        },

        /**
         * Returns stats data
         */
        statsData ()
        {
            return this.communitySettings.statsData;
        },

        /**
         * Returns subscription data
         */
        subscriptions ()
        {
            return this.statsData?.subscriptions || null;
        },

        /**
         * Returns count of paid members
         */
        paidMemberCnt ()
        {
            return this.subscriptions?.paid_members || 0;
        },

        /**
         * Returns monthly recurring revenue
         */
        monthlyRecurringRevenue ()
        {
            return this.subscriptions?.mrr || 0;
        },

        /**
         * Returns churn
         */
        churn ()
        {
            return this.subscriptions?.churn || 0;
        },

        /**
         * Returns traffic data
         */
        traffic ()
        {
            return this.statsData?.traffic || null;
        },

        /**
         * Returns count of visitors
         */
        visitorCnt ()
        {
            return this.traffic?.about_page_visitors || 0;
        },

        /**
         * Returns count of sign up visitors
         */
        signupCnt ()
        {
            return this.traffic?.signups || 0;
        },

        currency () {
            return this.$store.state.auth.currency;
        },

        /**
         * Returns conversion rate
         */
        conversionRate ()
        {
            let conversionRate = 0;
            if (this.visitorCnt > 0) {
                conversionRate = parseFloat(this.signupCnt * 100 / this.visitorCnt);
            }

            return conversionRate;
        },

        /**
         * Returns last updated
         */
        lastUpdated ()
        {
            let lastUpdated = moment().unix();
            if (typeof this.statsData.lastUpdated !== 'undefined' && this.statsData.lastUpdated) {
                lastUpdated = this.statsData.lastUpdated;
            }

            return lastUpdated;
        },

        /**
         * Returns subscription items
         */
        subscriptionItems ()
        {
            return [{
                key: "paid_members",
                value: this.paidMemberCnt
            }, {
                key: "mrr",
                value: this.formatAmountWithCurrency(this.currency, this.monthlyRecurringRevenue),
            }, {
                key: "churn",
                value: this.churn.toFixed(2) + '%'
            }];
        },

        /**
         * Returns traffic items
         */
        trafficItems ()
        {
            return [{
                key: "about_page_visitors",
                value: this.visitorCnt
            }, {
                key: "signups",
                value: this.signupCnt
            }, {
                key: "conversion_rate",
                value: this.conversionRate.toFixed(2) + '%'
            }];
        },

        seconds() {
            return Math.trunc(this.diff) % 60
        },

        minutes() {
            return Math.trunc(this.diff / 60) % 60
        },

        hours() {
            return Math.trunc(this.diff / 60 / 60) % 24
        },

        days() {
            return Math.trunc(this.diff / 60 / 60 / 24)
        },

        diffDate() {
            let diffDate = '';
            if (this.days > 0) {
                diffDate += this.days + this.$t('common.day').charAt(0);
            }

            if (this.hours > 0) {
                if (diffDate !== '') {
                    diffDate += ' ';
                }
                
                diffDate += this.hours + this.$t('common.hour').charAt(0);
            }

            if (this.minutes > 0) {
                if (diffDate !== '') {
                    diffDate += ' ';
                }
                
                diffDate += this.minutes + this.$t('common.minute').charAt(0);
            }

            return diffDate;
        },

        updateTimeText() {
            let updateTimeText = this.$t('community.members.setting-modal.admin-settings.stats-content.update-now');
            if (this.diff > 60) {
                updateTimeText = this.$t('community.members.setting-modal.admin-settings.stats-content.update-ago', { diff_time: this.diffDate });
            }

            return updateTimeText;
        },
    },
    methods: {
        async refreshStats() {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'contentLoading',
                v: true
            });

            await this.$store.dispatch('GET_ADMIN_STATS_DATA');

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'contentLoading',
                v: false
            });
        },
    }
}
</script>

<style scoped>
.stats-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.tab-content-action {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.tab-content-action label {
    font-style: italic;
    font-size: 13px;
}

.refresh-action {
    border-radius: 50%;
    width: 35px;
    height: 36px;
    padding: 6px;
    cursor: pointer;
    border: 1px solid #ddd;

    &:hover {
        background-color: rgb(228, 228, 228);
    }
}

.refresh-span {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
}

.stats-item-section {
    border-radius: 6px;
    border: 1px solid rgb(228, 228, 228);
    background-color: rgb(248, 247, 245);
    min-height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-item-value {
    font-size: 22px;
    font-weight: bold;
    text-align: center;
}

.stats-item-label {
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    color: rgb(144, 144, 144);
}


@media only screen and (max-width: 600px) {
    .stats-title {
        display: block;
    }

    .stats-item-value {
        font-size: 20px;
    }

    .stats-item-label {
        font-size: 13px;
    }
}
</style>
