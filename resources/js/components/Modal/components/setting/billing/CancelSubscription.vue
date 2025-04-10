<template>
    <div>
        <div class="flex align-items-center ml-min-05">
            <div class="back-arrow" @click="back">
                <font-awesome-icon icon="fa fa-arrow-left" class="link-icon" />
            </div>
            <div class="tab-content-title mb-0">
                {{ $t('community.members.setting-modal.admin-settings.billings.cancel-subscription') }}
            </div>
        </div>

        <!-- Card number -->
        <div class="mt1">
            <p>
                {{ $t('community.members.setting-modal.admin-settings.billings.cancel-plan-confirm') }}
            </p>
            <ul class="dot-list">
                <li>
                    {{ $t('community.members.setting-modal.admin-settings.billings.archive-plan-1').replace('#community_name', community.name) }}
                </li>
                <li>
                    {{ $t('community.members.setting-modal.admin-settings.billings.archive-plan-2') }}
                </li>
            </ul>

            <div class="danger-text">
                <p>
                    ⚠️ {{ $t('common.warning') }}
                </p>
                <ul class="dot-list ">
                    <li>
                        {{ $t('community.members.setting-modal.admin-settings.billings.archive-plan-3') }}
                    </li>
                    <li>
                        {{ $t('community.members.setting-modal.admin-settings.billings.archive-plan-4') }}
                    </li>
                </ul>
            </div>

            <p>
                {{ $t('community.members.setting-modal.admin-settings.billings.archive-plan-5') }}
            </p>

            <textarea class="textarea mt1"
                :placeholder="$t('community.members.setting-modal.admin-settings.billings.archive-plan-reason')"
                v-model="reason" rows="3" maxlength="500" />
        </div>

        <div class="mt1 text-left mb1 flex">
            <button
                class="button is-medium community-blue-btn"
                :class="updateClass"
                :disabled="!reason || loading"
                @click="handleProcess">
                {{ $t('community.members.setting-modal.admin-settings.billings.cancel-subscription') }}
            </button>
            <button
                class="button is-medium ml1"
                :disabled="loading"
                @click="back">
                {{ $t('community.members.setting-modal.admin-settings.billings.keep-subscription') }}
            </button>
        </div>
    </div>
</template>

<script>
import { BillingSectionType } from "../../../../../data/enums";
export default {
    name: 'CancelSubscription',
    components: {
    },
    props: {
        changeSection: {
            type: Function
        },
    },
    data() {
        return {
            reason: ''
        };
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
        loading() {
            return this.$store.state.community.loading;
        },
    },
    methods: {
        async handleProcess() {
            await this.$store.dispatch('CANCEL_PLAN', {
                communityId: this.community.id,
                reason: this.reason
            });
            this.changeSection(BillingSectionType.OVERVIEW)
        },

        back() {
            this.changeSection(BillingSectionType.MANAGE_SUBSCRIPTION)
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
.dot-list {
    list-style: initial;
    padding: 0 1em;
    margin: 1em;
}

.danger-text {
    color: #e74c3c;
}
</style>
