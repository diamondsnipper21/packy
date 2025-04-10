<template>
    <div class="box p0 community-post-item" @click="selectPost(post, postType == PostType.REGULAR)">
        <div class="community-schedule-actions" v-if="postType == PostType.SCHEDULE">
            <div class="flex">
                <button class="button is-medium community-btn mr-05 desktop-only-show"
                    @click="showRescheduleModal(post)">
                    {{ $t('community.community.post.reschedule') }}
                </button>

                <button class="control button is-medium grey-bg mr-05 mobile-only-show"
                    @click="showRescheduleModal(post)">
                    <font-awesome-icon icon="fa fa-pencil" class="link-icon" />
                </button>

                <button class="button is-medium community-btn desktop-only-show" @click="showRemovePostModal(post)">
                    {{ $t('common.remove') }}
                </button>

                <button class="control button is-medium grey-bg mobile-only-show" @click="showRemovePostModal(post)">
                    <font-awesome-icon icon="fa fa-trash" class="link-icon" />
                </button>
            </div>
        </div>
        <div v-if="parseInt(post.pinned) > 0 && postType == PostType.REGULAR" class="post-pinned">
            <div class="flex align-items-center">
                <font-awesome-icon icon="fa fa-thumbtack" /> &nbsp;
                <div class="font-weight-500">
                    {{ $t('community.community.post.pinned') }}
                </div>
            </div>
        </div>

        <div @click="selectPost(post, postType == PostType.SCHEDULE)">
            <div class="post-scheduled" v-if="postType == PostType.SCHEDULE">
                <div class="flex align-items-center">
                    <font-awesome-icon icon="fa fa-clock" /> &nbsp;
                    <div class="font-weight-500">
                        {{
                            post.repeat_every ?
                                $t('community.community.post.recurring')
                                : $t('community.community.post.schedule')
                        }}
                    </div>
                </div>
            </div>

            <div class="post-item-header">
                <div class="flex align-items-center">
                    <img :src="getMemberGravatar(post.member)" class="community-customer-gravatar" />
                    <div class="ml-05">
                        <div class="flex align-items-center">
                            <div class="customer-name">
                                {{ postOwnerName }}
                            </div>
                            <div v-if="isManager(postOwnerRole)" class="customer-role ml-05">
                                {{ $t(`community.members.role-options.${postOwnerRole}`) }}
                            </div>
                            <template v-for="group, i in postOwnerGroups" :key="group.id">
                                <div class="customer-group ml-05">
                                    {{ group.name }}
                                </div>
                            </template>
                        </div>
                            
                        <div class="post-created" v-html="getPostCreatedInfo(post)"></div>
                    </div>
                </div>

                <div v-if="postType === PostType.REGULAR && isManager(role)" class="flex align-items-center">
                    <button v-if="post.visibility === 0" class="button is-medium community-btn text-uppercase" @click.stop="approve(post)">
                        {{ $t('community.members.approve') }}
                    </button>
                    <button v-if="post.visibility === 0" class="button is-medium community-btn text-uppercase ml-05" @click.stop="decline(post)">
                        {{ $t('community.members.decline') }}
                    </button>
                    <span v-if="parentId > 0" class="close-post">
                        <font-awesome-icon icon="fa fa-times" class="pointer" @click.stop="closePost" />
                    </span>
                </div>
            </div>

            <div class="post-card-content">
                <div class="post-body" :style="postBodyCss(post)">
                    <div class="post-title" v-html="post.title">
                    </div>
                    <p class="post-content" v-html="linkify(updateViewContentForMention(post.content))"></p>
                </div>

                <media-thumb v-if="post.firstMedia" :media="post.firstMedia"
                    additionalClass="community-main-media-container" :viewMedia="false" :hoverMedia="false" />
            </div>
        </div>

        <div v-if="postType === PostType.REGULAR && post.pollVotedMemberCount > 0" class="post-poll-vote-section">
            <div class="poll-label-section">
                {{ $t('community.poll.poll') }}
            </div>
            <div class="poll-desc-section">
                {{ $tc('community.poll.voted-member', post.pollVotedMemberCount, {count: post.pollVotedMemberCount}) }}
            </div>
        </div>

        <div class="post-footer" v-if="postType === PostType.REGULAR">
            <font-awesome-icon icon="fa fa-thumbs-up" class="mr-05 mt-min-3px like-icon"
                :class="getCommunityPostLikeStatus(post)" @click.stop="togglePostLike(post)" />
            <span class="font-weight-500 mr1">
                {{ post.number_of_likes }}
            </span>
            <font-awesome-icon icon="fa fa-comment" :class="'mr-05'" />
            <span class="font-weight-500">
                {{ post.commentsCount }}
            </span>
        </div>
    </div>
