<template>
    <div class="flex align-items-center">
        <!-- Hidden on < 1024px -->
        <template v-if="auth">
            <!-- Right of nav -->
            <div class="navbar-end position-relative">
                <div class="navbar-item">
                    <div class="flex align-items-center je">
                        <div v-if="selectedTab === 'member'"
                            class="navbar-item is-right mr-1-5 member-search desktop-only-show">
                            <font-awesome-icon :icon="['fa', 'magnifying-glass']" class="member-search-icon" />

                            <input type="text" class="input member-search-filter"
                                :placeholder="$t('common.search-members')" @input="searchMemberFilteredInput"
                                v-model="searchMemberFilter" />
                        </div>

                        <div class="navbar-item has-dropdown is-right mr-1-5 desktop-only-show">
                            <div class="dropdown flex align-items-center">
                                <div
                                    class="chat-dropdown-trigger"
                                    @click="showChatUsers"
                                >
                                    <font-awesome-icon :icon="['fa', 'message']" class="chat-bell" />
                                    <span v-if="unreadChatsCnt > 0" class="chat-cnt">
                                        {{ unreadChatsCnt }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-if="access === MemberAccess.ALLOWED"
                            class="navbar-item has-dropdown is-right mr-1-5 desktop-only-show">
                            <div class="dropdown flex align-items-center">
                                <div
                                    class="notification-dropdown-trigger"
                                    @click="showNotifications">
                                    <font-awesome-icon :icon="['fa', 'bell']" class="notification-bell" />
                                    <span v-if="unreadNotificationsCnt > 0" class="notification-cnt">
                                        {{ unreadNotificationsCnt }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-right desktop-only-show">
                            <div class="dropdown flex align-items-center">
                                <div
                                    class="dropdown-trigger navbar-link"
                                    @click="showLangOptions"
                                    v-click-outside="hideLangOptions"
                                >
                                    <div class="lang-info">
                                        {{ langIcon }}
                                    </div>
                                </div>
                                <div id="lang_options"
                                     class="dropdown-menu"
                                     :class="displayLangSelectDropdown ? 'show' : ''">
                                    <div class="dropdown-content">
                                        <a v-for="lang in this.langs" class="navbar-item"
                                            @click="changeLanguage(lang.value)">
                                            <img :src="lang.icon" class="lang-flag-icon" />
                                            <span>&nbsp;{{ lang.label }}</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="navbar-item has-dropdown is-right desktop-only-show">
                            <div class="dropdown">
                                <div
                                    class="dropdown-trigger navbar-link"
                                    @click="showUserInfo"
                                    v-click-outside="hideUserInfo"
                                >
                                    <div class="image is-48x48 flex align-items-center">
                                        <img v-if="user" class="is-rounded" :src="getUserGravatar(user)"
                                            alt="gravatar-profile-image">
                                        <img v-else class="is-rounded" :src="defaultAvatar"
                                            alt="gravatar-profile-image">
                                    </div>
                                </div>
                                <div id="user_info_content"
                                     class="dropdown-menu"
                                     :class="displayUserInfo ? 'show' : ''">
                                    <div class="dropdown-content">
                                        <span class="navbar-item">{{ userEmail }}</span>

                                        <hr class="navbar-divider">
                                        <a class="navbar-item" @click="goToProfile">{{ $t('nav.profile') }}</a>
                                        <a v-if="parseInt(this.user.id) === 1" class="navbar-item" @click="goToUsers">{{ $t('nav.user-manager') }}</a>

                                        <hr class="navbar-divider">
                                        <a v-if="wolfeoLoginUrl" class="navbar-item" @click="loginToPacky">{{ $t('nav.wolfeo') }}</a>
                                        <a class="navbar-item" @click="logout">{{ $t('nav.logout') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="navbar-item has-dropdown is-right mobile-only-show">
                            <div class="dropdown">
                                <div class="dropdown-trigger notification-dropdown-trigger"
                                    @click="showUserInfoForMobile">

                                    <div class="image is-48x48 flex align-items-center">
                                        <img v-if="member" class="is-rounded" :src="getMemberGravatar(member)"
                                            alt="gravatar-profile-image">
                                        <img v-else class="is-rounded" :src="defaultAvatar"
                                            alt="gravatar-profile-image">
                                    </div>

                                    <span v-if="unreadNotificationsCnt > 0"
                                        class="notification-cnt notification-cnt-for-mobile">
                                        {{ unreadNotificationsCnt }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    id="chats_section"
                    :class="displayChat ? 'show' : ''"
                    v-click-outside="hideChat"
                >
                    <div class="dropdown-content chat-dropdown-content">
                        <div class="chats-header">
                            <div class="flex align-items-center jcb">
                                <div class="font-weight-600 font-18px">
                                    {{ $t('community.chats.title') }}
                                </div>
                                <div v-if="unreadChatUsers.length > 0" class="dropdown">
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
                            </div>

                            <div class="chat-filters">
                                <div v-for="filter in chatUserFilters" :key="filter.key" class="chat-filter"
                                    :class="chatUserFilter === filter.key ? 'selected' : ''"
                                    @click="selectChatUserFilter(filter.key)">
                                    {{ filter.label }}
                                </div>
                            </div>
                        </div>

                        <div class="chat-search">
                            <font-awesome-icon :icon="['fa', 'magnifying-glass']" class="chat-search-icon" />

                            <input type="text" class="input chat-search-filter" :placeholder="$t('common.search-user')"
                                @input="searchUserFilteredInput" v-model="searchUserFilter" />
                        </div>

                        <div class="chats-content">
                            <div v-if="chatLoading" class="chat-loading"></div>
                            <div v-else class="mt-05">
                                <div v-if="chatUsers.length > 0">
                                    <div v-for="item in chatUsers" class="chat-item">
                                        <div class="flex align-items-center jcb w100" :class="chatItemClass(item)">
                                            <div class="flex align-items-center w100"
                                                @click="markChatAsRead(item, true)">
                                                <div class="image is-48x48 flex align-items-center">
                                                    <img v-if="item.member && item.member.photo" class="is-rounded"
                                                        :src="getMemberGravatar(item.member)"
                                                        alt="gravatar-profile-image">
                                                    <img v-else-if="item && item.email" class="is-rounded"
                                                        :src="getUserGravatar(item)" alt="gravatar-profile-image">
                                                    <img v-else class="is-rounded" :src="defaultAvatar"
                                                        alt="gravatar-profile-image">
                                                </div>
                                                <div class="chat-content">
                                                    <div class="chat-content-title">
                                                        <div class="chat-title-owner">
                                                            {{ getUserName(item) }}
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
                                                    <span class="flex align-items-center jc flex-column h100-percent"
                                                        title="">
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
                    </div>
                </div>

                <div
                    id="notifications_section"
                    v-if="access === MemberAccess.ALLOWED"
                    :class="displayNotifications ? 'show' : ''"
                    v-click-outside="hideNotifications"
                >
                    <div class="dropdown-content notification-dropdown-content">
                        <div class="notifications-header">
                            <div class="flex align-items-center jcb">
                                <div class="font-weight-600 font-18px">
                                    {{ $t('community.notifications.title') }}
                                </div>
                                <div v-if="unreadNotificationsCnt > 0" class="dropdown">
                                    <div 
                                        class="dropdown-trigger notification-action-link"
                                        @click="showNotificationAction"
                                        v-click-outside="hideNotificationAction"
                                    >
                                        <font-awesome-icon icon="fa fa-ellipsis" class="font-20px" />
                                    </div>
                                    <div 
                                        id="notification_action_content" 
                                        :class="displayNotificationAction ? 'show' : ''"
                                    >
                                        <div class="dropdown-content">
                                            <div class="tab-content-item-action-item"
                                                @click.stop="markNotificationAllAsRead">
                                                {{ $t('community.notifications.mark-all-as-read') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="notification-filters">
                                <div v-for="filter in notificationFilters" :key="filter.key" class="notification-filter"
                                    :class="notificationFilter === filter.key ? 'selected' : ''"
                                    @click="selectNotificationFilter(filter.key)">
                                    {{ filter.label }}
                                </div>
                            </div>
                        </div>

                        <div class="notifications-content">
                            <div v-if="notificationLoading" class="notification-loading"/>
                            <template v-else>
                                <template v-if="notifications.length > 0">
                                    <div v-for="item in notifications" class="notification-item"
                                         :class="item.read_at === null ? '' : 'already-read'">
                                        <div class="flex align-items-center w100" @click="markNotificationAsRead(item, true)">
                                            <div class="image is-48x48 flex align-items-center">
                                                <img class="is-rounded" :src="getMemberGravatar(item)"
                                                     alt="gravatar-profile-image">
                                            </div>
                                            <div class="notification-content">
                                                <div class="notification-content-title" v-html="notificationTitle(item)"></div>
                                                <div class="notification-content-summary" v-html="updateViewContentForMention(notificationSummary(item))"></div>
                                                <div class="notification-created-at">{{ getDiffTimeFromNow(item.created_at) }}</div>
                                            </div>
                                        </div>
                                        <div v-if="item.read_at === null" class="notification-read-mark unread"
                                             @click="markNotificationAsRead(item, false)">
                                            <a href="#" :title="$t('community.notifications.mark-as-read')"
                                               class="tooltip small-tooltip ">
                                        <span class="flex align-items-center jc flex-column h100-percent" title="">
                                            <font-awesome-icon icon="fa fa-circle" class="unread-circle" />
                                        </span>
                                            </a>
                                        </div>
                                        <div v-else class="notification-read-mark"></div>
                                    </div>
                                </template>

                                <div v-else class="flex align-items-center jc mt1">
                                    {{ noUserResults }}
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="navbar-end">
                <div class="navbar-item has-dropdown is-right desktop-only-show">
                    <div class="dropdown flex align-items-center">
                        <div
                            class="dropdown-trigger navbar-link"
                            @click="showLangOptions"
                            v-click-outside="hideLangOptions"
                        >
                            <div class="lang-info">
                                {{ langIcon }}
                            </div>
                        </div>
                        <div
                            id="lang_options"
                            class="dropdown-menu"
                            :class="displayLangSelectDropdown ? 'show' : ''">
                            <div class="dropdown-content">
                                <a v-for="lang in this.langs" class="navbar-item"
                                    @click="changeFrontendLanguage(lang.value)">
                                    <img :src="lang.icon" class="lang-flag-icon" />
                                    <span>&nbsp;{{ lang.label }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="navbar-item desktop-only-show p0">
                    <div class="flex align-items-center ">
                        <button class="button is-medium community-btn text-uppercase mr1" @click="showSignup">
                            {{ $t('home.signup') }}
                        </button>

                        <button class="button is-medium community-btn text-uppercase" @click="showLogin">
                            {{ $t('home.login') }}
                        </button>
                    </div>
                </div>

                <div class="navbar-item mobile-only-show">
                    <div class="mobile-login-link" @click="showLogin">
                        {{ $t('home.login') }}
                    </div>
                </div>

            </div>
        </template>
    </div>
</template>

<script>
import moment from 'moment'
import getUserName from "../mixins/util";
import getMemberGravatar from "../mixins/util";
import getUserGravatar from "../mixins/util";
import updateViewContentForMention from "../mixins/util";
import languages from '../data/languages';
import { MemberAccess } from '../data/enums';
import formatAmountWithCurrency from "../mixins/util";

export default {
    name: 'CommunityTopRightNav',
    mixins: [
        getUserName,
        getMemberGravatar,
        getUserGravatar,
        formatAmountWithCurrency,
        updateViewContentForMention
    ],
    data() {
        return {
            MemberAccess,
            langs: [],
            chatLoading: false,
            notificationLoading: false,
            noUserResults: this.$t('common.no-result'),
            defaultAvatar: '/assets/img/default.png',

            displayUserInfo: false,
            displayChat: false,
            displayChatAction: false,
            displayNotifications: false,
            displayNotificationAction: false,
            displayLangSelectDropdown: false
        };
    },
    mounted() {
        this.langs = languages;

        // Listen for new chat message event, add message to messages
        Echo.channel(`App.User.${this.userId}`)
            .listen('.chat.message.sent', async event => {
                if (this.auth && this.memberExist) {
                    if (this.modalShow && this.modalType === 'ChatDetail' && typeof this.modalExtraData.id !== 'undefined' && this.modalExtraData.id === event.fromId) {
                        await this.$store.dispatch('ADD_NEW_CHAT_MESSAGE', {
                            id: event.id
                        });

                        setTimeout(() => {
                            var objDiv = document.getElementById("chat_detail_content");
                            objDiv.scrollTo({ top: objDiv.scrollHeight, behavior: 'smooth' });
                        }, 500);
                    } else {
                        await this.$store.dispatch('GET_CHAT_USERS');
                    }
                }
            })

        // Listen for new chat message event, add message to messages
        Echo.channel(`App.Member.${this.memberId}`)
            .listen('.notification.created', async event => {
                if (this.auth && this.memberExist && event.id === this.community.id) {
                    if (event.action === 'approved_to_join' || event.action === 'declined_to_join') {
                        await this.$store.dispatch('GET_COMMUNITY', this.url);
                    } else {
                        await this.$store.dispatch('GET_NOTIFICATIONS');
                    }
                }
            })
    },
    computed: {
        /**
         * Return auth status
         */
        auth() {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns user
         */
        user() {
            return this.$store.state.auth.user;
        },

        /**
         * Returns user id
         */
        userId() {
            return this.user ? this.user.id : null;
        },

        /**
         * Return 'dropdown' class when not mobile
         */
        dropdown() {
            return this.open ? 'navbar' : '';
        },

        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
        },

        /**
         * Returns member id
         */
        memberId() {
            return this.member ? this.member.id : null;
        },

        /**
         * Returns member existence
         */
        memberExist() {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Return community url
         */
        url() {
            return this.community.url;
        },

        /**
         * Returns access of member
         */
        access() {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * Return if the navBar is open or not (mobile only)
         */
        open() {
            return this.$store.state.communitycenter.navBarOpen;
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
         * Logs user to wolfeo.me
         */
        wolfeoLoginUrl() {
            return this.$store.state.communitycenter.wolfeoLoginUrl;
        },

        /**
         * The current locale
         */
        langIcon() {
            return this.$i18n.locale.toUpperCase();
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
         * Returns unread chat users count
         */
        unreadChatsCnt() {
            return this.$store.state.communitycenter.unreadChatsCnt;
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
         * Get | Set search member filter string
         */
        searchMemberFilter: {
            get() {
                return this.$store.state.communitycenter.searchMemberFilter;
            },
            set(v) {
                this.$store.commit('setSearchMemberFilter', v);
            }
        },

        /**
         * Return active tab
         */
        selectedTab() {
            return this.$store.state.communitycenter.selectedTab;
        },

        /**
         * Show modal
         * @bool
         */
        modalShow() {
            return this.$store.state.modal.show;
        },

        /**
         * Shortcut for modal.type
         */
        modalType() {
            return this.$store.state.modal.type;
        },

        /**
         * extra data of child modal
         */
        modalExtraData() {
            return this.$store.state.modal.extraData;
        },

        /**
         * Return legal show status
         */
        showLegal ()
        {
            return this.$store.state.auth.showLegal;
        },
    },
    methods: {
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
         * Open or close the navbar on mobile
         */
        goToProfile() {
            if (this.auth) {
              this.$store.commit('showModal', {
                type: 'CommunitySetting',
                extraData: 'profile',
                transparent: true
              });
            } else {
                this.showLogin();
            }
        },

        showLangOptions() {
            this.displayLangSelectDropdown = !this.displayLangSelectDropdown;
        },

        hideLangOptions() {
            this.displayLangSelectDropdown = false;
        },

        showUserInfo() {
            this.displayUserInfo = !this.displayUserInfo;
        },

        hideUserInfo() {
            this.displayUserInfo = false;
        },

        showUserInfoForMobile() {
            if (this.access === MemberAccess.ALLOWED) {
                this.$store.commit('showModal', {
                    type: 'TopRightNavContent',
                    transparent: true
                });
            }
        },

        async showChatUsers() {
            await new Promise((resolve) => setTimeout(() => { resolve(); }, 500));
            this.displayChat = true;
            this.chatLoading = true;
            await this.$store.dispatch('GET_CHAT_USERS');
            this.chatLoading = false;
        },

        hideChat() {
            this.displayChat = false;
        },

        async showNotifications() {
            await new Promise((resolve) => setTimeout(() => { resolve(); }, 500));
            this.displayNotifications = !this.displayNotifications;
            this.notificationLoading = true;
            await this.$store.dispatch('GET_NOTIFICATIONS');
            this.notificationLoading = false;
        },

        hideNotifications() {
            this.displayNotifications = false;
        },

        /**
         * Show login modal
         */
        showLogin() {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },

        /**
         * Show signup modal
         */
        showSignup() {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Signup',
                transparent: true
            });
        },

        /**
         * Change the main language the User uses to display Wolfeo
         *
         * en, fr
         *
         * This includes how we communicate with them
         */
        async changeLanguage(lang) {
            await this.$store.dispatch('CHANGE_USER_LANGUAGE', { lang: lang, reload: false });
            localStorage.setItem('lang', lang);
            if (this.$i18n) {
                this.$i18n.locale = lang;
            }

            window.dispatchEvent(new CustomEvent('lang-localstorage-changed', {
                detail: {
                    storage: localStorage.getItem('lang')
                }
            }));
        },

        changeFrontendLanguage(lang) {
            localStorage.setItem('lang', lang);
            if (this.$i18n) {
                this.$i18n.locale = lang;
            }
        },

        showChatAction() {
            this.displayChatAction = !this.displayChatAction;
        },

        hideChatAction() {
            this.displayChatAction = false;
        },

        showNotificationAction() {
            this.displayNotificationAction = !this.displayNotificationAction;
        },

        hideNotificationAction() {
            this.displayNotificationAction = false;
        },

        /**
         * Select chat filter
         */
        async selectChatUserFilter(key) {
            this.$store.commit('setChatUserFilter', key);

            this.chatLoading = true;
            await this.$store.dispatch('GET_CHAT_USERS');
            this.chatLoading = false;
        },

        /**
         * Select notification filter
         */
        async selectNotificationFilter(key) {
            this.$store.commit('setNotificationFilter', key);

            this.notificationLoading = true;
            await this.$store.dispatch('GET_NOTIFICATIONS');
            this.notificationLoading = false;
        },

        notificationTitle(item) {
            let html = '';

            if (item.object_type === 'approved_to_join' || item.object_type === 'declined_to_join') {
                html = '<div class="notification-title-owner">' + this.community.name + '</div>&nbsp;&nbsp;' + this.$t('community.notifications.message.' + item.object_type);
            } else {
                html = '<div class="notification-title-owner">' + item.user.name + '</div>&nbsp;' + this.$t('community.notifications.message.' + item.object_type);
            }

            return html;
        },

        notificationSummary(item) {
          let html = item.summary;
          if (item.object_type === 'new_payment') {
            let summary = item.summary.split('/');
            html = this.formatAmountWithCurrency(summary[1], summary[0] / 100) + ' / ' + summary[2];
          }

          return html;
        },

        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow(date) {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        async markNotificationAsRead(item, showDetail) {
            this.notificationLoading = true;
            await this.$store.dispatch('MARK_NOTIFICATION_AS_READ', {
                id: item.id,
                objectType: item.object_type,
                objectId: item.object_id,
                showDetail
            });
            this.notificationLoading = false;
        },

        async markNotificationAllAsRead() {
            this.notificationLoading = true;
            await this.$store.dispatch('MARK_NOTIFICATION_AS_READ');
            this.notificationLoading = false;
        },

        async markChatAllAsRead() {
            this.chatLoading = true;
            await this.$store.dispatch('MARK_CHAT_ALL_AS_READ');
            this.chatLoading = false;
        },

        /**
         * Search User Filter when inputing
         *
         * Events are grouped and fired once after 500ms
         * this.timeout is created dynamically
         *
         */
        searchUserFilteredInput() {
            this.chatLoading = (this.searchUserFilter.length > 0);
            this.noUserResults = this.$t('common.no-result');

            if (this.timeout) {
                clearTimeout(this.timeout);
            }

            this.timeout = setTimeout(async () => {
                await this.$store.dispatch('GET_CHAT_USERS');
                if (this.chatUsers.length === 0) {
                    this.noUserResults = this.$t('common.no-result-for').replace('#searchFilter#', this.searchUserFilter);
                }

                this.chatLoading = false;
            }, 500);
        },

        /**
         * Search Member Filter when inputing
         *
         * Events are grouped and fired once after 500ms
         * this.timeout is created dynamically
         *
         */
        searchMemberFilteredInput() {
            if (this.timeout) clearTimeout(this.timeout);

            this.timeout = setTimeout(async () => {
                await this.$store.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');
            }, 500);
        },

        async markChatAsRead(user, showDetail) {
            await this.$store.dispatch('GET_CHAT_DETAIL', {
                communityId: this.community.id,
                fromId: this.user.id,
                toId: user.id,
                showDetail
            });

            this.chatLoading = true;
            await this.$store.dispatch('GET_CHAT_USERS');
            this.chatLoading = false;
        },

        goToUsers() {
            const params = this.$route.path.split("/");
            if (typeof params[1] !== 'undefined' && params[1] === 'legal') {
                return;
            }

            this.$router.push('/' + this.community.url + '/users').catch(() => { });
            this.$store.commit('setCommunityTab', 'users');
        },

        /**
         * Return message created date
         */
        getMessageCreatedDate(createdAt) {
            return createdAt ? moment(createdAt).locale(this.$i18n.locale).format("LLL") : '';
        },

        /**
         * Return chat item class
         */
        chatItemClass(item) {
            return item.lastChat && item.lastChat.read_at === null && item.lastChat.to_id === this.user.id ? '' : 'already-read';
        },

        stripSpanTag(content) {
            return content.replace(/<\/?span[^>]*>/g, "");
        },
    }
}
</script>

<style scoped>
.dropdown-menu{
    top: 60px;
}
.navbar-user-info {
    display: flex;
    flex-direction: column;
    text-align: right;
    margin-right: .5em;
}

.lang-info {
    color: #7957d5;
    cursor: pointer;
    font-size: 18px;
}

.navbar-user-name {
    font-size: .8em;
    font-weight: 700;
    white-space: nowrap;
}

.navbar-user-email {
    color: lightslategray;
    font-size: .7em;
}

.dropdown-item.selected-item {
    background-color: rgb(250, 227, 172);
}

.lang-flag-icon {
    height: 1.5em;
    margin-right: 0.5em;
}

#chats_section,
#notifications_section {
    display: none;
    min-width: 450px;
    top: 70px;
    right: 0.75em;
    padding-top: 4px;
    position: absolute;
    left: auto;
    z-index: 20;
}

#chat_action_content,
#notification_action_content {
    display: none;
    position: absolute;
    right: 0px;
}

.chats-header,
.notifications-header {
    padding: 7px 15px;
    border-bottom: 1px solid rgb(228, 228, 228);
}

.mobile-login-link {
    text-transform: uppercase;
    cursor: pointer;
    font-weight: bold;
    color: rgb(144, 144, 144);
}

.mobile-login-link:hover {
    color: rgb(32, 33, 36);
}

@media only screen and (max-width: 600px) {
    .member-search-filter {
        width: 180px;
    }
}

</style>
