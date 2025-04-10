<template>
    <div class="community-setting h100-percent" @click="outclick">
        <div class="settings-header">
            <div v-if="typeof this.community.name !== 'undefined'" class="community-logo-section">
                <img v-if="communityLogo !== '' && communityLogo !== null" :src="communityLogo"
                    class="community-logo" />
                <div v-else class="community-logo-placeholder">
                    {{ logoPlaceholder(community.name) }}
                </div>
                <div class="ml1">
                    <div class="community-name ml-0">
                        {{ this.community.name }}
                    </div>
                    <div class="membership-settings">
                        {{ $t('community.members.setting-modal.group-settings') }}
                    </div>
                </div>
            </div>
            <img v-else-if="this.settings?.logo_dark" :src="this.settings?.logo_dark"
                class="navbar-item main-logo pointer" />
            <div v-else class="mtba">
                <div class="community-name">
                    {{ this.settings.company_name }}
                </div>
                <div class="membership-settings">
                    {{ $t('community.members.setting-modal.group-settings') }}
                </div>
            </div>

            <!-- Close button -->
            <font-awesome-icon icon="fa fa-times" class="pointer" @click="close" />
        </div>

        <div class="columns settings-content">
            <div class="column is-one-quarter tab-column" id="tab_link_section">
                <!-- Tabs -->
                <div v-if="isManager(role)">
                    <div class="setting-label-section">
                        {{ $t('community.members.setting-modal.admin-settings.user-settings') }}
                    </div>
                    <div class="setting-content-section">
                        <div v-for="tab, i in adminUserTabs" class="tab-link" :class="checkSideTab(tab)"
                            @click="selectTab(tab)">
                            {{ $t(`community.members.setting-modal.${tab}`) }}
                        </div>
                    </div>

                    <div class="setting-label-section">
                        {{ $t('community.members.setting-modal.admin-settings.admin-settings') }}
                    </div>
                    <div class="setting-content-section">
                        <div v-for="tab, i in adminSettingTabs" class="tab-link" :class="checkSideTab(tab)"
                            @click="selectTab(tab)">
                            {{ $t(`community.members.setting-modal.admin-settings.${tab}`) }}
                        </div>
                    </div>
                </div>

                <div v-else-if="access === MemberAccess.ALLOWED">
                    <div v-for="tab, i in regularTabs" class="tab-link" :class="checkSideTab(tab)"
                        @click="selectTab(tab)">
                        {{ $t(`community.members.setting-modal.${tab}`) }}
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="column tab-content relative">
                <div v-if="contentLoading" class="setting-content-loading"></div>
                <div v-else>
                    <div v-if="extraData === 'membership'">
                        <p class="tab-content-title">
                            {{ $t('community.members.setting-modal.membership') }}
                        </p>

                        <p v-if="community.user_id === member.user_id" class="tab-content-desc" v-html="$t('community.members.setting-modal.ownership-desc').replace('#community_name#', communityName).replace('#created_at#', createdAt(member.created_at))"
                        ></p>
                        <p v-else class="tab-content-desc" v-html="$t('community.members.setting-modal.membership-desc').replace('#community_name#', communityName).replace('#created_at#', createdAt(member.created_at))"
                        ></p>

                      <div v-if="member.subscription" class="mb1">
                        <template v-if="member.subscription.cancel">
                          <div v-html="$t('community.members.setting-modal.your-membership-will-end-at', { date: createdAt(member.subscription.next_billing_at) })"/>
                          <button class="button is-medium mt1 community-blue-btn mb1 is-fullwidth" @click="subscriptionReactivate()" :class="button">
                            {{ $t('community.members.setting-modal.reactivate-membership') }}
                          </button>
                        </template>

                        <template v-else>
                          <div class="font-14px"
                               v-html="$t('community.members.setting-modal.next-payment') + ': ' + createdAt(member.subscription.next_billing_at)"/>
                          <div>
                            <button class="button is-medium mt1 community-blue-btn is-fullwidth" @click="updatePaymentMethod">
                              {{ $t('community.members.setting-modal.update-payment-method') }}
                              <template v-if="member.subscription.payment_method">({{ member.subscription.payment_method.card_brand.toUpperCase() }} **{{ member.subscription.payment_method.last4 }})</template>
                            </button>
                          </div>
                          <div v-if="member.subscription.period === 'monthly' && member.subscription.price.stripe_price_id_yearly !== null">
                            <button class="button is-medium mt1 community-blue-btn is-fullwidth" @click="upgradeToAnnual" disabled>
                              {{ $t('community.members.setting-modal.upgrade-to-annual') }}
                              <template v-if="discount > 0">({{ $t('community.community.subscription-purchase-modal.save-x-percent', { discount: discount }) }})</template>
                            </button>
                          </div>
                          <div>
                            <button class="button is-medium mt1 community-blue-btn is-fullwidth" @click="manageMembership">
                              {{ $t('community.members.setting-modal.manage-membership') }}
                            </button>
                          </div>
                        </template>
                      </div>

                      <div class="mb1 text-left" v-if="!member.subscription || member.subscription.cancel">
                        <button 
                            v-if="memberExist" 
                            class="button is-medium mt1 community-btn is-fullwidth" 
                            :disabled="community.user_id === member.user_id"
                            @click="leaveGroup"
                        >
                          {{ $t('community.members.setting-modal.leave-this-group') }}
                        </button>
                      </div>
                    </div>

                    <PaymentHistory v-if="extraData === 'payments'"/>
                    <CancelMembership v-if="extraData === 'manage-membership'"/>
                    <MembershipCancelled v-if="extraData === 'membership-cancelled'"/>
                    <MembershipUpdatePaymentMethod v-if="extraData === 'manage-membership-update-payment-method'"/>
                    <Payouts v-if="extraData === 'payouts'"/>

                    <div v-if="extraData === 'security'">
                        <p class="tab-content-title">
                            {{ $t('community.members.setting-modal.security') }}
                        </p>
                        <p class="tab-content-desc">
                            {{ $t('community.members.setting-modal.security-desc') }}
                        </p>

                        <div class="form">
                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.current-password') }}
                                    </p>
                                    <input
                                        class="input"
                                        :placeholder="$t('community.members.setting-modal.current-password')"
                                        type="password"
                                        v-model="currentPassword" />
                                </div>
                            </div>

                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.new-password') }}
                                    </p>
                                    <input
                                        class="input"
                                        :placeholder="$t('community.members.setting-modal.new-password')"
                                        type="password"
                                        v-model="newPassword" />
                                </div>
                            </div>

                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.confirm-password') }}
                                    </p>
                                    <input
                                        class="input"
                                        :placeholder="$t('community.members.setting-modal.confirm-password')"
                                        type="password"
                                        v-model="confirmPassword" />
                                </div>
                            </div>

                            <div v-if="disabledReset" class="mt1 password-not-match-error">
                                {{ $t('community.members.setting-modal.password-not-match') }}
                            </div>

                            <div class="submit-button mt1">
                                <button
                                    class="button is-medium community-blue-btn text-uppercase"
                                    :class="button"
                                    :disabled="newPassword === '' || disabledReset" @click="resetPassword">
                                    {{ $t('common.save') }}</button>
                            </div>
                        </div>
                    </div>

                    <div v-if="extraData === 'notifications'">
                        <p class="tab-content-title">
                            {{ $t('nav.notifications') }}
                        </p>

                        <div class="form">
                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.digest-email-title') }}
                                    </p>
                                    <p class="input-description">
                                        {{ $t('community.members.setting-modal.digest-email-desc') }}
                                    </p>
                                    <select class="input" v-model="popularInterval">
                                        <option
                                            v-for="popularIntervalOpt in popularIntervalOpts"
                                            :value="popularIntervalOpt"
                                            :selected="popularInterval === popularIntervalOpt">
                                            {{ $t(`community.members.setting-modal.digest-email-options.${popularIntervalOpt}`) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.likes-title') }}
                                    </p>
                                    <p class="input-description">
                                        {{ $t('community.members.setting-modal.likes-desc') }}
                                    </p>
                                    <select class="input" v-model="likes">
                                        <option
                                            v-for="likesOpt in likesOpts"
                                            :value="likesOpt"
                                            :selected="likes === likesOpt">
                                            {{ $t(`community.members.setting-modal.likes-options.${likesOpt}`) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.reply-title') }}
                                    </p>
                                    <p class="input-description">
                                        {{ $t('community.members.setting-modal.reply-desc') }}
                                    </p>
                                    <select class="input" v-model="reply">
                                        <option
                                            v-for="replyOpt in replyOpts"
                                            :value="replyOpt"
                                            :selected="reply === replyOpt">
                                            {{ $t(`community.members.setting-modal.reply-options.${replyOpt}`) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.admin-announcements-title') }}
                                    </p>
                                    <p class="input-description">
                                        {{ $t('community.members.setting-modal.admin-announcements-desc') }}
                                    </p>
                                    <select class="input" v-model="adminAnnounce">
                                        <option
                                            v-for="adminAnnounceOpt in adminAnnounceOpts"
                                            :value="adminAnnounceOpt"
                                            :selected="adminAnnounce === adminAnnounceOpt">
                                            {{ $t(`community.members.setting-modal.admin-options.${adminAnnounceOpt}`) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex mt1">
                                <div class="flex-1">
                                    <p class="input-label">
                                        {{ $t('community.members.setting-modal.event-reminders-title') }}
                                    </p>
                                    <p class="input-description">
                                        {{ $t('community.members.setting-modal.event-reminders-desc') }}
                                    </p>
                                    <select class="input" v-model="eventReminder">
                                        <option
                                            v-for="eventReminderOpt in eventReminderOpts"
                                            :value="eventReminderOpt"
                                            :selected="eventReminder === eventReminderOpt">
                                            {{ $t(`community.members.setting-modal.event-options.${eventReminderOpt}`) }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="submit-button mt1">
                                <button
                                    class="button is-medium community-blue-btn text-uppercase"
                                    @click="saveNotificationSettings">{{ $t('common.save') }}</button>
                            </div>
                        </div>
                    </div>

                    <div v-if="extraData === 'chat'">
                        <p class="tab-content-title">
                            {{ $t('community.members.setting-modal.chat') }}
                        </p>
                        <p class="tab-content-desc">
                            {{ $t('community.members.setting-modal.chat-desc') }}
                        </p>

                        <div class="chat-content-action">
                            <div v-if="typeof community.name !== 'undefined'" class="community-logo-section">
                                <img v-if="community.logo !== ''" :src="community.logo" class="community-logo" />
                                <div class="ml1 community-name">
                                    {{ community.name }}
                                </div>
                            </div>

                            <select class="input tab-content-chat-select" v-model="chat">
                                <option value="on" :selected="chat === 'on'">
                                    {{ $t('community.members.setting-modal.chat-on') }}
                                </option>
                                <option value="off" :selected="chat === 'off'">
                                    {{ $t('community.members.setting-modal.chat-off') }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <StatsSetting v-if="extraData === 'stats'" />

                    <div v-if="extraData === 'invite'">
                        <div v-if="isManager(role)">
                            <p class="tab-content-title">
                                {{ $t('community.members.setting-modal.invite-title') }}
                            </p>
                            <p class="tab-content-desc">
                                {{
                                    $t('community.members.setting-modal.admin-settings.to-join-group').replace('#community_name#',
                                        community.name) }}
                            </p>

                            <div class="flex mt1">
                                <!-- Email -->
                                <div class="flex-1">
                                    <p class="left input-label">{{
                                        $t('community.members.setting-modal.admin-settings.email-address') }}</p>
                                    <input class="input"
                                        :placeholder="$t('community.members.setting-modal.admin-settings.email-address')"
                                        v-model="inviteEmail" @input="onInput" />
                                </div>
                            </div>

                            <div class="submit-button mt1">
                                <button class="button is-medium community-blue-btn" :class="sendInviteButton" @click="sendInvite"
                                    :disabled="disabledConfirm">{{
                                        $t('community.members.setting-modal.admin-settings.send-invite') }}</button>
                            </div>

                            <div class="invite-share-link" @click="toggleShareLinkShow">
                                <p>
                                    {{ $t('community.members.setting-modal.admin-settings.invite-to-share-link') }}
                                </p>
                                <font-awesome-icon v-if="inviteShareLinkCollapseStatus" icon="fa fa-angle-up"
                                    class="ml-05 share-link-icon" />
                                <font-awesome-icon v-else icon="fa fa-angle-down" class="ml-05 share-link-icon" />
                            </div>

                            <div v-if="inviteShareLinkCollapseStatus">
                                <div class="invite-content-action">
                                    <div class="invite-content-action-input">
                                        {{ inviteShareLink }}
                                    </div>

                                    <div class="invite-content-action-btn" @click="copy">
                                        {{ inviteActionText }}
                                    </div>
                                </div>
                                <p class="grey-desc mt1">
                                    {{ $t('community.members.setting-modal.admin-settings.invite-to-share-desc') }}
                                </p>
                            </div>

                            <AdminMembershipSetting />
                            <ApiKeySetting />
                        </div>
                        <div v-else>
                            <p class="tab-content-title">
                                {{ $t('community.members.setting-modal.invite-title') }}
                            </p>
                            <p class="tab-content-desc">
                                {{ $t('community.members.setting-modal.invite-desc') }}
                            </p>

                            <div class="invite-share-link" @click="toggleShareLinkShow">
                                <p>
                                    {{ $t('community.members.setting-modal.admin-settings.invite-to-share-link') }}
                                </p>
                                <font-awesome-icon v-if="inviteShareLinkCollapseStatus" icon="fa fa-angle-up"
                                    class="ml-05 share-link-icon" />
                                <font-awesome-icon v-else icon="fa fa-angle-down" class="ml-05 share-link-icon" />
                            </div>

                            <div v-if="inviteShareLinkCollapseStatus">
                                <div class="invite-content-action">
                                    <div class="invite-content-action-input">
                                        {{ inviteShareLink }}
                                    </div>

                                    <div class="invite-content-action-btn" @click="copy">
                                        {{ inviteActionText }}
                                    </div>
                                </div>
                                <p class="grey-desc mt1">
                                    {{ $t('community.members.setting-modal.admin-settings.invite-to-share-desc') }}
                                </p>
                            </div>
                        </div>

                        <div ref="reference"></div>
                    </div>

                    <GeneralSetting v-if="extraData === 'general'"/>
                    <GroupsSetting v-if="extraData === 'groups'"/>
                    <ClassroomSetting v-if="extraData === 'classroom'"/>
                    <PricingSubscriptionSettings v-if="extraData === 'pricing-notifications-settings'"/>
                    <PricingSetting v-if="extraData === 'admin-membership'"/>

                    <RulesSetting v-if="extraData === 'rules'" />
                    <CategorySetting v-if="extraData === 'categories'"/>
                    <PluginSetting v-if="extraData === 'extensions'"/>
                    <LinkTabSetting v-if="extraData === 'linktab'"/>
                    <CalendarSetting v-if="extraData === 'calendar'"/>

                    <ProfileSetting v-if="extraData === 'profile'"/>
                    <RewardsOverview v-if="extraData === 'level-rewards'"/>
                    <BillingOverview v-if="extraData === 'billing'"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment';
import StatsSetting from "./components/setting/StatsSetting";
import GeneralSetting from "./components/setting/GeneralSetting";
import ApiKeySetting from "./components/setting/ApiKeySetting";
import GroupsSetting from "./components/setting/GroupsSetting";
import AdminMembershipSetting from "./components/setting/AdminMembershipSetting";
import RulesSetting from "./components/setting/RulesSetting";
import CategorySetting from "./components/setting/CategorySetting";
import PluginSetting from "./components/setting/PluginSetting";
import LinkTabSetting from "./components/setting/LinkTabSetting";
import CalendarSetting from "./components/setting/CalendarSetting";
import ClassroomSetting from "./components/setting/ClassroomSetting";
import ProfileSetting from "./components/setting/ProfileSetting";
import BillingOverview from "./components/setting/billing";
import RewardsOverview from "./components/setting/rewards";
import { notify } from "@kyvg/vue3-notification";
import {MemberAccess, ClassroomViewType, BillingSectionType, CommunityStatus} from '../../data/enums';
import isManager from "../../mixins/util";
import PricingSetting from "./components/setting/PricingSetting.vue";
import PricingSubscriptionSettings from "./components/setting/PricingSubscriptionSettings.vue";
import CancelMembership from "./components/setting/membership/CancelMembership.vue";
import MembershipUpdatePaymentMethod from "./components/setting/membership/MembershipUpdatePaymentMethod.vue";
import MembershipCancelled from "./components/setting/membership/MembershipCancelled.vue";
import PaymentHistory from "./components/setting/membership/PaymentHistory.vue";
import PayoutHistory from "./components/setting/PayoutHistory.vue";
import UserTransactionsTable from "../Table/UserTransactionsTable.vue";
import VerificationCode from "../Forms/VerificationCode.vue";
import InvoiceServiceFeesHistory from "./components/setting/InvoicesServiceFeesHistory.vue";
import Payouts from "./components/setting/Payouts.vue";

export default {
    name: 'CommunitySetting',
    mixins: [
        isManager
    ],
    components: {
      Payouts,
      InvoiceServiceFeesHistory,
      VerificationCode,
      UserTransactionsTable,
      PayoutHistory,
      PricingSetting,
      StatsSetting,
      GeneralSetting,
      ApiKeySetting,
      GroupsSetting,
      AdminMembershipSetting,
      RulesSetting,
      CategorySetting,
      LinkTabSetting,
      CalendarSetting,
      ClassroomSetting,
      ProfileSetting,
      PricingSubscriptionSettings,
      BillingOverview,
      RewardsOverview,
      PluginSetting,
      CancelMembership,
      MembershipUpdatePaymentMethod,
      MembershipCancelled,
      PaymentHistory
    },
    data() {
        return {
            MemberAccess,
            ClassroomViewType,
            BillingSectionType,
            regularTabs: ['membership', 'payments', 'notifications', 'invite', 'security'],
            adminUserTabs: ['profile', 'notifications', 'membership', 'security'],
            adminSettingTabs: [],
            admin0SettingTabs: ['stats', 'invite', 'general', 'groups', 'classroom', 'payouts', 'admin-membership', 'rules', 'categories', 'extensions', 'linktab', 'calendar', 'level-rewards'],
            admin1SettingTabs: ['stats', 'invite', 'general', 'groups', 'classroom', 'payouts', 'billing', 'admin-membership', 'rules', 'categories', 'extensions', 'linktab', 'calendar', 'level-rewards'],
            popularIntervalOpts: ['daily', 'weekly', '14days', 'monthly', 'never'],
            unreadIntervalOpts: ['hourly', '3-hours', '6-hours', '12-hours', 'daily', '2-day', 'weekly', 'never'],
            likesOpts: [1, 0],
            replyOpts: [1, 0],
            adminAnnounceOpts: [1, 0],
            eventReminderOpts: [1, 0],
            inviteActionText: '',
            inviteEmail: '',
            disabledConfirm: true,
            inviteShareLinkCollapseStatus: false,
            currentPassword: '',
            newPassword: '',
            confirmPassword: '',
            processing: false,
            loading: false
        };
    },
    mounted() {
        this.inviteActionText = this.$t('community.members.setting-modal.copy');

        if (parseInt(this.member.user_id) === parseInt(this.community.user_id)) {
            this.adminSettingTabs = this.admin1SettingTabs;
        } else {
            this.adminSettingTabs = this.admin0SettingTabs;
        }

        // if community is inactive -> user can only see billing tag
        if (this.community.status === CommunityStatus.INACTIVE) {
            this.adminSettingTabs = ['billing'];
        }

        this.currentPassword = Math.random().toString(36).slice(2);
        moment.locale(this.$i18n.locale);
    },
    watch: {
        'extraAction': function (v) { },
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns community name
         */
        communityName() {
            return '<span class="font-weight-600">' + this.community.name + '</span>';
        },

        /**
         * Returns user
         */
        user() {
            return this.$store.state.auth.user;
        },

        /**
         * Return url valid status
         */
        validUrl() {
            return this.$store.state.auth.validUrl;
        },

        /**
         * Return invite share link
         */
        inviteShareLink() {
            return this.$store.state.communitycenter.inviteShareLink;
        },

        /**
         * Returns community logo
         */
        communityLogo() {
            return this.community.logo;
        },

        /**
         * Returns settings
         */
        settings() {
            return this.$store.state.communitycenter.settings;
        },

        /**
         * extra data of child modal
         */
        extraData() {
            return this.$store.state.modal.extraData;

            /*
            let tabLink = this.$store.state.modal.extraData;

            if (this.adminSettingTabs.indexOf(tabLink) > 4) {
                setTimeout(() => {
                    var objDiv = document.getElementById("tab_link_section");
                    objDiv.scrollTop = objDiv.scrollHeight;
                }, 1000);
            }

            return tabLink;
            */
        },

        extraAction: {
            get() {
                if (this.$store.state.modal.extraAction == 'openInviteLink') {
                    this.toggleShareLinkShow()
                }
                return this.$store.state.modal.extraAction
            },
            set(v) {
            }
        },

        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist() {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns access of member
         */
        access() {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * Returns role of member
         */
        role() {
            return this.memberExist ? this.member.role : null;
        },

        /**
         * Get | Set digest email
         */
        popularInterval: {
            get() {
                return this.$store.state.member.settings.popular_interval;
            },
            set(v) {
                this.$store.commit('setMemberSettingsProperty', {
                    key: 'popular_interval',
                    v
                });
            }
        },

        /**
         * Get | Set notification email
         */
        unreadInterval: {
            get() {
                return this.$store.state.member.settings.unread_interval;
            },
            set(v) {
                this.$store.commit('setMemberSettingsProperty', {
                    key: 'unread_interval',
                    v
                });
            }
        },

        /**
         * Get | Set likes
         */
        likes: {
            get() {
                return this.$store.state.member.settings.likes;
            },
            set(v) {
                this.$store.commit('setMemberSettingsProperty', {
                    key: 'likes',
                    v
                });
            }
        },

        /**
         * Get | Set adminAnnounce
         */
        adminAnnounce: {
            get() {
                return this.$store.state.member.settings.admin_announce;
            },
            set(v) {
                this.$store.commit('setMemberSettingsProperty', {
                    key: 'admin_announce',
                    v
                });
            }
        },

        /**
         * Get | Set eventReminder
         */
        eventReminder: {
            get() {
                return this.$store.state.member.settings.event_reminder;
            },
            set(v) {
                this.$store.commit('setMemberSettingsProperty', {
                    key: 'event_reminder',
                    v
                });
            }
        },

        /**
         * Get | Set chat
         */
        chat: {
            get() {
                return this.$store.state.communitycenter.communitySettings.chat;
            },
            set(v) {
                this.$store.commit('setCommunitySettingsProperty', {
                    key: 'chat',
                    v
                });
            }
        },

        /**
         * Get | Set reply
         */
        reply: {
            get() {
                return this.$store.state.member.settings.reply;
            },
            set(v) {
                this.$store.commit('setMemberSettingsProperty', {
                    key: 'reply',
                    v
                });
            }
        },

        /**
         * Returns disabled reset status
         */
        disabledReset() {
            return this.newPassword !== this.confirmPassword;
        },

        /**
         * Returns button status class
         */
        button() {
            return this.processing ? ' is-loading' : '';
        },

        /**
         * Returns button status class
         */
        sendInviteButton() {
            return this.processing ? ' is-loading' : '';
        },

        /**
         * Returns content loading
         */
        contentLoading() {
            return this.$store.state.communitycenter.communitySettings.contentLoading;
        },

        discount() {
          let discount = 0;

          if (this.price && this.price.amount_monthly && this.price.amount_yearly) {
            let yearlyMonthly = this.price.amount_monthly * 12;
            discount = 1 - (this.price.amount_yearly / yearlyMonthly);
          }

          if (discount < 0) {
            discount = 0
          }

          return parseInt(discount * 100);
        }
    },
    methods: {
      manageMembership()
      {
        this.$store.commit('setModalExtraData', 'manage-membership');
      },

      upgradeToAnnual()
      {
        this.$store.commit('setModalExtraData', 'manage-membership-upgrade-to-annual');
      },

      updatePaymentMethod()
      {
        this.$store.commit('setModalExtraData', 'manage-membership-update-payment-method');
      },

      async subscriptionReactivate()
      {
        this.processing = true;
        await this.$store.dispatch('SUBSCRIPTION_REACTIVATE');
        this.processing = false;
      },
        /**
         * Class to show for clicked tab
         */
        checkSideTab(name) {
            let sideTabClass = '';
            sideTabClass = (this.extraData === name) ? 'clicked' : '';

            return sideTabClass;
        },

        /**
         * Select tab click handler
         */
        async selectTab(tab) {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'contentLoading',
                v: true
            });

            if (tab === 'classroom') {
                this.$store.commit('setIsSimpleClassrooms', 1);
                await this.$store.dispatch('GET_CLASSROOMS');
                this.$store.commit('setClassroomShow', ClassroomViewType.LIST);
            } else if (tab === 'calendar') {
                await this.$store.dispatch('GET_COMMUNITY_EVENTS');
                await this.$store.dispatch('GET_MONTHLY_EVENTS');
            } else if (tab === 'stats') {
                await this.$store.dispatch('GET_ADMIN_STATS_DATA');
            } else if (tab === 'billing') {
                this.$store.commit('setCommunitySettingsProperty', {
                    key: 'billingSection',
                    v: BillingSectionType.OVERVIEW
                });
            }

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'contentLoading',
                v: false
            });

            this.$store.commit('setModalExtraData', tab);
            this.$store.commit('resetSettingModalContent');
        },

        /**
         * Returns last updated
         */
        createdAt(created_at) {
            return '<span class="font-weight-600">' + moment(created_at).format("LL") + '</span>';
        },

        /**
         * Leave group
         */
        async leaveGroup() {

            console.log('111111111111')
            console.log(this.community)
            console.log(this.member)

            if (this.memberExist && this.community.user_id === this.member.user_id) {
                notify({
                    text: this.$t('community.members.setting-modal.cannot-leave'),
                    type: 'error'
                });
            } else {
                await this.$store.dispatch('LEAVE_FROM_COMMUNITY');
                this.$store.commit('hideModal');
            }
        },

        /**
         * copy action handler
         */
        copy() {
            const storage = document.createElement('textarea');
            storage.value = this.inviteShareLink;
            this.$refs.reference.appendChild(storage);
            storage.select();
            storage.setSelectionRange(0, 99999);
            document.execCommand('copy');
            this.$refs.reference.removeChild(storage);

            this.inviteActionText = this.$t('community.members.setting-modal.copied');

            setTimeout(() => {
                this.inviteActionText = this.$t('community.members.setting-modal.copy');
            }, 2000);
        },

        /**
         * Get first characters from every words in string
         */
        logoPlaceholder(name) {
            const matches = name.match(/\b(\w)/g);
            const acronym = matches.join('');

            return acronym.slice(0, 2).toUpperCase();
        },

        validateEmail(email) {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
                return true;
            } else {
                return false;
            }
        },

        onInput(e) {
            if (this.inviteEmail === '' || !this.validateEmail(this.inviteEmail)) {
                this.disabledConfirm = true;
            } else {
                this.disabledConfirm = false;
            }
        },

        /**
         * Send invite to email
         */
        async sendInvite() {
            this.processing = true;

            this.$store.commit('setInviteShareLink', null);

            this.inviteShareLinkCollapseStatus = false;

            await this.$store.dispatch('SEND_INVITE', this.inviteEmail);

            this.processing = false;
        },

        async toggleShareLinkShow() {
            await this.$store.dispatch('GET_INVITE_SHARE_LINK');
            this.inviteShareLinkCollapseStatus = !this.inviteShareLinkCollapseStatus;
        },

        /**
         * Save notification settings
         */
        async saveNotificationSettings() {
            this.$store.dispatch('STORE_MEMBER_SETTINGS');
        },

        /**
         * Reset password
         */
        async resetPassword() {
            this.processing = true;

            await new Promise((resolve) => setTimeout(() => { resolve(); }, 1500));

            await this.$store.dispatch('RESET_PASSWORD', {
                userId: this.user.id,
                password: this.newPassword,
                from: 'settings'
            });

            this.processing = false;
        },

        outclick(e) {
            this.$store.commit('resetRuleDropdownItem');
            this.$store.commit('resetCategoryDropdownItem');
            this.$store.commit('resetLinkDropdownItem');
            this.$store.commit('resetEventDropdownItem');
            this.$store.commit('resetClassroomDropdownItem');
            this.$store.commit('resetSetDropdownItem');
            this.$store.commit('resetLessonDropdownItem');
            this.$store.commit('resetSetLessonDropdownItem');
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

.community-name {
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
    white-space: nowrap;
}

.tab-link:hover {
    background-color: rgb(228, 228, 228);
}

.tab-link.clicked {
    color: #fff;
    background-color: #9198FF;
}

.tab-content-label {
    margin-bottom: 10px;
}

.tab-content-select {
    width: 100%;
    margin-bottom: 20px;
}

.chat-content-action {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-top: 15px;
}

.tab-content-chat-select {
    width: 150px;
}

.invite-content-action {
    width: 100%;
    height: 52px;
    border-radius: 3px;
    border: 1px solid rgb(228, 228, 228);
    display: flex;
    align-items: center;
    margin-top: 15px;
}

.invite-content-action:focus {
    border: 1px solid rgb(144, 144, 144);
}

.invite-content-action-input {
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding-left: 10px;
}

.invite-content-action-btn {
    width: 100px;
    height: 48px;
    background-color: #9198FF;
    font-weight: bold;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 3px;
    margin-right: 5px;
    text-transform: uppercase;
}

.invite-content-action-btn:hover {
    background-color: #7957d5;
    color: #fff;
}

.setting-label-section {
    color: rgb(144, 144, 144);
    font-size: 16px;
    font-weight: bold;
    text-align: left;
    padding: 10px 0px;
}

.setting-content-section {
    margin-bottom: 15px;
}

.invite-share-link {
    margin-top: 0px;
    font-size: 15px;
    color: rgb(46, 110, 245);
    display: flex;
    align-items: center;
    cursor: pointer;
}

.invite-share-link:hover {
    text-decoration: underline;
}

.invite-share-link .share-link-icon {
    transition-duration: 500ms;
    color: rgb(46, 110, 245);
}

.password-not-match-error {
    color: #e74c3c;
    font-size: 12px;
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

    .invite-content-action {
        height: 42px;
        margin-top: 5px;
    }

    .invite-content-action-btn {
        height: 38px;
    }

    .setting-label-section {
        font-size: 14px;
        padding: 10px;
    }

    .setting-content-section {
        display: flex;
        align-items: center;
        overflow: auto;
    }

    .invite-share-link {
        margin-top: 25px;
        font-size: 14px;
    }
}
</style>