</template>

<script>
import moment from 'moment-timezone';
import { MemberAccess, PostType } from '../../../data/enums';
import getMemberName from "../../../mixins/util";
import getMemberGravatar from "../../../mixins/util";
import linkify from "../../../mixins/util";
import isManager from "../../../mixins/util";
import updateViewContentForMention from "../../../mixins/util";

import MediaThumb from "../../Media/MediaThumb";

export default {
    name: 'PostElement',
    components: {
        MediaThumb,
    },
    mixins: [
        getMemberName,
        getMemberGravatar,
        linkify,
        isManager,
        updateViewContentForMention
    ],
    created() {
        moment.locale(this.$i18n.locale);

        setTimeout(() => {
            if (this.parentId > 0) {
                this.parentPage = this.$route.path;
            }
        }, 100);
    },
    props: {
        post: {
            type: Object,
        },
        postType: {
            type: Number,
        },
        parentId: {
            type: Number,
            default: 0
        }
    },
    data() {
        return {
            MemberAccess,
            PostType,
            parentPage: '',
        };
    },
    computed: {
        auth() {
            return this.$store.state.auth.loggedIn;
        },
        memberExist() {
            return this.$store.state.member.data?.id || false;
        },
        member() {
            return this.$store.state.member.data;
        },
        access() {
            return this.member?.access;
        },
        role() {
            return this.member?.role;
        },
        postOwnerName() {
            return this.getMemberName(this.post.member);
        },
        postOwnerRole() {
            return this.post.member?.role || '';
        },
        postOwnerGroups() {
            let postOwnerGroups = this.post.member?.groups || [];
            postOwnerGroups = postOwnerGroups.filter(el => el.publish > 0);

            return postOwnerGroups;
        },
    },
    methods: {
        async approve(post) {
            if (this.auth) {
                if (this.isManager(this.role)) {
                    await this.$store.dispatch('APPROVE_POST_REQUEST', post.id);
                }
            } else {
                this.showLogin();
            }
        },

        async decline(post) {
            if (this.auth) {
                if (this.isManager(this.role)) {
                    await this.$store.dispatch('DECLINE_POST_REQUEST', post.id);
                }
            } else {
                this.showLogin();
            }
        },

        async closePost() {
            if (this.auth) {
                if (this.isManager(this.role) && this.parentId > 0) {
                    await this.$store.dispatch('CLOSE_POST_FROM_PARENT', {
                        parentId: this.parentId,
                        postId: this.post.id
                    });
                }
            } else {
                this.showLogin();
            }
        },

        showLogin() {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },

        showRescheduleModal(scheduledPost) {
            let post = JSON.parse(JSON.stringify(scheduledPost));
            post.scheduled = true;

            this.$store.commit('setCommunityPost', post);
            this.$store.commit('showModal', {
                type: 'SchedulePost',
                transparent: true
            });
        },

        showRemovePostModal(scheduledPost) {
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.community.post.confirm-delete.title'),
                    desc: this.$t('community.community.post.confirm-delete.desc'),
                    action: 'DELETE_SCHEDULED_POST',
                    param: { id: scheduledPost.id, communityId: scheduledPost.community_id },
                    hideModal: true
                }
            });
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

        stripSpanTag(content) {
            return content.replace(/<\/?span[^>]*>/g, "");
        },

        selectPost(post, selected) {
            if (!selected) return

            this.$store.commit('setParentPage', this.parentPage);

            if (this.postType == PostType.REGULAR) {
                this.$store.dispatch('GET_COMMUNITY_POST', {
                    path: post.path,
                    communityId: post.community_id,
                    redirect: this.parentId > 0 ? false : true
                })
            } else {
                this.$store.dispatch('GET_SCHEDULED_POST', {
                    communityId: this.post.community_id,
                    id: this.post.id
                })
            }
        },

        async togglePostLike(communityPost) {
            if (communityPost.visibility === 1) {
                if (this.auth) {
                  if (this.access === MemberAccess.ALLOWED) {
                    this.$store.commit('setCommunityPost', communityPost);
                    await this.$store.dispatch('LIKE_COMMUNITY_POST', {
                      id: communityPost.id
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
                    this.showSignup();
                }
            }
        },

        showSignup() {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Signup',
                transparent: true
            });
        },

        getCommunityPostLikeStatus(post) {
            let likeStatusClass = '';
            if (this.memberExist && post.member_id === this.member.id) {
                likeStatusClass = 'disabled';
            } else {
                likeStatusClass = post.is_member_like ? 'liked' : '';
            }

            return likeStatusClass;
        },

        getPostCreatedInfo(post) {
            let getPostCreatedInfo = '';
            if (this.postType == PostType.REGULAR) {
                let now = moment();
                let givenDate = moment(post.created_at);
                let count = 1;
                let unit = 'seconds';

                const seconds = now.diff(givenDate, 'seconds');
                if (seconds) {
                    count = seconds;
                    unit = 'seconds';
                }

                const minutes = now.diff(givenDate, 'minutes');
                if (minutes) {
                    count = minutes;
                    unit = 'minutes';
                }

                const hours = now.diff(givenDate, 'hours');
                if (hours) {
                    count = hours;
                    unit = 'hours';
                }

                const days = now.diff(givenDate, 'days');
                if (days) {
                    count = days;
                    unit = 'days';
                }

                const months = now.diff(givenDate, 'months');
                if (months) {
                    count = months;
                    unit = 'months';
                }

                const years = now.diff(givenDate, 'years');
                if (years) {
                    count = years;
                    unit = 'years';
                }

                getPostCreatedInfo = this.$t('community.community.post.created-info.' + unit, {
                    count: count,
                    category: this.getPostCategory(post)
                });
            } else {
                getPostCreatedInfo = this.$t('community.community.post.scheduled-on', {
                    date: moment(post.publish_at).locale(this.$i18n.locale).format('MMM Do YYYY'),
                    category: this.getPostCategory(post)
                });
            }

            return getPostCreatedInfo;
        },

        getPostCategory(post) {
            return post.category?.title || this.$t('community.community.category-options.all');
        },

    }
}
</script>
<style lang="scss" scoped>
$color_1: #fff;
$color_2: #909090;
$pin_background: #9198FF;
$schedule_background: #a77b53;

