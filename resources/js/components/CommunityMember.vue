<template>
    <div class="container pl-0 pr-0">
        <loading v-if="loading" :active.sync="loading" :is-full-page="true" />
        <template v-else>
            <!-- No community Exist -->
            <div v-if="typeof community.name === 'undefined' || ![CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status)"
                class="empty-section">
                {{ $t('community.community.empty-community-placeholder') }}
            </div>
            <div v-else class="columns">
                <div class="column is-two-thirds left-column-for-desktop">
                    <div class="members-header" id="member_filter_list_wrapper">
                        <div class="member-filters" :class="memberFiltersClass" id="member_filter_list">
                            <span v-for="filter in memberFilters" :key="filter.key" class="member-filter"
                                :class="memberFilter === filter.key ? 'selected' : ''"
                                @click="selectMemberFilter(filter.key)">
                                {{ filter.label }} {{ filter.count }}
                            </span>

                            <span v-if="showMoreLessLink" class="member-filter-more-link-container" :class="moreLinkClass">
                                <span class="member-filter-more-link" @click="expandMemberFilters">
                                    {{ $t('community.community.more-link') }}
                                </span>
                            </span>

                            <span v-if="showMoreLessLink" class="member-filter-less-link" :class="lessLinkClass"
                                @click="shrinkMemberFilters">
                                {{ $t('community.community.less-link') }}
                            </span>
                        </div>

                        <button v-if="auth" class="button is-medium community-blue-btn text-uppercase" @click="showInviteModal">
                            {{ $t('community.members.invite') }}
                        </button>
                    </div>

                    <!-- No members Exist -->
                    <div v-if="this.members.length === 0" class="box empty-section mt1">
                        {{ $t('community.members.empty-members-placeholder') }}
                    </div>

                    <div v-else class="box mt1" ref="memberRef">
                        <div v-for="(item, index) in members" :key="item.id" :class="{ 'first-item': index === 0 }" class="member-item">
                            <img :src="getMemberGravatar(item)" class="community-customer-gravatar" />
                            <div class="member-item-content">
                                <div class="member-item-header">
                                    <div>
                                        <div class="member-item-name">
                                            {{ getMemberName(item) }}
                                            <span v-if="item.user_id === community.user_id" class="member-owner-mark">
                                                ({{ $t('community.members.owner') }})
                                            </span>
                                        </div>
                                        <div class="member-item-info">
                                            {{ getMemberTag(item) }}
                                        </div>
                                    </div>

                                    <div class="member-item-header-actions desktop-only-show">
                                        <div v-if="memberFilter === MemberFilter.PENDING" class="mr-05">
                                            <button class="button is-medium community-btn text-uppercase mr-05"
                                                :class="approveActionItemId === item.id ? 'is-loading' : ''"
                                                @click="approve(item)">
                                                {{ $t('community.members.approve') }}
                                            </button>
                                            <button class="button is-medium community-btn text-uppercase"
                                                @click="decline(item)">
                                                {{ $t('community.members.decline') }}
                                            </button>
                                        </div>

                                        <button v-if="memberExist && item.id !== member.id"
                                            class="button is-medium community-btn mr-05" @click="showChatModal(item.user)">
                                            {{ $t('community.members.chat') }}
                                            <font-awesome-icon icon="fa fa-comment" :class="'ml-05'" />
                                        </button>

                                        <button v-if="showMembershipSettingBtn(item)"
                                            class="button is-medium community-btn mr-05"
                                            @click="showMembershipSettingsModal(item)">
                                            {{ $t('community.members.setting-modal.membership') }}
                                            <font-awesome-icon icon="fa fa-gear" :class="'ml-05'" />
                                        </button>

                                        <button v-if="showRemoveBtn(item)"
                                            class="button is-medium community-btn"
                                            @click="showRemoveMemberModal(item)">
                                            {{ $t('common.remove') }}
                                            <font-awesome-icon icon="fa fa-trash" :class="'ml-05'" />
                                        </button>
                                    </div>
                                </div>

                                <div class="member-item-desc">
                                    {{ (item.content !== null && item.content !== '') ? item.content : '-' }}
                                </div>

                                <template v-if="bannedMemberChecked(item)">
                                    <div class="member-item-banned">
                                        <font-awesome-icon icon="fa fa-calendar" class="mr1" />
                                        {{ $t('community.members.banned') }} {{ getMemberBannedInfo(item) }}
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="member-item-online">
                                        <div v-if="item.user && item.user.online">
                                            <font-awesome-icon icon="fa fa-circle" class="mr1 online-now" />
                                            {{ $t('community.members.online-now') }}
                                        </div>
                                        <div v-else>
                                            <font-awesome-icon icon="fa fa-clock" class="mr1" />
                                            {{ $t('community.members.active') }} {{ getDiffTimeFromNow(item.last_activity) }}
                                        </div>
                                    </div>
                                    <div class="member-item-joined">
                                        <font-awesome-icon icon="fa fa-calendar" class="mr1" />
                                        {{ $t('community.members.joined') }} {{ getMemberJoinedInfo(item) }}
                                    </div>
                                </template>
                                    
                                <div class="member-item-location">
                                    <font-awesome-icon icon="fa fa-location" class="mr1" />
                                    {{ getCountryTxt(item.country) }}
                                </div>

                                <div class="mobile-only-show mt-05">
                                    <div class="flex align-items-center">
                                        <div v-if="memberFilter === MemberFilter.PENDING" class="mr-05">
                                            <button class="button is-medium community-btn text-uppercase mr-05"
                                                :class="approveActionItemId === item.id ? 'is-loading' : ''"
                                                @click="approve(item)">
                                                {{ $t('community.members.approve') }}
                                            </button>
                                            <button class="button is-medium community-btn text-uppercase"
                                                @click="decline(item)">
                                                {{ $t('community.members.decline') }}
                                            </button>
                                        </div>

                                        <button v-if="memberExist && item.id !== member.id"
                                            class="button is-medium community-btn mr-05" @click="showChatModal(item)">
                                            <font-awesome-icon icon="fa fa-comment" class="member-action-icon" />
                                        </button>

                                        <button v-if="showMembershipSettingBtn(item)"
                                            class="button is-medium community-btn mr-05"
                                            @click="showMembershipSettingsModal(item)">
                                            <font-awesome-icon icon="fa fa-gear" class="member-action-icon" />
                                        </button>

                                        <button v-if="showRemoveBtn(item)"
                                            class="button is-medium community-btn"
                                            @click="showRemoveMemberModal(item)">
                                            <font-awesome-icon icon="fa fa-trash" class="member-action-icon" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <Pagination :total="total" :perPage="perPage" :current="currentPage"
                            pageAction="GET_PAGINATED_COMMUNITY_MEMBERS" class="mb1" />

                    </div>
                </div>

                <div class="column is-one-third right-column-for-desktop">
                    <CommunitySummarySidebarTop />
                </div>
            </div>
        </template>
    </div>
