<template>
    <div class="box p0">
        <img v-if="summary_photo" :src="summary_photo" class="summary-photo-img" />
        <div v-else class="upload-cover-photo-section">
            <div v-if="isManager(role)" class="upload-cover-photo-text" @click="showSettingsModal('general')">
                {{ $t('common.upload-cover-photo') }}
            </div>
        </div>

        <div class="community-content">
            <div v-if="community.name" class="pb-05">
                <div class="community-name ml-0">
                    {{ community.name }}
                </div>
                <div class="community-privacy">
                    <font-awesome-icon v-if="community.privacy === 'public'" icon="fa fa-lock-open" />
                    <font-awesome-icon v-else icon="fa fa-lock" />
                    <span class="ml-05">
                        {{ privacyWord(community.privacy) }}
                    </span>
                </div>
                <div class="community-summary-description">
                    {{ community.summary_description }}
                </div>

                <div v-if="auth || privacy === 'public'" class="community-external-links">
                    <a v-for="link in links" :key="link.name" :href="link.url" class="community-external-link"
                        target="_blank">
                        <font-awesome-icon icon="fa fa-link" class="community-external-link-icon" />
                        <span class="ml-05 community-external-link-title">
                            {{ getLinkName(link.name) }}
                        </span>
                    </a>
                </div>
            </div>

            <div class="community-summary-info">
                <div class="community-summary-info-item" :class="(auth || privacy === 'public') ? '' : 'w100'"
                    @click="goToMembers('all')">
                    <div class="community-summary-info-item-title">
                        {{ totalMembersCount }}
                    </div>
                    <div class="community-summary-info-item-label">
                        {{ $tc('community.community.summary.members', totalMembersCount) }}
                    </div>
                </div>
                <div v-if="auth || privacy === 'public'" class="border-left-side community-summary-info-item"
                    @click="goToMembers('online')">
                    <div class="community-summary-info-item-title">
                        {{ onlineMembers.length }}
                    </div>
                    <div class="community-summary-info-item-label">
                        {{ $t('community.community.summary.online') }}
                    </div>
                </div>
            </div>

            <div v-if="auth || privacy === 'public'" class="community-members-info">
                <img v-for="member in onlineShowedMembers" :key="member.id" :src="getMemberGravatar(member)"
                    class="member-item-photo" />
            </div>

            <div class="w100 mt1">
                <button v-if="access === MemberAccess.ALLOWED"
                    class="button is-medium w100 community-btn text-uppercase" @click="showSettingsModal('membership')">
                    {{ $t('community.community.summary.settings') }}
                </button>
                <div v-else-if="access === MemberAccess.PENDING" class="w100">
                    <button class="button is-medium w100 community-btn text-uppercase"
                        @click="pendingMembershipRequest">
                        {{ $t('community.community.summary.membership-pending') }}
                    </button>
                    <div class="cancel-membership" @click="cancelMembershipRequest">
                        {{ $t('community.community.summary.cancel-membership') }}
                    </div>
                </div>
                <button v-else-if="access !== MemberAccess.BANNED" class="button is-medium w100 community-blue-btn text-uppercase" :class="button"
                    @click="joinMembershipRequest">
                    {{ $t('community.community.summary.join-group') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import getMemberGravatar from "../mixins/util";
import isManager from "../mixins/util";
import { MemberAccess } from '../data/enums';

export default {
    name: 'CommunitySummarySidebarTop',
    mixins: [
        getMemberGravatar,
        isManager
    ],
    data() {
        return {
            MemberAccess,
            processing: false,
            self: null
        };
    },
    created ()
    {
        this.self = this;
    },
    mounted() {
        this.$store.dispatch('GET_MEMBER_SETTINGS', {
            id: this.community.id,
            memberId: this.currentMember.id
        });

        this.reInitSettings();
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
         * Returns community
         */
        summary_photo() {
            return this.community.summary_photo;
        },

        /**
         * Returns community url
         */
        communityUrl() {
            return this.community?.url || '';
        },

        /**
         * Returns community privacy
         */
        privacy() {
            return this.community.privacy;
        },

        /**
         * Returns community links
         */
        links() {
            return this.community.links;
        },

        /**
         * Returns online members
         */
        currentMember() {
            return this.$store.state.member.data;
        },

        /**
         * Returns allowed members
         */
        allowedMembers() {
            return this.$store.state.communitycenter.allowedMembers;
        },

        /**
         * Returns access of member
         */
        access() {
            return this.memberExist ? this.currentMember.access : null;
        },

        /**
         * Returns role of member
         */
        role() {
            return this.memberExist ? this.currentMember.role : null;
        },

        /**
         * Returns total members count
         */
        totalMembersCount() {
            // return this.allowedMembers.length;
            return this.$store.state.communitycenter.membersCount;
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
         * Add spinner when processing
         * Returns online members
         */
        onlineShowedMembers() {
            return this.onlineMembers.length > 0 ? this.onlineMembers.slice(0, 9) : [];
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
            return (typeof this.currentMember.id !== 'undefined' && parseInt(this.currentMember.id) > 0) ? true : false;
        },
    },
    methods: {
        /**
         * Return privacy
         */
        privacyWord(privacy) {
            return this.$te(`community.community.${privacy}-group`) ? this.$t(`community.community.${privacy}-group`) : '';
        },

        reInitSettings() {
            this.$store.commit('cloneCommunity', JSON.parse(JSON.stringify(this.$store.state.community.data)));
            this.$store.commit('resetDropzoneError');
        },

        showSettingsModal(extraData) {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                if (extraData !== 'general') {
                  this.reInitSettings();
                }

                this.$store.commit('showModal', {
                  type: 'CommunitySetting',
                  extraData,
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

        getLinkName(linkName) {
            return this.$te(`community.community.links.${linkName}`) ? this.$t(`community.community.links.${linkName}`) : linkName;
        },

        async joinMembershipRequest() {
            if (this.communityUrl.toUpperCase() === 'INCUBATEUR') {
                let tab = 'start';
                let path = '/' + this.communityUrl + '/' + tab;
                this.$router.push(path).catch(() => { });
                this.$store.commit('setCommunityTab', tab);
            } else {
                if (this.auth) {
                    this.processing = true;
                    await new Promise((resolve) => setTimeout(() => { resolve(); }, 2000));
                    await this.$store.dispatch('JOIN_TO_COMMUNITY');
                    this.processing = false;
                } else {
                    this.showSignup();
                }
            }
        },

        pendingMembershipRequest() {
        },

        async cancelMembershipRequest() {
            if (this.auth) {
                await this.$store.dispatch('LEAVE_FROM_COMMUNITY');
                this.$store.commit('hideModal');
            } else {
                this.showLogin();
            }
        },

        async goToMembers(key) {
            let tab = 'member';
            if (this.privacy === 'private' && this.access !== MemberAccess.ALLOWED) {
                tab = 'about';
            }

            let path = '/' + this.communityUrl + '/' + tab;

            this.$router.push(path).catch(() => { });
            this.$store.commit('setCommunityTab', tab);

            this.$store.commit('setMemberFilter', key);

            await this.$store.dispatch('GET_PAGINATED_COMMUNITY_MEMBERS');
        },

        removeSummaryPhoto ()
        {
            this.$store.commit('setCommunityProperty', {
                key: 'summary_photo',
                v: null
            });
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
                transparent: true,
                extraAction: 'JOIN_TO_COMMUNITY'
            });
        },
    }
}
</script>

<style scoped>

.community-content {
    padding: 1.25rem;
}

.community-privacy {
    color: #909090;
    font-size: 13px;
    padding: 5px 0px;
}

.community-summary-description {
    white-space: pre-wrap;
    padding: 5px 0px;
    display: -webkit-box;
    -webkit-line-clamp: 7;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.community-external-links {
    padding: 15px 0px;
}

.community-external-link {
    color: #909090;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block;
}

.community-external-link:hover .community-external-link-title {
    text-decoration: underline;
}

.community-summary-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px;
    border-top: 1px solid #E4E4E4;
    border-bottom: 1px solid #E4E4E4;
}

.community-summary-info-item {
    padding: 5px;
    width: 50%;
    text-align: center;
    cursor: pointer;
}

.border-left-side {
    border-left: 1px solid #E4E4E4;
}

.community-external-link-icon {
    font-size: 14px;
}

.community-summary-info-item-title {
    font-size: 15px;
    font-weight: bold;
}

.community-members-info {
    padding: 10px 0px;
    white-space: nowrap;
    overflow: hidden;
    display: flex;
    align-items: center;
}

.member-item-photo {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 5px;
}

.community-summary-info-item-label {
    overflow: hidden;
    text-overflow: ellipsis;
    color: rgb(144, 144, 144);
    font-size: 13px;
}

.cancel-membership {
    text-align: center;
    margin-top: 15px;
    color: rgb(144, 144, 144);
    cursor: pointer;
    margin-bottom: 15px;
}

.cancel-membership:hover {
    color: rgb(32, 33, 36);
    text-decoration: underline;
}

.summary-photo-img {
    display: block;
    width: 100%;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
}

.upload-cover-photo-section {
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #ddd;
    min-height: 150px;
}

.upload-cover-photo-section .upload-cover-photo-text {
    color: #707070;
}

.upload-cover-photo-section:hover .upload-cover-photo-text {
    text-decoration: underline;
    cursor: pointer;
    color: #4a4a4a;
}

@media only screen and (max-width: 600px) {
    .community-content {
        padding: 1rem;
    }

    .community-privacy {
        font-size: 12px;
    }

    .community-summary-description {
        font-size: 14px;
    }

    .community-external-links {
        padding: 10px 0px;
    }

    .community-external-link-title {
        font-size: 14px;
    }

    .community-external-link-icon {
        font-size: 13px;
    }

    .community-summary-info-item-title {
        font-size: 14px;
    }

    .community-members-info {
        padding: 5px 0px;
    }

    .community-summary-info-item-label {
        font-size: 12px;
    }

    .cancel-membership {
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .upload-cover-photo-section {
        min-height: 100px;
    }
}
</style>
