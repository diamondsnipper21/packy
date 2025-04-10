<template>
    <nav class="navbar nav-container">
        <div class="container">
            <div class="navbar-brand-section">
                <div class="flex align-items-center">
                    <div class="navbar-brand ml-0">
                        <div class="dropdown community-switch mobile-only-show" v-if="communities.length > 0">
                            <div
                                class="navbar-burger burger dropdown-trigger mr1"
                                @click.stop="toggleCommunitySwitchMobile"
                                v-click-outside="hideCommunitySwitchMobile">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <div
                                id="community_switch_content_for_mobile"
                                class="dropdown-menu"
                                :class="displayCommunitySwitchMobile ? 'show' : ''"
                            >
                                <div class="dropdown-content">
                                    <div @click="createCommunity()" class="dropdown-item font-weight-600">
                                        <div class="community-item-logo create-community-logo">
                                            <font-awesome-icon icon="fa fa-plus" />
                                        </div>
                                        <div class="community-item-name">
                                            {{ $t('community.community.create-community') }}
                                        </div>
                                    </div>

                                    <div v-for="(item, index) in communities" :key="item.id"
                                        @click.stop="selectCommunity(item.url)" class="dropdown-item font-weight-600"
                                        :class="community.id === item.id ? 'selected-item' : ''">
                                        <div class="community-item-logo">
                                            <img v-if="item.logo !== '' && item.logo !== null" :src="item.logo"
                                                class="community-item-logo-img" />
                                            <div v-else class="item-logo-placeholder">
                                                {{ logoPlaceholder(item.name) }}
                                            </div>
                                        </div>
                                        <div class="community-item-name">
                                            {{ item.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logo / Left nav -->
                        <div v-if="typeof community.name !== 'undefined'" class="community-logo-section"
                            @click="goToCommunity">
                            <img v-if="communityLogo !== '' && communityLogo !== null" :src="communityLogo"
                                class="community-logo" />
                            <div v-else class="community-logo-placeholder">
                                {{ logoPlaceholder(community.name) }}
                            </div>
                            <div class="community-name">
                                {{ this.community.name }}
                            </div>
                        </div>
                        <img v-else-if="this.settings?.logo_dark" :src="this.settings?.logo_dark"
                            class="navbar-item main-logo pointer" @click="goToCustomer" />
                        <div v-else class="empty-community">{{ $t('community.community.empty') }}</div>
                    </div>

                    <div
                        class="dropdown community-switch desktop-only-show"
                        v-if="communities.length > 0">
                        <div class="dropdown-trigger"
                             @click="showCommunitySwitch"
                             v-click-outside="hideCommunitySwitch">
                            <a href="#" :title="$t('community.community.switch-communities')" class="tooltip switch-tooltip">
                                <span class="flex align-items-center jc flex-column h100-percent" title="">
                                    <font-awesome-icon icon="fa fa-chevron-up" class="font-12px font-weight-600" />
                                    <font-awesome-icon icon="fa fa-chevron-down" class="font-12px font-weight-600" />
                                </span>
                            </a>
                        </div>

                        <div
                            id="community_switch_content"
                            class="dropdown-menu"
                            :class="displayCommunitySwitch ? 'show' : ''"
                        >
                            <div class="dropdown-content">
                                <div @click="createCommunity()" class="dropdown-item font-weight-600">
                                    <div class="community-item-logo create-community-logo">
                                        <font-awesome-icon icon="fa fa-plus" />
                                    </div>
                                    <div class="community-item-name">
                                        {{ $t('community.community.create-community') }}
                                    </div>
                                </div>

                                <div v-for="(item, index) in communities" :key="item.id"
                                    @click="selectCommunity(item.url)" class="dropdown-item font-weight-600"
                                    :class="community.id === item.id ? 'selected-item' : ''">
                                    <div class="community-item-logo">
                                        <img v-if="item.logo !== '' && item.logo !== null" :src="item.logo"
                                            class="community-item-logo-img" />
                                        <div v-else class="item-logo-placeholder">
                                            {{ logoPlaceholder(item.name) }}
                                        </div>
                                    </div>
                                    <div class="community-item-name">
                                        {{ item.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <CommunityTopRightNav />
            </div>
        </div>

        <div v-if="[CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status) && (access === MemberAccess.ALLOWED || privacy === 'public')" class="tabs-container">
            <template v-for="tab in tabs">
                <p v-show="displayTab(tab)" class="community-tab" :class="tabClass(tab)" @click="selectTab(tab)">{{ translate(tab) }}</p>
            </template>
        </div>
    </nav>

    <InactiveBar v-if="isManager(role)" />
    <SubscriptionOverdueBar v-if="member.subscription && member.subscription.status === 'overdue'"/>
</template>

<script>
import CommunityTopRightNav from './CommunityTopRightNav';
import { MemberAccess, CommunityStatus } from '../data/enums';
import SubscriptionOverdueBar from "./General/SubscriptionOverdueBar.vue";
import InactiveBar from "./General/InactiveBar.vue";
import isManager from "../mixins/util";

export default {
    name: 'CommunityTopNav',
    components: {
        InactiveBar, SubscriptionOverdueBar,
        CommunityTopRightNav
    },
    mixins: [
        isManager
    ],
    data() {
        return {
            MemberAccess,
            CommunityStatus,
            displayCommunitySwitch: false,
            displayCommunitySwitchMobile: false
        }
    },
    computed: {
        /**
         * Returns current member
         */
        currentMember ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns role of member
         */
        role() {
            return this.memberExist ? this.currentMember.role : null;
        },

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
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns community privacy
         */
        privacy() {
            return this.community.privacy;
        },

        /**
         * Returns access of member
         */
        access() {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * Returns community logo
         */
        communityLogo() {
            return this.community.logo;
        },

        /**
         * Returns communities
         */
        communities() {
            return this.$store.state.member.communities;
        },

        /**
         * Returns member existence
         */
        memberExist() {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns tabs
         */
        tabs() {
            let tabs = ['community', 'ressources', 'calendar', 'member', 'about'];
            if (this.memberExist) {
                tabs = ['community', 'ressources', 'calendar', 'member', 'rankings', 'about'];
            }

            return tabs;
        },

        /**
         * Returns settings
         */
        settings() {
            return this.$store.state.communitycenter.settings;
        },

        /**
         * Add ' is-active' when navbar is open
         */
        navBurger() {
            return this.open ? 'navbar-burger burger is-active' : 'navbar-burger burger';
        },

        /**
         * Return if the navBar is open or not (mobile only)
         */
        open() {
            return this.$store.state.communitycenter.navBarOpen;
        },

        /**
         * Return active tab
         */
        selectedTab() {
            let selectedTab = this.$store.state.communitycenter.selectedTab;
            document.title = this.translate(selectedTab) + ' - ' + this.community.name;

            return selectedTab;
        }
    },
    methods: {
        /**
        * Go to community
        */
        goToCommunity() {
            if (this.auth) {
                let tab = 'community';
                if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
                    tab = 'about';
                }

                let path = '/' + this.community.url;
                if (tab !== 'community') {
                    path += '/' + tab;
                }

                this.$router.push(path).catch(() => {});
                this.$store.commit('setCommunityTab', tab);
            } else {
                this.showLogin();
            }
        },

        /**
        * Go to customer
        */
        goToCustomer() {
            this.$router.push('/customer/index');
        },

        /**
        * Select tab
        */
        selectTab(tab) {
            if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
                tab = 'about';
            }

            let path = '/' + this.community.url;
            if (tab !== 'community') {
                path += '/' + tab;
            }

            this.$router.push(path).catch(() => { });

            this.$store.commit('setCommunityTab', tab);
        },

        /**
         * Return class for selected or unselected tab
         */
        tabClass(tab) {
            return (tab === this.selectedTab)
                ? 'tab-selected'
                : '';
        },

        displayTab(tab) {
            if (tab === 'ressources') {
                tab = 'classrooms';
            }

            if (typeof this.community[`tab_${tab}`] === 'undefined') {
                return true;
            }

            if (this.community[`tab_${tab}`]) {
                return true;
            }

            return false;
        },

        /**
         * Return translated value
         */
        translate(tab) {
            return this.$t(`community.tabs.${tab}`);
        },

        /**
         * Create community
         */
        createCommunity(id) {
            if (this.auth) {
              this.$store.commit('resetCommunity');
              this.$store.commit('showModal', {
                type: 'NewCommunity',
                transparent: true
              });
            } else {
                this.showLogin();
            }
        },

        /**
         * Create temp community
         */
        createTempCommunity(id) {
            if (this.auth) {
              this.$store.commit('resetCommunity');
              this.$store.commit('showModal', {
                type: 'NewTempCommunity',
                transparent: true
              });
            } else {
                this.showLogin();
            }
        },

        /**
         * Select community
         */
        async selectCommunity(url) {
            this.$store.commit('communityLoading', true);

            this.$store.commit('setCategoryFilter', 0);
            this.$store.commit('setPostFilter', 'none');
            this.$store.commit('setPostSort', 'newest');

            await this.$store.dispatch('GET_COMMUNITY', url);

            // Close opened add post modal
            this.$store.commit('resetCommunityPost');
            this.$store.commit('setAddPostShow', false);

            this.$store.commit('communityLoading', false);
        },

        /**
         * Get first characters from every words in string
         */
        logoPlaceholder(name) {
            const matches = name.match(/\b(\w)/g);
            const acronym = matches.join('');

            return acronym.slice(0, 2).toUpperCase();
        },

        showCommunitySwitch() {
            this.displayCommunitySwitch = !this.displayCommunitySwitch;
        },

        hideCommunitySwitch() {
            this.displayCommunitySwitch = false;
        },

        toggleCommunitySwitchMobile() {
            this.displayCommunitySwitchMobile = !this.displayCommunitySwitchMobile;
        },

        hideCommunitySwitchMobile() {
            setTimeout(() => {
                this.displayCommunitySwitchMobile = false;
            }, 200);
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
    }
}
</script>

<style scoped>
.nav-container {
    padding-top: 0px !important;
    padding-bottom: 0px !important;
}

.empty-community {
    font-weight: 500;
    white-space: nowrap;
    display: flex;
    align-items: center;
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

.community-tab {
    margin-right: 1em;
    cursor: pointer;
    color: #8a8a8a;
    padding-bottom: 0.5em;
    border-bottom: 1px solid transparent;
    display: flex;
    align-items: center;
    white-space: nowrap;
}

.community-tab:hover {
    color: #4a4a4a;
    border-bottom: 1px solid #ccc;
}

.navbar-brand-section {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.navbar-brand {
    margin-right: 15px;
    align-items: center;
}

.community-switch {
    height: 40px;
    width: 40px;
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
}

.community-switch i {
    font-size: 12px;
    color: rgb(144, 144, 144);
}

.community-switch:hover {
    background-color: rgb(228, 228, 228) !important;
    border: 1px solid rgb(228, 228, 228) !important;
}

.community-switch:hover i {
    color: rgb(32, 33, 36);
}

.dropdown-item.selected-item {
    background-color: rgb(250, 227, 172);
}

.community-item-logo {
    margin-right: 10px;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    background-color: #fff;
}

.community-item-logo-img {
    width: 25px;
    height: 25px;
    object-fit: cover;
}

.community-item-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 500;
    font-style: normal;
    font-size: 16px;
}

.create-community-logo {
    background-color: whitesmoke;
}

.create-community-logo i {
    font-size: 16px;
}

.lang-flag-icon {
    height: 1.5em;
    margin-right: 0.5em;
}

.tabs-container {
    display: flex;
    align-items: center;
    max-width: 1170px !important;
    margin: 0 auto;
    width: 100%;
    overflow: auto;
}

.tabs-container::-webkit-scrollbar {
    width: 1px;
}

#community_switch_content_for_mobile {
    left: 0px;
    right: auto;
}

@media only screen and (max-width: 600px) {
    .community-tab {
        font-size: 14px;
    }
}
</style>