</template>

<script>
import moment from 'moment'

import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import Pagination from "../components/General/Elements/Pagination.vue";
import CommunitySummarySidebarTop from "./CommunitySummarySidebarTop.vue";

import getMemberName from "../mixins/util";
import getMemberTag from "../mixins/util";
import getMemberGravatar from "../mixins/util";
import isManager from "../mixins/util";

import countries from '../data/countries';
import { MemberAccess, MemberFilter, CommunityStatus, MembershipSettingViewType } from '../data/enums';


export default {
    name: 'CommunityMember',
    mixins: [
        getMemberName,
        getMemberTag,
        getMemberGravatar,
        isManager
    ],
    components: {
        Loading,
        Pagination,
        CommunitySummarySidebarTop
    },
    data() {
        return {
            MemberAccess,
            MemberFilter,
            CommunityStatus,
            MembershipSettingViewType,

            loading: false,
            activeFilter: 'members',
            countries: [],
            approveActionItemId: 0,
            removeActionItemId: 0,

            showMoreLessLink: false,

            memberFiltersClass: '',
            moreLinkClass: '',
            lessLinkClass: 'hidden',
        };
    },
    async created() {
        moment.locale(this.$i18n.locale);
        this.countries = countries;

        if (this.community.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
            window.location.href = '/' + this.community.url + '/about';
        } else {
            this.loading = true;
            await this.$store.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');
            this.loading = false;
        }
    },
    mounted() {
        
    },
    watch: {
        'currentPage': function (v) {
            if (this.$refs.memberRef) {
                window.scroll({
                    top: this.$refs.memberRef.offsetTop,
                    left: 0,
                    behavior: 'smooth'
                })
            }
        },
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
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
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
         * Returns member filters
         */
        memberFilters() {
            let memberFilters = [{
                key: "all",
                label: this.$t('community.members.members'),
                count: this.allowedMembers.length,
                selected: true
            }, {
                key: "admin",
                label: this.$t('community.members.admins'),
                count: this.adminMembers.length,
                selected: false
            }, {
                key: "moderator",
                label: this.$t('community.members.moderators'),
                count: this.moderatorMembers.length,
                selected: false
            }, {
                key: "online",
                label: this.$t('community.members.online'),
                count: this.onlineMembers.length,
                selected: false
            }];

            if (this.isCreator || this.isManager(this.role)) {
                memberFilters.push({
                    key: "pending",
                    label: this.$t('community.members.requests'),
                    count: this.pendingMembers.length,
                    selected: false
                });

                memberFilters.push({
                    key: "banned",
                    label: this.$t('community.members.banned'),
                    count: this.bannedMembers.length,
                    selected: false
                });

                this.showMoreLessLink = true;
            }

            memberFilters = memberFilters.map((filter) => {
                if (filter.key === this.memberFilter) {
                    filter.selected = true;
                } else {
                    filter.selected = false;
                }

                return { ...filter };
            });

            return memberFilters;
        },

        /**
         * Returns status of member is creator or not
         */
        isCreator() {
            let isCreator = false;
            if (this.memberExist && this.community.user_id === this.member.user_id) {
                isCreator = true;
            }

            return isCreator;
        },

        /**
         * Returns super admin
         */
        isSuperAdmin() {
            return this.user && parseInt(this.user.id) === 1;
        },

        /**
         * Returns community members
         */
        paginatedMembers() {
            return this.$store.state.communitycenter.paginatedMembers;
        },

        /**
         * Returns community posts
         */
        members() {
            return (this.paginatedMembers.hasOwnProperty('data')) ? this.paginatedMembers.data : [];
        },

        /**
         * Returns bannedMembers
         */
        bannedMembers() {
            return this.$store.state.communitycenter.bannedMembers;
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers() {
            let allowedMembers = this.$store.state.communitycenter.allowedMembers;
            if (this.community.owner_show === 0) {
                allowedMembers = allowedMembers.filter(el => el.user_id !== this.community.user_id);
            }

            return allowedMembers;
        },

        /**
         * Returns requested admin members
         */
        adminMembers() {
            let adminMembers = this.$store.state.communitycenter.adminMembers;
            if (this.community.owner_show === 0) {
                adminMembers = adminMembers.filter(el => el.user_id !== this.community.user_id);
            }

            return adminMembers;
        },

        /**
         * Returns requested moderator members
         */
        moderatorMembers() {
            let moderatorMembers = this.$store.state.communitycenter.moderatorMembers;
            if (this.community.owner_show === 0) {
                moderatorMembers = moderatorMembers.filter(el => el.user_id !== this.community.user_id);
            }

            return moderatorMembers;
        },

        /**
         * Returns pending members
         */
        pendingMembers() {
            let pendingMembers = this.$store.state.communitycenter.pendingMembers;
            if (this.community.owner_show === 0) {
                pendingMembers = pendingMembers.filter(el => el.user_id !== this.community.user_id);
            }

            return pendingMembers;
        },

        /**
         * Returns member filter
         */
        memberFilter() {
            return this.$store.state.communitycenter.memberFilter;
        },

        /**
         * Returns online members
         */
        onlineMembers() {
            let onlineMembers = [];
            if (this.allowedMembers.length > 0) {
                this.allowedMembers.map((member, index) => {
                    if (member.user && member.user.online) {
                        onlineMembers.push(member);
                    }
                });
            }

            if (this.community.owner_show === 0) {
                onlineMembers = onlineMembers.filter(el => el.user_id !== this.community.user_id);
            }

            return onlineMembers;
        },

        /**
         * Returns button status class
         */
        button() {
            return this.processing ? ' is-loading' : '';
        },

        /**
         * Returns member existence
         */
        memberExist() {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        total() {
            return this.$store.state.communitycenter.paginatedMembers?.total || 0;
        },

        currentPage() {
            return this.$store.state.communitycenter.paginatedMembers?.current_page || 0;
        },

        perPage() {
            return this.$store.state.communitycenter.paginatedMembers?.per_page || 1;
        },
    },
    methods: {
        /**
         * Select member filter
         */
        async selectMemberFilter(key) {
            this.$store.commit('setMemberFilter', key);

            this.loading = true;

            await this.$store.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');

            this.loading = false;
        },

        /**
         * Show invite modal
         */
        showInviteModal() {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                this.$store.commit('showModal', {
                  type: 'CommunitySetting',
                  extraData: 'invite',
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

        /**
         * Get member joined info
         */
        getMemberJoinedInfo(member) {
            return moment(member.created_at).format("MMM Do, YYYY");
        },

        /**
         * Get member banned info
         */
        getMemberBannedInfo(member) {
            return moment(member.has_updated_at).format("MMM Do, YYYY");
        },
        
        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow(date) {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        /**
         * Approve member request
         */
        async approve(member) {
            if (this.auth) {
                if (this.isManager(this.role)) {
                    this.approveActionItemId = member.id;
                    await this.$store.dispatch('APPROVE_MEMBER_REQUEST', member.id);
                    this.approveActionItemId = 0;
                }
            } else {
                this.showLogin();
            }
        },

        /**
         * Decline member request
         */
        decline(member) {
            if (this.auth) {
                if (this.isManager(this.role)) {
                    this.$store.commit('setDeclineJoinFeedback', '');
                    this.$store.commit('setDeclineJoinShareNotify', false);
                    this.$store.commit('setModalSize', 'small');
                    this.$store.commit('showModal', {
                        type: 'DeclineMemberRequest',
                        extraData: member,
                        transparent: true
                    });
                }
            } else {
                this.showLogin();
            }
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
         * Get country text
         */
        getCountryTxt(value) {
            let countryTxt = '-';
            let selectedCountries = this.countries.filter(el => el.value === value);
            if (selectedCountries.length === 1) {
                countryTxt = selectedCountries[0].text;
            }

            return countryTxt;
        },

        /**
         * Expand member filters by clicking more link
         */
        expandMemberFilters() {
            this.moreLinkClass = 'hidden';
            this.lessLinkClass = '';
            this.memberFiltersClass = 'flex-wrap';
        },

        /**
         * Shrink member filters by clicking less link
         */
        shrinkMemberFilters() {
            this.moreLinkClass = '';
            this.lessLinkClass = 'hidden';
            this.memberFiltersClass = '';
        },

        async showChatModal(user) {
            await this.$store.dispatch('GET_CHAT_DETAIL', {
                communityId: this.community.id,
                fromId: this.user.id,
                toId: user.id,
                showDetail: true
            });
        },

        /**
         * Check banned membership role
         */
        bannedMemberChecked(item) {
            return [MemberAccess.BANNED].includes(item.access);
        },

        showRemoveMemberModal(member) {
            this.$store.commit('setModalSize', 'small');
            this.$store.commit('showModal', {
                type: 'DeleteConfirm',
                extraData: member,
                title: this.$t('community.members.delete-member-title'),
                description: this.$t('community.members.delete-member-desc').replace("#memberName#", this.getMemberName(member)).replace("#communityName#", this.community.name),
                button: this.$t('common.remove'),
                disabledConfirm: false,
                action: 'REMOVE_MEMBER',
                actionParam: member.id
            });
        },

        showMembershipSettingBtn(item) {
            let showMembershipSettingBtn = false;
            if (item.id !== this.member.id && item.user_id !== this.community.user_id) {
                if (this.isCreator || this.isManager(this.role)) {
                    showMembershipSettingBtn = true;
                }
            }

            return showMembershipSettingBtn;
        },

        showMembershipSettingsModal(member) {
            this.$store.commit('setMembershipSettingsProperty', {
                key: 'member',
                v: JSON.parse(JSON.stringify(member))
            });

            this.$store.commit('showModal', {
                type: 'MembershipSetting',
                extraData: MembershipSettingViewType.MEMBERSHIP,
                transparent: true
            });
        },

        showRemoveBtn(item) {
            let showRemoveBtn = false;
            if (this.memberFilter === MemberFilter.ALL && item.id !== this.member.id && item.user_id !== this.community.user_id) {
                if (this.isCreator) {
                    showRemoveBtn = true;
                } else if (this.isManager(this.role) && !this.isManager(item.role)) {
                    showRemoveBtn = true;
                }
            }

            return showRemoveBtn;
        },
    }
}
</script>

<style scoped>
.members-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    width: 100%;
    overflow: auto;
    padding-bottom: 5px;
}

.member-filters {
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    width: calc(100% - 100px);
}

.member-filter,
.member-filter-less-link {
    margin-right: 10px;
    margin-bottom: 5px;
    cursor: pointer;
    border: 1px solid rgb(228, 228, 228);
    border-radius: 25px;
    padding: 7px 15px;
    background-color: #fff;
    white-space: nowrap;
    color: rgb(144, 144, 144);
}


.member-filter:hover,
.member-filter-less-link:hover,
.member-filter-more-link:hover {
    background-color: rgb(228, 228, 228);
    color: rgb(32, 33, 36);
}


.member-filter.selected {
    color: #FFFFFF;
    border: 1px solid #909090;
    background-color: #909090;
}

.member-filter-more-link-container {
    position: absolute;
    right: 0px;
    height: 40px;
    margin-bottom: 5px;
    padding-left: 32px;
    background: linear-gradient(90deg, rgba(0, 0, 0, 0) 0%, rgb(244, 245, 248) 15%);
    display: flex;
    align-items: center;
}

.member-filter-more-link {
    cursor: pointer;
    border: 1px solid rgb(228, 228, 228);
    border-radius: 25px;
    padding: 7px 15px;
    background-color: #fff;
    white-space: nowrap;
    color: rgb(144, 144, 144);
}

.member-item {
    display: flex;
    border-top: 1px solid #ddd;
    padding: 15px 0px;
}

.member-item.first-item {
    border: none;
}

.no-border {
    border: none;
}

.member-item-content {
    padding-left: 15px;
    width: 100%;
}

.member-item-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.member-item-name {
    font-size: 16px;
    font-weight: 500;
    text-transform: capitalize;
}

.member-owner-mark {
    font-size: 14px;
    font-weight: 400;
    color: #7957d5;
}

.member-item-info {
    font-size: 13px;
    font-weight: bold;
    color: rgb(144, 144, 144);
}

.member-item-chat-link {
    color: rgb(144, 144, 144);
    font-weight: 500;
    padding: 7px 25px;
    text-align: center;
    cursor: pointer;
    border-radius: 3px;
    text-transform: uppercase;
}

.member-item-chat-link:hover {
    color: rgb(32, 33, 36);
}

.member-item-desc {
    padding: 10px 0px;
}

.member-item-online,
.member-item-joined,
.member-item-banned,
.member-item-location {
    padding: 3px 0px;
}

.member-item-header-actions {
    display: flex;
    align-items: center;
}

.member-item-header-actions button.control {
    border: none;
    background: transparent;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    text-align: center;
    cursor: pointer;
}

.member-item-header-actions button.control .link-icon {
    opacity: 0.6;
}

.member-item-header-actions button.control:hover {
    background: rgb(228, 228, 228);
}

.member-item-header-actions button.control:hover .link-icon {
    opacity: 1;
}

@media only screen and (max-width: 600px) {
    .member-filter,
    .member-filter-less-link {
        margin-right: 7px;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 14px;
    }

    .member-filter-more-link-container {
        height: 36px;
        padding-left: 20px;
    }

    .member-filter-more-link {
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 14px;
    }

    .member-item {
        padding: 10px 0px;
    }

    .member-item-content {
        padding-left: 10px;
    }

    .member-item-name {
        font-size: 14px;
    }

    .member-owner-mark {
        font-size: 12px;
    }

    .member-item-info {
        font-size: 12px;
    }

    .member-item-chat-link {
        padding: 5px 15px;
    }

    .member-item-desc {
        padding: 5px 0px;
        font-size: 14px;
    }

    .member-item-online,
    .member-item-joined,
    .member-item-banned,
    .member-item-location {
        padding: 1px 0px;
        font-size: 14px;
    }

    .member-item-header-actions button.control {
        width: 30px;
        height: 30px;
    }

    .member-action-icon {
        font-size: 13px;
    }
}
</style>
