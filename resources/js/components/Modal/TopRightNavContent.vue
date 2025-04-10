<template>
    <div class="w100">
        <div class="nav-content-header">
            <div class="flex align-items-center jcb">
                <div class="nav-content-tab-container">
                    <div v-for="tab in navContentTabs" :key="tab.key" class="nav-content-tab"
                        :class="navContentTab === tab.key ? 'tab-selected' : ''" @click="selectNavContentTab(tab.key)">
                        <div v-if="tab.key === 'chat'" class="chat-dropdown-trigger">
                            <font-awesome-icon :icon="['fa', 'message']" class="chat-bell" />
                            <span v-if="unreadChatsCnt > 0" class="chat-cnt">
                                {{ unreadChatsCnt }}
                            </span>
                        </div>

                        <div v-if="tab.key === 'notification'" class="notification-dropdown-trigger">
                            <font-awesome-icon :icon="['fa', 'bell']" class="notification-bell" />
                            <span v-if="unreadNotificationsCnt > 0" class="notification-cnt">
                                {{ unreadNotificationsCnt }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pb-05 flex align-items-center">
                    <div v-if="navContentTab === 'chat' && unreadChatUsers.length > 0" class="dropdown mr-05">
                        <div
                            class="dropdown-trigger chat-action-link"
                            @click="showChatAction"
                            v-click-outside="hideChatAction"
                        >
                            <font-awesome-icon icon="fa fa-ellipsis" class="font-20px" />
                        </div>
                        <div
                            id="chat_action_content"
                            class="dropdown-menu"
                            :class="displayChatAction ? 'show' : ''"
                        >
                            <div class="dropdown-content">
                                <div class="tab-content-item-action-item" @click.stop="markChatAllAsRead">
                                    {{ $t('community.chats.mark-all-as-read') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="navContentTab === 'notification' && unreadNotificationsCnt > 0" class="dropdown mr-05">
                        <div
                            class="dropdown-trigger notification-action-link"
                            @click="showNotificationAction"
                            v-click-outside="hideNotificationAction"
                        >
                            <font-awesome-icon icon="fa fa-ellipsis" class="font-20px" />
                        </div>
                        <div
                            id="notification_action_content"
                            class="dropdown-menu"
                            :class="displayNotificationAction ? 'show' : ''"
                        >
                            <div class="dropdown-content">
                                <div class="tab-content-item-action-item" @click.stop="markNotificationAllAsRead">
                                    {{ $t('community.notifications.mark-all-as-read') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <div
                            class="dropdown-trigger"
                            @click="showUserImgInfo"
                            v-click-outside="hideUserImgInfo"
                        >
                            <div class="image is-48x48 flex align-items-center">
                                <img v-if="member" class="is-rounded" :src="getMemberGravatar(member)"
                                    alt="gravatar-profile-image">
                                <img v-else-if="user && user.email" class="is-rounded" :src="getUserGravatar(user)"
                                    alt="gravatar-profile-image">
                                <img v-else class="is-rounded" :src="defaultAvatar" alt="gravatar-profile-image">
                            </div>
                        </div>
                        <div
                            id="user_info_content_mobile"
                            class="dropdown-menu"
                            :class="displayUserImgInfo ? 'show' : ''"
                        >
                            <div class="dropdown-content">
                                <span class="navbar-item">{{ userEmail }}</span>
                                <hr class="navbar-divider">
                                <a class="navbar-item" @click="goToProfile">{{ $t('nav.profile') }}</a>
                                <a v-if="wolfeoLoginUrl" class="navbar-item" @click="loginToPacky">{{ $t('nav.wolfeo') }}</a>

                                <a class="navbar-item" @click="logout">{{ $t('nav.logout') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-05">
                <div v-if="navContentTab === 'chat'" class="chat-filters">
                    <div v-for="filter in chatUserFilters" :key="filter.key" class="chat-filter"
                        :class="chatUserFilter === filter.key ? 'selected' : ''"
                        @click="selectChatUserFilter(filter.key)">
                        {{ filter.label }}
                    </div>
                </div>

                <div v-if="navContentTab === 'chat'" class="chat-search">
                    <font-awesome-icon :icon="['fa', 'magnifying-glass']" class="chat-search-icon" />

                    <input type="text" class="input chat-search-filter" :placeholder="$t('common.search-user')"
                        @input="searchUserFilteredInput" v-model="searchUserFilter" />
                </div>

                <div v-if="navContentTab === 'notification'" class="notification-filters">
                    <div v-for="filter in notificationFilters" :key="filter.key" class="notification-filter"
                        :class="notificationFilter === filter.key ? 'selected' : ''"
                        @click="selectNotificationFilter(filter.key)">
                        {{ filter.label }}
                    </div>
                </div>
            </div>
        </div>

        <div class="nav-content-content">
            <div v-if="navContentTab === 'chat'" class="chats-content-for-mobile">
                <div v-if="navContentLoading" class="chat-loading"></div>

                <div v-else class="mt-05">
                    <div v-if="chatUsers.length > 0">
                        <div v-for="item in chatUsers" class="chat-item">
                            <div class="flex align-items-center jcb w100" :class="chatItemClass(item)">
                                <div class="flex align-items-center w100" @click="markChatAsRead(item, true)">
                                    <div class="image is-48x48 flex align-items-center">
                                        <img v-if="item.member && item.member.photo" class="is-rounded"
                                            :src="getMemberGravatar(item.member)" alt="gravatar-profile-image">
                                        <img v-else-if="item && item.email" class="is-rounded"
                                            :src="getUserGravatar(item)" alt="gravatar-profile-image">
                                        <img v-else class="is-rounded" :src="defaultAvatar"
                                            alt="gravatar-profile-image">
                                    </div>
                                    <div class="chat-content">
                                        <div class="chat-content-title">
                                            <div class="chat-title-owner">
                                                {{ getChatUserName(item) }}
                                            </div>
                                            &nbsp;
                                            <div class="chat-created-at grey">
                                                • {{ getMessageCreatedDate(item.lastChat?.created_at) }}
                                            </div>
                                        </div>

                                        <div v-if="item.lastChat" class="chat-content-summary">
                                            {{ stripSpanTag(item.lastChat.content) }}
                                        </div>
                                        <div v-else class="chat-content-summary">
                                            {{ $t('community.chats.no-message-yet') }}
                                        </div>
                                    </div>
                                </div>

                                <div v-if="item.lastChat && item.lastChat.read_at === null && item.lastChat.to_id === user.id"
                                    class="chat-read-mark unread" @click="markChatAsRead(item, false)">
                                    <a href="#" :title="$t('community.chats.mark-as-read')"
                                        class="tooltip small-tooltip ">
                                        <span class="flex align-items-center jc flex-column h100-percent" title="">
                                            <font-awesome-icon icon="fa fa-circle" class="unread-circle" />
                                        </span>
                                    </a>
                                </div>
                                <div v-else class="chat-read-mark"></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex align-items-center jc mt1">
                        {{ noUserResults }}
                    </div>
                </div>
            </div>

            <div v-if="navContentTab === 'notification'" class="notifications-content-for-mobile">
                <div v-if="navContentLoading" class="notification-loading"></div>
                <div v-else v-for="item in notifications" class="notification-item"
                    :class="item.read_at === null ? '' : 'already-read'">
                    <div class="flex align-items-center w100" @click="markNotificationAsRead(item, true)">
                        <div class="image is-48x48 flex align-items-center">
                            <img class="is-rounded" :src="getMemberGravatar(item.member)" alt="gravatar-profile-image">
                        </div>
                        <div class="notification-content">
                            <div class="notification-content-title">
                                <div v-if="item.object_type === 'approved_to_join' || item.object_type === 'declined_to_join'"
                                    class="notification-title-owner">
                                    {{ community.name }}
                                </div>
                                <div v-else class="notification-title-owner">
                                    {{ getChatUserName(item.user) }}
                                </div>
                                &nbsp;
                                <div class="notification-created-at">
                                    • {{ getDiffTimeFromNow(item.created_at) }}
                                </div>
                            </div>

                            <div class="notification-content-summary">
                                {{ this.$t('community.notifications.message.' + item.object_type) }}
                            </div>

                            <div class="notification-content-summary" v-html="'- ' + updateViewContentForMention(item.summary)"></div>
                        </div>
                    </div>
                    <div v-if="item.read_at === null" class="notification-read-mark unread"
                        @click="markNotificationAsRead(item, false)">
                        <a href="#" :title="$t('community.notifications.mark-as-read')" class="tooltip small-tooltip ">
                            <span class="flex align-items-center jc flex-column h100-percent" title="">
                                <font-awesome-icon icon="fa fa-circle" class="unread-circle" />
                            </span>
                        </a>
                    </div>
                    <div v-else class="notification-read-mark"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment'
import getMemberGravatar from "../../mixins/util";
import getUserGravatar from "../../mixins/util";
import updateViewContentForMention from "../../mixins/util";
import { MemberAccess } from '../../data/enums';

export default {
    name: 'TopRightNavContent',
    mixins: [
        getMemberGravatar,
        getUserGravatar,
        updateViewContentForMention
    ],
    data() {
        return {
            MemberAccess,
            navContentLoading: false,

            navContentTabs: [{
                key: "chat",
                selected: true
            }, {
                key: "notification",
                selected: false
            }],

            noUserResults: '',
            defaultAvatar: '/assets/img/default.png',

            displayChatAction: false,
            displayNotificationAction: false,
            displayUserImgInfo: false
        };
    },
    computed: {
        /**
         * Return auth status
         */
        auth() {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns notification filter
         */
        navContentTab() {
            return this.$store.state.communitycenter.navContentTab;
        },

        /**
         * Returns user
         */
        user() {
            return this.$store.state.auth.user;
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
         * Return the user's email
         */
        userEmail() {
            let userEmail = '';
            if (this.memberExist && typeof this.member.user !== 'undefined' && this.member.user !== null) {
                userEmail = this.member.user.email;
            } else {
                userEmail = this.user.email;
            }

            return userEmail;
        },

        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns unread chat users count
         */
        unreadChatsCnt() {
            return this.$store.state.communitycenter.unreadChatsCnt;
        },

        /**
         * Returns chat users
         */
        chatUsers() {
            return this.$store.state.communitycenter.chatUsers;
        },

        /**
         * Returns notifications
         */
        notifications() {
            return this.$store.state.communitycenter.notifications;
        },

        /**
         * Returns unread notifications count
         */
        unreadNotificationsCnt() {
            return this.$store.state.communitycenter.unreadNotificationsCnt;
        },

        /**
         * Returns notification filter
         */
        notificationFilter() {
            return this.$store.state.communitycenter.notificationFilter;
        },

        /**
         * Returns notification filters
         */
        notificationFilters() {
            let notificationFilters = [{
                key: "all",
                label: this.$t('community.notifications.all'),
                selected: true
            }, {
                key: "unread",
                label: this.$t('community.notifications.unread'),
                selected: false
            }];

            notificationFilters = notificationFilters.map((filter) => {
                if (filter.key === this.notificationFilter) {
                    filter.selected = true;
                } else {
                    filter.selected = false;
                }

                return { ...filter };
            });

            return notificationFilters;
        },

        /**
         * Returns chat users
         */
        chatUsers() {
            return this.$store.state.communitycenter.chatUsers;
        },

        /**
         * Returns unread chat users
         */
        unreadChatUsers() {
            return this.chatUsers.filter(el => el.lastChat && el.lastChat.read_at === null && el.lastChat.to_id === this.user.id);
        },

        /**
         * Returns chat filter
         */
        chatUserFilter() {
            return this.$store.state.communitycenter.chatUserFilter;
        },

        /**
         * Returns chat filters
         */
        chatUserFilters() {
            let chatUserFilters = [{
                key: "all",
                label: this.$t('community.chats.all'),
                selected: true
            }, {
                key: "unread",
                label: this.$t('community.chats.unread'),
                selected: false
            }];

            chatUserFilters = chatUserFilters.map((filter) => {
                if (filter.key === this.chatUserFilter) {
                    filter.selected = true;
                } else {
                    filter.selected = false;
                }

                return { ...filter };
            });

            return chatUserFilters;
        },

        /**
         * Get | Set search filter string
         */
        searchUserFilter: {
            get() {
                return this.$store.state.communitycenter.searchUserFilter;
            },
            set(v) {
                this.$store.commit('setSearchUserFilter', v);
            }
        },

        /**
         * Logs user to wolfeo.me
         */
        wolfeoLoginUrl() {
            return this.$store.state.communitycenter.wolfeoLoginUrl;
        },
    },
    methods: {
        /**
         * Select nav content tab
         */
        async selectNavContentTab(key) {
            this.$store.commit('setNavContentTab', key);

            this.navContentLoading = true;

            if (key === 'notification') {
                await this.$store.dispatch('GET_NOTIFICATIONS');
            } else if (key === 'chat') {
                await this.$store.dispatch('GET_CHAT_USERS');
            }

            this.navContentLoading = false;
        },

        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow(date) {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        async markNotificationAsRead(item, showDetail) {
            this.navContentLoading = true;
            await this.$store.dispatch('MARK_NOTIFICATION_AS_READ', {
                id: item.id,
                objectType: item.object_type,
                objectId: item.object_id,
                showDetail
            });
            this.navContentLoading = false;
        },

        async markNotificationAllAsRead() {
            this.navContentLoading = true;
            await this.$store.dispatch('MARK_NOTIFICATION_AS_READ');
            this.navContentLoading = false;
        },

        /**
         * Return message created date
         */
        getMessageCreatedDate(createdAt) {
            return createdAt ? moment(createdAt).locale(this.$i18n.locale).format("LLL") : '';
        },

        /**
         * Return user name
         */
        getChatUserName(user) {
            let name = '';
            if (typeof user.firstname !== 'undefined') {
                name = user.firstname;
                if (typeof user.lastname !== 'undefined') {
                    name += ' ' + user.lastname;
                }
            }

            return name;
        },

        /**
         * Return chat item class
         */
        chatItemClass(item) {
            return item.lastChat && item.lastChat.read_at === null && item.lastChat.to_id === this.user.id ? '' : 'already-read';
        },

        async markChatAsRead(user, showDetail) {
            await this.$store.dispatch('GET_CHAT_DETAIL', {
                communityId: this.community.id,
                fromId: this.user.id,
                toId: user.id,
                showDetail
            });

            this.navContentLoading = true;
            await this.$store.dispatch('GET_CHAT_USERS');
            this.navContentLoading = false;
        },

        showChatAction() {
            this.displayChatAction = true;
        },

        hideChatAction() {
            this.displayChatAction = false;
        },

        showNotificationAction() {
            this.displayNotificationAction = true;
        },

        hideNotificationAction() {
            this.displayNotificationAction = false;
        },

        /**
         * Select chat filter
         */
        async selectChatUserFilter(key) {
            this.$store.commit('setChatUserFilter', key);

            this.navContentLoading = true;
            await this.$store.dispatch('GET_CHAT_USERS');
            this.navContentLoading = false;
        },

        /**
         * Select notification filter
         */
        async selectNotificationFilter(key) {
            this.$store.commit('setNotificationFilter', key);

            this.navContentLoading = true;
            await this.$store.dispatch('GET_NOTIFICATIONS');
            this.navContentLoading = false;
        },

        /**
         * Search User Filter when inputing
         *
         * Events are grouped and fired once after 500ms
         * this.timeout is created dynamically
         *
         */
        searchUserFilteredInput() {
            this.navContentLoading = (this.searchUserFilter.length > 0);

            if (this.timeout) clearTimeout(this.timeout);

            this.timeout = setTimeout(async () => {
                await this.$store.dispatch('GET_CHAT_USERS');

                if (this.chatUsers.length === 0) {
                    this.noUserResults = this.$t('common.no-result-for').replace('#searchFilter#', this.searchUserFilter);
                }

                this.navContentLoading = false;
            }, 500);
        },

        async markChatAllAsRead() {
            this.chatLoading = true;
            await this.$store.dispatch('MARK_CHAT_ALL_AS_READ');
            this.chatLoading = false;
        },

        showUserImgInfo() {
            this.displayUserImgInfo = !this.displayUserImgInfo;
        },

        hideUserImgInfo() {
            this.displayUserImgInfo = false;
        },

        /**
         * Open or close the navbar on mobile
         */
        goToProfile() {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                this.$store.commit('showModal', {
                  type: 'CommunitySetting',
                  extraData: 'profile',
                  transparent: true
                });
              } else if (this.access === MemberAccess.PENDING) {
                this.$store.commit('showModal', {
                  type: 'Pending',
                  transparent: true
                });
              } else {
                this.$store.commit('showModal', {
                  type: 'Join',
                  transparent: true
                });
              }
            } else {
                this.showLogin();
            }
        },

        stripSpanTag(content) {
            return content.replace(/<\/?span[^>]*>/g, "");
        },

        /**
         * Login to wolfeo
         */
        async loginToPacky() {
            await axios.post('/customer/wolfeo/login').then(resp => {
                console.log('customer.loginToPacky', resp);
                if (resp.data.success) {
                    window.open(this.wolfeoLoginUrl, '_blank');
                }
            }).catch(err => {
                console.error(err);
            });
        },

        /**
         * Log the user out
         */
        async logout() {
            this.$store.commit('communityLoading', true);

            await axios.post('/logout')
                .then(resp => {
                    console.log('customer.logout', resp);

                    this.$store.commit('setLoggedIn', false);
                    this.$store.commit('setValidUrl', null);
                    this.$store.commit('resetCommunityData');
                    this.$store.commit('resetUser');

                    if (localStorage.getItem('communityUrl') !== null) {
                        localStorage.removeItem('communityUrl');
                    }

                    window.location.href = '/';
                })
                .catch(err => {
                    console.error(err);
                });
        },
    }
}
</script>

<style scoped>
.nav-content-header {
    padding: 20px 15px 0px 15px;
    border-bottom: 1px solid rgb(228, 228, 228);
}

.nav-content-tab-container {
    display: flex;
    align-items: center;
    margin-bottom: -15px;
}

.nav-content-tab {
    width: 50px;
    display: flex;
    align-items: center;
    padding-bottom: 5px;
}

.chat-dropdown-trigger,
.notification-dropdown-trigger {
    margin: 0 auto;
}

.chats-content-for-mobile,
.notifications-content-for-mobile {
    text-align: left;
    width: 100%;
}

@media only screen and (max-width: 600px) {
    .navbar-item {
        text-align: left;
        padding: 5px 12px;
        font-size: 14px;
    }

    .dropdown-menu {
        top: 40px;
    }

    .navbar-divider {
        display: block;
    }
}
</style>