.community-schedule-actions {
    position: absolute;
    right: 20px;
    top: 60px;
}

.post-pinned {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: $color_1;
    background-color: $pin_background;
    padding: 8px 15px;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
}

.post-scheduled {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: $color_1;
    background-color: $schedule_background;
    padding: 8px 15px;
    border-top-left-radius: 6px;
    border-top-right-radius: 6px;
}

.post-card-content {
    padding: 0px 15px 15px 15px;
    display: flex;
    align-items: flex-start;
}

.post-body {
    cursor: pointer;
    padding: 5px;
}

.post-item-header {
    padding: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.post-created {
    font-size: 13px;
    color: $color_2;
}

.post-title {
    font-weight: bold;
    font-size: 20px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.post-content {
    margin-top: 10px;
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: pre-wrap;
}

.post-footer {
    padding: 5px 15px 10px 15px;
    display: flex;
    align-items: center;
}

.community-post-item {
    cursor: pointer;
    position: relative;

    &:hover {
        box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 1px 3px 1px;
    }
}

.post-poll-vote-section {
    display: flex;
    align-items: center;
    padding: 5px 15px;
}

.poll-label-section {
    padding: 2px 15px;
    border: 1px solid #0eb769;
    border-radius: 20px;
    color: #0eb769;
    margin-right: 7px;
    font-weight: 500;
}

.poll-desc-section {
    color: #0eb769;
    font-weight: 500;
}

.close-post {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.close-post:hover {
    background-color: rgb(228, 228, 228);
}

@media only screen and (max-width: 600px) {
    .post-pinned {
        padding: 4px 10px;
    }

    .post-scheduled {
        padding: 4px 10px;
    }

    .post-card-content {
        padding: 0px 10px 10px 10px;
    }

    .post-item-header {
        padding: 10px;
    }

    .post-created {
        font-size: 12px;
    }

    .post-title {
        font-size: 16px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: pre-wrap;
    }

    .post-content {
        margin-top: 5px;
        -webkit-line-clamp: 3;
        font-size: 14px;
    }

    .post-footer {
        padding: 5px 10px 10px 10px;
        font-size: 14px;
    }
}
</style>