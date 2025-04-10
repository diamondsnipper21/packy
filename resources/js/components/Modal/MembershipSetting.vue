<template>
    <div class="community-setting h100-percent" @click="outclick">
        <div class="settings-header">
            <div class="flex align-items-center">
                <img :src="getMemberGravatar(selectedMember)" class="community-customer-gravatar" />
                <div class="ml1">
                    <div class="member-name ml-0">
                        {{ getUserName(selectedMember.user) }}
                    </div>
                    <div class="membership-settings">
                        {{ $t('community.members.setting-modal.membership-settings') }}
                    </div>
                </div>
            </div>

            <!-- Close button -->
            <font-awesome-icon icon="fa fa-times" class="pointer" @click="close" />
        </div>

        <div class="columns settings-content">
            <div class="column is-one-quarter tab-column" id="tab_link_section">
                <div v-for="tab, i in regularTabs" class="tab-link" :class="checkSideTab(tab)"
                    @click="selectTab(tab)">
                    {{ $t(`community.members.setting-modal.${tab}`) }}
                </div>
            </div>

            <!-- Content -->
            <div class="column tab-content relative">
                <div v-if="contentLoading" class="setting-content-loading"></div>
                <div v-else>
                    <Membership v-if="extraData === MembershipSettingViewType.MEMBERSHIP" />
                    <MembershipRole v-if="extraData === MembershipSettingViewType.MEMBERSHIP_ROLE" />
                    <Payments v-if="extraData === MembershipSettingViewType.PAYMENTS"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment';
import Membership from "./components/membershipSetting/Membership";
import MembershipRole from "./components/membershipSetting/MembershipRole";
import Payments from "./components/membershipSetting/Payments";

import getUserName from "../../mixins/util";
import getMemberGravatar from "../../mixins/util";

import { MembershipSettingViewType } from '../../data/enums';

export default {
    name: 'MembershipSetting',
    mixins: [
        getUserName,
        getMemberGravatar
    ],
    components: {
        Membership,
        MembershipRole,
        Payments
    },
    data() {
        return {
            MembershipSettingViewType,
            regularTabs: [],
            processing: false,
            loading: false
        };
    },
    mounted() {
        moment.locale(this.$i18n.locale);
        this.regularTabs = [MembershipSettingViewType.MEMBERSHIP, MembershipSettingViewType.PAYMENTS];
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * extra data of child modal
         */
        extraData() {
            return this.$store.state.modal.extraData;
        },

        /**
         * Returns current member
         */
        currentMember() {
            return this.$store.state.member.data;
        },

        /**
         * Returns membership settings
         */
        membershipSettings() {
            return this.$store.state.member.membershipSettings;
        },

        /**
         * Returns selected member
         */
        selectedMember() {
            return this.membershipSettings?.member;
        },

        /**
         * Returns content loading
         */
        contentLoading() {
            return this.membershipSettings?.contentLoading;
        },
    },
    methods: {
        /**
         * Class to show for clicked tab
         */
        checkSideTab(name) {
            return (this.extraData === name || (this.extraData === MembershipSettingViewType.MEMBERSHIP_ROLE && name === MembershipSettingViewType.MEMBERSHIP)) ? 'clicked' : '';
        },

        /**
         * Select tab click handler
         */
        selectTab(tab) {
            this.$store.commit('setMembershipSettingsProperty', {
                key: 'contentLoading',
                v: true
            });

            this.$store.commit('setModalExtraData', tab);

            this.$store.commit('setMembershipSettingsProperty', {
                key: 'contentLoading',
                v: false
            });
        },

        outclick(e) {

        },

        /**
         * Close the modal
         */
        close() {
            this.$store.commit('hideModal');
        }
    }
}
</script>

<style scoped>

.settings-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 25px;
    border-bottom: 1px solid rgb(228, 228, 228);
}

.settings-content {
    height: calc(100% - 80px);
    overflow: auto;
}

.membership-settings {
    font-size: 14px;
    color: rgb(144, 144, 144);
    text-align: left;
}

.member-name {
    text-align: left;
    font-weight: bold;
}

.settings-modal-content::-webkit-scrollbar {
    background-color: #f4f5f8;
    width: 5px;
}

.settings-modal-content::-webkit-scrollbar-thumb {
    background-color: #c0c0c0;
    border: 0.25em solid #c0c0c0;
    border-radius: 1em;
}

.tab-link {
    padding: 10px 15px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background 300ms ease 0s;
    text-align: left;
}

.tab-link:hover {
    background-color: rgb(228, 228, 228);
}

.tab-link.clicked {
    color: #fff;
    background-color: #9198FF;
}

@media only screen and (max-width: 600px) {
    .settings-header {
        padding: 10px;
    }

    .settings-content {
        height: calc(100% - 50px);
    }

    .membership-settings {
        font-size: 13px;
    }

    .tab-link {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        margin-bottom: 5px;
    }
}
</style>