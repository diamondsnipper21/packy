<template>
    <div class="container pl-0 pr-0">

        <loading v-if="loading" :active.sync="loading" :is-full-page="true" />

        <div v-else>
            <div v-if="typeof community.name === 'undefined' || ![CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status)"
                class="empty-section">
                {{ $t('community.community.empty-community-placeholder') }}
            </div>
            <div v-else class="card p1">
                <!-- Filters -->
                <div class="flex align-items-center mb1">
    
                    <!-- Filter by input string -->
                    <div class="field flex-05">
                        <div class="member-search">
                            <font-awesome-icon :icon="['fa', 'magnifying-glass']" class="member-search-icon" />
                            <input class="input member-search-filter" type="text" :placeholder="$t('common.search-user')"
                                v-model="paginatedUserSearch" @input="searchUserFilteredInput" />
                        </div>
                    </div>
                </div>
                <!-- end filters -->
    
                <div class="table-container">
                    <div v-if="processing" class="user-loading"></div>
                    <div v-else class="mt-05">
                        <div v-if="users.length > 0" ref="userRef">
                            <table class="table is-hoverable is-fullwidth pointer">
                                <thead>
                                    <tr style="cursor: default;">
                                        <th>{{ $t('admin-users.avatar') }}</th>
                                        <th>{{ $t('admin-users.contact-info') }}</th>
                                        <th>{{ $t('admin-users.tag') }}</th>
                                        <th>{{ $t('admin-users.access.ADMIN') }}</th>
                                        <th>{{ $t('admin-users.created-at') }}</th>
                                        <th>{{ $t('admin-users.last-activity') }}</th>
                                        <th>{{ $t('admin-users.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users">
                                        <td class="no-border" @click="showDetails(user.member_id)">
                                            <div class="image is-48x48 mr-05 flex align-items-center">
                                                <img class="is-rounded" :src="getUserGravatar(user)"
                                                    alt="gravatar-profile-image">
                                            </div>
                                        </td>
                                        <td class="no-border" @click="showDetails(user.member_id)">
                                            <div class="user-name">
                                                {{ user.name }}
                                            </div>
                                            <div class="user-email">
                                                {{ user.email }}
                                            </div>
                                        </td>
                                        <td class="no-border" @click="showDetails(user.member_id)">
                                            <div class="user-tag">
                                                {{ getUserTag(user) }}
                                            </div>
                                        </td>
                                        <td class="no-border" @click="showDetails(user.member_id)">
                                            <div class="community-name-container" v-html="getAdminInfo(user.communityIds)">
                                            </div>
                                        </td>
                                        <td class="no-border" @click="showDetails(user.member_id)">
                                            <div class="user-created-at">
                                                {{ getDate(user.created_at) }}
                                            </div>
                                        </td>
                                        <td class="no-border" @click="showDetails(user.member_id)">
                                            <div class="user-last-activity">
                                                <div v-if="user.online">
                                                    <font-awesome-icon icon="fa fa-circle" class="mr1 online-now" />
                                                    {{ $t('community.members.online-now') }}
                                                </div>
                                                <div v-else>
                                                    {{ $t('community.members.active') }} {{
                                                        getDiffTimeFromNow(user.last_activity) }}
                                                </div>
                                            </div>
                                        </td>
    
                                        <td class="no-border purp">
                                            <div class="login-user-link" @click="loginAsUser(user.id)">
                                                <font-awesome-icon :icon="['fa', 'right-to-bracket']" class="font-14px" />
                                                &nbsp;
                                                <div class="font-weight-600">
                                                    {{ $t('login.btn') }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
    
                            <Pagination
                                :total="total"
                                :perPage="perPage"
                                :current="currentPage"
                                pageAction="GET_PAGINATED_USERS"
                                class="mb1" />
                        </div>
    
                        <div v-else class="flex align-items-center jc mt1">
                            <div class="flex align-items-center jc flex-column mt4">
                                <div>{{ noUserResults }}</div>
                                <p class="purp pointer" @click="resetFilters">
                                    {{ $t('admin-users.reset-filter-link') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import md5 from 'md5'
import moment from 'moment'
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import Pagination from "../components/General/Elements/Pagination.vue";
import { CommunityStatus } from '../data/enums';
import getUserTag from "../mixins/util";

export default {
    name: 'UserManagement',
    components: {
        Loading,
        Pagination
    },
    mixins: [
        getUserTag
    ],
    data() {
        return {
            CommunityStatus,
            loading: true,
            processing: false,
            noUserResults: ''
        };
    },
    async created() {
        this.loading = true;

        moment.locale(this.$i18n.locale);

        if (this.auth && parseInt(this.user.id) === 1) {
            await this.$store.dispatch('GET_PAGINATED_USERS');
        } else {
            window.location.href = '/' + this.community.url + '/about';
        }

        this.loading = false;
    },
    watch: {
        'currentPage': function (v) {
            if (this.$refs.userRef) {
                window.scroll({
                    top: this.$refs.userRef.offsetTop,
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
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns user
         */
        user() {
            return this.$store.state.auth.user;
        },

        /**
         * Returns online members
         */
        currentMember() {
            return this.$store.state.member.data;
        },

        /**
         * Returns access of member
         */
        access() {
            return this.currentMember ? this.currentMember.access : null;
        },

        /**
         * Get | Set user search
         */
        paginatedUserSearch: {
            get() {
                return this.$store.state.communitycenter.paginatedUserSearch;
            },
            set(v) {
                this.$store.commit('setPaginatedUserSearch', v);
            }
        },

        /**
         * Returns community events
         */
        paginatedUsers() {
            return this.$store.state.communitycenter.paginatedUsers;
        },

        /**
         * Returns community posts
         */
        users() {
            return (this.paginatedUsers.hasOwnProperty('data')) ? this.paginatedUsers.data : [];
        },

        /**
         * Returns communities
         */
        communities() {
            return this.$store.state.communitycenter.communities;
        },

        total() {
            return this.$store.state.communitycenter.paginatedUsers?.total || 0;
        },

        currentPage() {
            return this.$store.state.communitycenter.paginatedUsers?.current_page || 0;
        },

        perPage() {
            return this.$store.state.communitycenter.paginatedUsers?.per_page || 1;
        },
    },
    methods: {
        /**
         * Capitalise a string
         */
        capitalize(str) {
            return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
        },

        /**
         * Get member joined info
         */
        getDate(date) {
            return moment(date).format("MMM Do, YYYY");
        },

        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow(date) {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        /**
         * Search users by name, tag, email
         *
         * Events are grouped and fired once after 500ms
         * this.timeout is created dynamically
         */
        async searchUserFilteredInput() {
            this.processing = (this.paginatedUserSearch.length > 0);

            if (this.timeout) clearTimeout(this.timeout);

            this.timeout = setTimeout(async () => {
                await this.$store.dispatch('GET_PAGINATED_USERS');

                if (this.users.length === 0) {
                    this.noUserResults = this.$t('common.no-result-for').replace('#searchFilter#', this.paginatedUserSearch);
                }

                this.processing = false;
            }, 500);

        },

        showDetails(id) {
            // this.$router.push({ path: '/admin/users/'+ id });
        },

        /**
         * When users.length == 0, the user can reset the filters
         */
        async resetFilters() {
            this.$store.commit('setPaginatedUserSearch', '');

            await this.$store.dispatch('GET_PAGINATED_USERS');
        },

        /**
         * Returns user name
         */
        getUserName(user) {
            let name = '';

            if (typeof user.firstname !== 'undefined') {
                name = user.firstname;

                if (typeof user.lastname !== 'undefined') {
                    name += ' ' + user.lastname;
                }
            }

            if (name === '' && user.email) {
                name = user.email;
            }

            return name;
        },

        /**
         * Return the user Gravatar, if it exists
         */
        getUserGravatar(user) {
            let gravatar = '';

            if (typeof user !== 'undefined' && user !== null) {
                if (typeof user.photo !== 'undefined' && user.photo !== null) {
                    gravatar = user.photo;
                }

                if (gravatar === '' && user.email) {
                    let email = user.email.toLowerCase();
                    if (email) {
                        gravatar = 'https://www.gravatar.com/avatar/' + md5(email) + '?s=48&d=identicon';
                    }
                }
            }

            if (gravatar === '') {
                gravatar = '/assets/img/default.png';
            }

            return gravatar;
        },

        /**
         * Get user access text
         */
        getAdminInfo(value) {
            let userAccess = '';
            let communityIds = [];
            let communityNames = [];

            if (value) {
                communityIds = value.split(",");
                for (let i = 0; i < this.communities.length; i++) {
                    let communityId = this.communities[i].id.toString();
                    if (communityIds.includes(communityId)) {
                        communityNames.push(this.communities[i].name);
                    }
                }
            }

            if (communityNames.length > 0) {
                for (let j = 0; j < communityNames.length; j++) {
                    userAccess += '<div class="admin-community-name">' + communityNames[j] + '</div>';
                }
            } else {
                userAccess = '-';
            }

            return userAccess;
        },

        /**
         * Log in as another user
         */
        loginAsUser(userId) {
            this.$store.dispatch('ADMIN_LOG_IN_AS_USER', userId);
        },
    }
}
</script>

<style scoped>
table thead tr th {
    text-align: center !important;
    vertical-align: middle;
}

table tbody tr td {
    font-size: 14px;
    font-weight: 400;
    text-align: center;
    vertical-align: middle;
}

.user-name {
    font-size: 15px;
    font-weight: 600;
    text-align: left;
}

.user-email {
    text-align: left;
}

.login-user-link {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 3px;
    border: 1px solid #ddd;
    padding: 5px 10px;
    white-space: nowrap;
}

.login-user-link:hover {
    background-color: #eee;
}

.user-tag {
    font-size: 13px;
    font-weight: bold;
    color: rgb(144, 144, 144);
}

.online-now {
    color: rgb(0, 158, 93);
}

.table-container {
    position: relative;
    min-height: 350px;
}

.user-loading {
    animation: spinAround 500ms infinite linear;
    border: 2px solid #a39c9c;
    border-radius: 290486px;
    border-right-color: transparent;
    border-top-color: transparent;
    content: "";
    display: block;
    height: 1.5em;
    width: 1.5em;
    position: absolute;
    left: 47%;
    top: 41%;
    transform: translate(-50%, -50%);
}

@media only screen and (max-width: 600px) {
    .user-tag {
        font-size: 12px;
    }
}
</style>
