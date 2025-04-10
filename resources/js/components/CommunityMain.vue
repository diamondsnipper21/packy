<template>
    <div class="container pl-0 pr-0">
        <div v-if="isNotAvailableCommunity" class="empty-section">
            {{ $t('community.community.empty-community-placeholder') }}
        </div>

        <div v-else class="columns">
            <div class="column is-two-thirds left-column-for-desktop">
                <AddNewCommunityPost v-if="addPostShow" />
                <div v-else class="box p0" @click="showAddPost">
                    <div class="add-new-community">
                        <div class="flex align-items-center">
                            <img v-if="member" class="community-customer-gravatar" :src="getMemberGravatar(member)"
                                alt="gravatar-profile-image">
                        </div>
                        <div class="add-new-community-text">
                            {{ $t('community.community.desc-placeholder') }}
                        </div>
                    </div>
                </div>
                <OnboardingCommunity v-if="community.onboarding_step !== CommunityOnboardSteps.COMPLETED && isManager(role)" />
                <div v-if="(isCreator || isManager(role)) && pendingMembers > 0"
                    class="membership-pending-request" @click="goToMembers('pending')">
                    <span class="membership-pending-request-count">
                        {{ this.pendingMembers }}
                    </span>
                    <div class="membership-pending-request-desc">
                        {{ $t('community.community.pending-request-count') }}
                    </div>
                </div>

                <div v-if="currentCommunityEvent !== null" class="current-event">
                    <div class="live-info" @click="goToUpEvent(currentCommunityEvent)">
                        <font-awesome-icon icon="fa fa-circle" />
                        &nbsp;&nbsp;
                        {{ $t('community.community.current-event.live') }}
                    </div>
                    <span class="current-event-link-title" @click="goToUpEvent(currentCommunityEvent)">
                        "{{ currentCommunityEvent.title }}"
                    </span>
                </div>

                <div v-else-if="upcomingCommunityEvent !== null" class="upcoming-event">
                    <font-awesome-icon icon="fa fa-calendar" />
                    &nbsp;
                    <span class="upcoming-event-link-title" @click="goToUpEvent(upcomingCommunityEvent)">
                        {{ upcomingCommunityEvent.title }}
                    </span>

                    <span class="upcoming-event-link-description">
                        {{ $t('community.community.upcoming-event-link.description').replace("#remainedTime#",
                            this.getDiffTimeFromNow(upcomingCommunityEvent.start_at)) }}
                    </span>
                </div>

                <div class="w100 flex align-items-start jcb" id="category_filter_list_wrapper">
                    <div class="category-filters" :class="categoryFiltersClass" id="category_filter_list">
                        <span v-if="categories.length > 0" class="category-filter"
                            :class="selectedCategoryId === 0 ? 'selected' : ''" @click="selectCategoryFilter(0)">
                            {{ $t('community.community.category-options.all') }}
                        </span>
                        <span v-for="category in categories" class="category-filter"
                            :class="selectedCategoryId === category.id ? 'selected' : ''"
                            @click="selectCategoryFilter(category.id)">
                            {{ category.title }}
                        </span>

                        <span v-if="showMoreLessLink" class="category-more-link-container" :class="moreLinkClass">
                            <span class="category-more-link" @click="expandCategoryFilters">
                                {{ $t('community.community.more-link') }}
                            </span>
                        </span>

                        <span v-if="showMoreLessLink" class="category-less-link" :class="lessLinkClass"
                            @click="shrinkCategoryFilters">
                            {{ $t('community.community.less-link') }}
                        </span>
                    </div>

                    <div class="other-post-filters">
                        <div class="dropdown">
                            <div 
                                class="dropdown-trigger other-post-filters-link" 
                                @click.stop="showPostFilterDropdown"
                                v-click-outside="hidePostFilterDropdown"
                            >
                                <font-awesome-icon icon="fa fa-filter" />
                            </div>
                            <div 
                                id="other_post_filter_content" 
                                class="dropdown-menu"
                                :class="displayPostFilterDropdown ? 'show' : ''"
                            >
                                <div class="dropdown-content">
                                    <div class="columns">
                                        <div class="column is-half p0">
                                            <div v-for="(column, index) in filterOptions" :key="column.key"
                                                class="other-filter-item">
                                                <input type="radio" :id="column.key" v-model="column.filter"
                                                    :value="sorted" @click="selectFilterOption(column.key)">
                                                <p class="dark-grey pl-05 font-14px"
                                                    @click="selectFilterOption(column.key)">
                                                    {{ $t(`community.community.filter-options.${column.key}`) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="column is-half p0 other-post-right-filter-container">
                                            <div v-for="(column, index) in sortOptions" :key="column.key"
                                                class="other-filter-item">
                                                <input type="radio" :id="column.key" v-model="column.sort"
                                                    :value="sorted" @click="selectSortOption(column.key)">
                                                <p class="dark-grey pl-05 font-14px"
                                                    @click="selectSortOption(column.key)">
                                                    {{ $t(`community.community.sort-options.${column.key}`) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <PendingCommunity
                    v-if="community.onboarding_step === CommunityOnboardSteps.COMPLETED && community.number_of_members <= 3 && isManager(role)" />

                <div class="post-list-section">
                    <PostList v-if="isManager(role)" :postType="PostType.SCHEDULE" :communityId="community?.id" class="mt1" />

                    <!-- No communityPosts Exist -->
                    <div v-if="!loading && totalPost === 0" class="empty-section box mt2">
                        {{ $t('community.community.empty-post-placeholder') }}
                    </div>

                    <!-- communityPosts Exist -->
                    <PostList :postType="PostType.REGULAR" :communityId="community?.id" class="mt1" />
                </div>
            </div>

            <div class="column is-one-third right-column-for-desktop">
                <CommunitySummarySidebarTop />
                <CommunitySummarySidebarBottom v-if="access === MemberAccess.ALLOWED || privacy === 'public'" />
            </div>
        </div>
    </div>
</template>

<script>
import moment from 'moment-timezone';
import MediaThumb from "./Media/MediaThumb";
import Pagination from "../components/General/Elements/Pagination.vue";
import AddNewCommunityPost from "./AddNewCommunityPost.vue";
import CommunitySummarySidebarTop from "./CommunitySummarySidebarTop.vue";
import CommunitySummarySidebarBottom from "./CommunitySummarySidebarBottom.vue";
import OnboardingCommunity from "./OnboardingCommunity";
import PendingCommunity from "./PendingCommunity";
import InactiveBar from "./General/InactiveBar";
import SubscriptionOverdueBar from "./General/SubscriptionOverdueBar";
import PostList from "./General/Posts/PostList";

import getMemberGravatar from "../mixins/util";
import isManager from "../mixins/util";
import { MemberAccess, CommunityStatus, CommunityOnboardSteps, PostType } from '../data/enums';

export default {
    name: 'CommunityMain',
    components: {
        MediaThumb,
        Pagination,
        CommunitySummarySidebarTop,
        CommunitySummarySidebarBottom,
        AddNewCommunityPost,
        OnboardingCommunity,
        PendingCommunity,
        InactiveBar,
        SubscriptionOverdueBar,
        PostList
    },
    mixins: [
        getMemberGravatar,
        isManager
    ],
    data() {
        return {
            MemberAccess,
            CommunityStatus,
            CommunityOnboardSteps,
            PostType,

            categoryFiltersClass: '',
            moreLinkClass: '',
            lessLinkClass: 'hidden',
            currentCommunityEvent: null,
            upcomingCommunityEvent: null,
            sorted: 1,

            filterOptions: [{
                key: "none",
                filter: 1
            }, {
                key: "pinned",
                filter: 0
            }, {
                key: "watching",
                filter: 0
            }, {
                key: "unread",
                filter: 0
            }, {
                key: "no-comments",
                filter: 0
            }],

            sortOptions: [{
                key: "newest",
                sort: 1
            }, {
                key: "oldest",
                sort: 0
            }],

            showMoreLessLink: false,
            
            displayPostFilterDropdown: false
        };
    },
    created() {
        moment.locale(this.$i18n.locale);
    },
    mounted() {
        let communityEvents = this.communityEvents;
        let currentUtcDatetime = moment().utc().format('YYYY-MM-DD HH:mm:ss');
        let currentUtcDatetimestamp = moment(currentUtcDatetime).format("X");
        
        if (communityEvents.length > 0) {
            communityEvents.sort((a, b) => moment(a.start_at) - moment(b.start_at));

            for (let i = 0; i < communityEvents.length; i++) {
                const event = communityEvents[i];

                let startAt = moment.tz(event.start_at, 'YYYY-MM-DD HH:mm:ss', event.timezone);
                let eventStartAtUtcDatetime = startAt.utc().format('YYYY-MM-DD HH:mm:ss');
                let eventStartAtUtcDatetimestamp = moment(eventStartAtUtcDatetime).format("X");

                let eventEndAtUtcDatetimestamp = 0;
                let endAt = event.end_at;
                if (endAt && endAt.startsWith('on_')) {
                    let endAtTz = moment.tz(endAt.replace('on_', ''), 'YYYY-MM-DD HH:mm:ss', event.timezone);
                    let eventEndAtUtcDatetime = endAtTz.utc().format('YYYY-MM-DD HH:mm:ss');
                    eventEndAtUtcDatetimestamp = moment(eventEndAtUtcDatetime).format("X");
                } else if (event.duration !== '') {
                    let durationMin = parseFloat(event.duration) * 60;
                    let eventEndDate = startAt.add(durationMin, 'minutes');
                    let eventEndAtUtcDatetime = eventEndDate.utc().format('YYYY-MM-DD HH:mm:ss');
                    eventEndAtUtcDatetimestamp = moment(eventEndAtUtcDatetime).format("X");
                }

                if (eventStartAtUtcDatetimestamp > currentUtcDatetimestamp) {
                    if (this.upcomingCommunityEvent === null) {
                        this.upcomingCommunityEvent = event;
                    }
                }

                if (eventStartAtUtcDatetimestamp < currentUtcDatetimestamp && eventEndAtUtcDatetimestamp > currentUtcDatetimestamp) {
                    if (this.currentCommunityEvent === null) {
                        this.currentCommunityEvent = event;
                    }
                }
            }
        }

        setTimeout(() => {
            // show hide more | less link
            if (document.getElementById('category_filter_list') !== null && document.getElementById('category_filter_list').scrollWidth > document.getElementById('category_filter_list').clientWidth && document.getElementById('category_filter_list').scrollWidth >= document.getElementById('category_filter_list_wrapper').clientWidth - 60) {
                this.showMoreLessLink = true;
            }
        }, 300);
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
         * Returns community privacy
         */
        privacy() {
            return this.community.privacy;
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
         * Returns status of member is creator or not
         */
        isCreator() {
            let isCreator = false;
            if (this.memberExist && this.community.user_id === this.user.id) {
                isCreator = true;
            }

            return isCreator;
        },

        /**
         * Returns add post show status
         */
        addPostShow() {
            return this.$store.state.post.addPostShow;
        },

        /**
         * Return category options
         */
        categories() {
            let categories = this.community.categories;
            if (!this.isManager(this.role)) {
                categories = categories.filter(el => el.admin_only !== 1);
            }

            return categories;
        },

        /**
         * Return selected category
         */
        selectedCategoryId() {
            return this.$store.state.post.selectedCategoryId;
        },

        /**
         * Returns scheduled posts
         */
        scheduledPosts() {
            return this.$store.state.post.scheduledItems || [];
        },

        totalPost() {
            return this.$store.state.post.pagination?.total;
        },

        /**
         * Returns community events
         */
        communityEvents() {
            return this.$store.state.communitycenter.communityEvents;
        },

        /**
         * Returns requested pending members
         */
        pendingMembers() {
            return this.$store.state.community.data.number_of_pending_members || 0;
        },

        isNotAvailableCommunity() {
            if (typeof this.community.name === 'undefined'
                || this.community.status === CommunityStatus.INACTIVE
                || this.community.status === CommunityStatus.SUSPENDED) {
                return true
            }

            return false
        },

        loading() {
            return this.$store.state.post.loading;
        },
    },
    methods: {
        /**
         * Open new post section
         */
        showAddPost() {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                this.$store.commit('resetCommunityPost');
                this.$store.commit('setAddPostShow', true);
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

        getPostCategory(post) {
            let categoryTitle = this.$t('community.community.category-options.all');
            let categoryId = post.category_id;

            let categories = this.community.categories;
            for (let i = 0; i < categories.length; i++) {
                const categoryItem = categories[i];
                if (categoryId === categoryItem.id) {
                    categoryTitle = categoryItem.title;
                    break;
                }
            }

            return categoryTitle;
        },

        postBodyCss(post) {
            let css = '';
            if (post.firstMedia) {
                if (window.innerWidth <= 600) {
                    css = 'width: calc(100% - 90px);';
                } else {
                    css = 'width: calc(100% - 150px);';
                }
            } else {
                css = 'width: 100%;'
            }

            return css;
        },

        /**
         * Select category filter
         */
        selectCategoryFilter(category) {
            this.$store.commit('setCategoryFilter', category);
        },

        /**
         * Expand category filters by clicking more link
         */
        expandCategoryFilters() {
            this.moreLinkClass = 'hidden';
            this.lessLinkClass = '';
            this.categoryFiltersClass = 'flex-wrap';
        },

        /**
         * Shrink category filters by clicking less link
         */
        shrinkCategoryFilters() {
            this.moreLinkClass = '';
            this.lessLinkClass = 'hidden';
            this.categoryFiltersClass = '';
        },

        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow(start_at) {
            return moment(start_at).locale(this.$i18n.locale).fromNow(true);
        },

        /**
         * Go to upcoming event
         */
        goToUpEvent(upcomingCommunityEvent) {
            let tab = 'calendar';
            if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
                tab = 'about';
            }

            let path = '/' + this.community.url + '/' + tab;

            this.$router.push(path).catch(() => { });
            this.$store.commit('setCommunityTab', tab);

            this.$store.commit('setCommunityEventById', upcomingCommunityEvent.id);
            this.$store.commit('showModal', {
                type: 'ViewCommunityEvent',
                transparent: true
            });
        },

        /**
         * Select filter option
         */
        async selectFilterOption(columnKey) {
            this.filterOptions = this.filterOptions.map(column => {
                column.filter = 0;
                if (column.key === columnKey) {
                    column.filter = 1;
                }

                return { ...column };
            });

            this.$store.commit('setPostFilter', columnKey);
        },

        /**
         * Select sort option
         */
        async selectSortOption(columnKey) {
            this.sortOptions = this.sortOptions.map(column => {
                column.sort = 0;
                if (column.key === columnKey) {
                    column.sort = 1;
                }

                return { ...column };
            });

            this.$store.commit('setPostSort', columnKey);
        },

        async goToMembers(key) {
            let tab = 'member';
            if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
                tab = 'about';
            }

            let path = '/' + this.community.url + '/' + tab;

            this.$router.push(path).catch(() => { });
            this.$store.commit('setCommunityTab', tab);

            this.$store.commit('setMemberFilter', key);

            await this.$store.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');
        },

        showPostFilterDropdown() {
            this.displayPostFilterDropdown = !this.displayPostFilterDropdown;
        },

        hidePostFilterDropdown() {
            this.displayPostFilterDropdown = false;
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
    }
}
</script>

<style scoped>
.add-new-community {
    display: flex;
    align-items: center;
    cursor: pointer;
    padding: 10px 20px;
}

.add-new-community-text {
    margin-left: 10px;
    font-size: 18px;
    color: #8a8a8a;
}

.add-new-community:hover .add-new-community-text {
    color: #4a4a4a;
}

.current-event-link-title {
    cursor: pointer;
}

.current-event-link-title:hover {
    text-decoration: underline;
}

.category-filters {
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    margin-top: 15px;
    width: calc(100% - 60px);
}

.other-post-filters {
    width: 60px;
    height: 40px;
    margin-top: 15px;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    justify-content: end;
}

.other-post-filters-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(228, 228, 228);
    border-radius: 50%;
    background-color: #fff;
    cursor: pointer;
}

.other-post-filters-link .fa-filter {
    width: 16px;
    height: 13px;
    color: rgb(144, 144, 144);
}

.other-post-filters-link:hover .fa-filter {
    color: rgb(32, 33, 36);
}

.category-filter,
.category-less-link {
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

.category-filter:hover,
.category-less-link:hover,
.category-more-link:hover,
.other-post-filters-link:hover {
    background-color: rgb(228, 228, 228);
    color: rgb(32, 33, 36);
}

.category-filter.selected {
    color: #FFFFFF;
    border: 1px solid #909090;
    background-color: #909090;
}

.category-more-link-container {
    position: absolute;
    right: 0px;
    height: 40px;
    margin-bottom: 5px;
    padding-left: 32px;
    background: linear-gradient(90deg, rgba(0, 0, 0, 0) 0%, rgb(244, 245, 248) 15%);
    display: flex;
    align-items: center;
}

.category-more-link {
    cursor: pointer;
    border: 1px solid rgb(228, 228, 228);
    border-radius: 25px;
    padding: 7px 15px;
    background-color: #fff;
    white-space: nowrap;
    color: rgb(144, 144, 144);
}

.upcoming-event {
    text-align: center;
    padding: 10px;
}

.upcoming-event i {
    font-weight: 600;
    font-size: 18px;
}

.upcoming-event-link-title {
    font-weight: 500;
    font-size: 16px;
    cursor: pointer;
    margin-right: 5px;
}

.upcoming-event-link-title:hover {
    text-decoration: underline;
}

.upcoming-event .upcoming-event-link-description {
    font-size: 16px;
}

.current-event {
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.live-info {
    display: inline-flex;
    align-items: center;
    padding: 5px 10px;
    margin-right: 10px;
    background-color: #E74C3C;
    color: #fff;
    font-size: 12px;
    border-radius: 5px;
    box-shadow: rgba(60, 64, 67, 0.32) 0px 1px 2px, rgba(60, 64, 67, 0.15) 0px 2px 6px, rgba(0, 0, 0, 0.1) 0px 1px 8px;
    cursor: pointer;
}

.live-info i {
    font-size: 8px;
    animation: blinker 1s linear infinite;
}

@keyframes blinker {
    50% {
        opacity: 0;
    }
}

.upcoming-event-info {
    font-weight: 500;
    font-size: 16px;
    cursor: pointer;
}

.other-filter-item {
    display: flex;
    align-items: center;
    padding: 4px 5px 4px 15px;
    cursor: pointer;
    text-align: left;
    white-space: nowrap;
}

#other_post_filter_content {
    right: 0px;
    top: 45px;
}

.membership-pending-request {
    padding: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.membership-pending-request-desc {
    color: #7957d5;
    font-size: 14px;
}

.membership-pending-request-desc:hover {
    color: #9198FF;
    text-decoration: underline;
}

.membership-pending-request-count {
    padding: 1px 8px;
    border-radius: 50%;
    background-color: #ff0000;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 5px;
    color: #fff;
    font-size: 13px;
}

.post-list-section {
    min-height: 400px;
}

@media only screen and (max-width: 600px) {
    .add-new-community {
        padding: 5px 10px;
    }

    .add-new-community-text {
        margin-left: 8px;
        font-size: 14px;
    }

    .community-schedule-actions {
        right: 10px;
        top: 40px;
    }

    .category-filters {
        margin-top: 10px;
    }

    .other-post-filters {
        width: 40px;
        height: 30px;
        margin-top: 7px;
    }

    .other-post-filters-link {
        width: 30px;
        height: 30px;
    }

    .other-post-filters-link .fa-filter {
        width: 14px;
        height: 12px;
    }

    .category-filter,
    .category-less-link {
        margin-right: 7px;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 14px;
    }

    .category-more-link-container {
        height: 36px;
        padding-left: 20px;
    }

    .category-more-link {
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 14px;
    }

    .upcoming-event i {
        font-size: 16px;
    }

    .upcoming-event-link-title {
        font-size: 14px;
    }

    .upcoming-event .upcoming-event-link-description {
        font-size: 14px;
    }

    .upcoming-event-info {
        font-size: 14px;
    }

    .post-list-section {
        min-height: 200px;
    }
}
</style>
