<template>
    <transition-group
        name="fade"
        tag="ul"
        class="comments-list"
    >
        <div
            v-for="childComment in comment.children"
            :key="childComment.id"
            class="comment-item"
        >
            <EditComment v-if="childComment.showMode === 'edit'" />
            <div v-else class="w100">
                <div class="post-comment-section">
                    <img :src="getMemberGravatar(childComment.member)" class="customer-gravatar" />
                    <div class="post-comment-content">
                        <div v-if="memberExist && (childComment.member_id === member.id || isManager(role))" class="dropdown post-comment-action-section">
                            <div
                                :id="'community_post_comment_link_' + childComment.id"
                                class="dropdown-trigger post-comment-action-link"
                                @click.stop="showPostCommentDropdown(childComment.id)"
                                v-click-outside="hidePostCommentDropdown"
                            >
                                <font-awesome-icon icon="fa fa-ellipsis" class="font-16px link-icon" />
                            </div>
                            <div 
                                :id="'community_post_comment_action_content_' + childComment.id" 
                                class="dropdown-menu post-comment-action-content"
                                :class="displayPostCommentDropdown === childComment.id ? 'show' : ''"
                            >
                                <div class="dropdown-content">
                                    <div
                                        v-if="childComment.editable && childComment.member_id === member.id"
                                        class="tab-content-item-action-item"
                                        @click.stop="editPostComment(childComment)"
                                    >
                                        <font-awesome-icon icon="fa fa-pencil"/> &nbsp;
                                        {{ $t('community.community.post.edit-post-comment') }}
                                    </div>
                                    <div
                                        class="tab-content-item-action-item"
                                        @click.stop="deletePostComment(childComment)"
                                    >
                                        <font-awesome-icon icon="fa fa-trash"/> &nbsp;
                                        {{ $t('community.community.post.delete-post-comment') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="post-comment-content-title">
                            <div class="flex align-items-center">
                                <div class="customer-name">
                                    {{ getMemberName(childComment.member) }}
                                </div>
                                <div v-if="isManager(commentOwnerRole(childComment))" class="customer-role ml-05">
                                    {{ $t(`community.members.role-options.${commentOwnerRole(childComment)}`) }}
                                </div>
                                <template v-for="commentGroup, i in commentOwnerGroups(childComment)" :key="commentGroup.id">
                                    <div class="customer-group ml-05">
                                        {{ commentGroup.name }}
                                    </div>
                                </template>
                            </div>
                            <div class="post-comment-created-at">
                                • {{ getCreatedInfo(childComment) }}
                            </div>
                        </div>

                        <div class="mb-05 comment-content" v-html="linkify(updateViewContentForMention(childComment.content))">
                        </div>

                        <div class="community-media-grid-container" v-if="childComment.medias.length > 0">
                            <media-thumb
                                v-for="media, i in childComment.medias"
                                :key="media.id"
                                :media="media"
                                additionalClass="community-media community-media-grid-item"
                                :viewMedia="true"
                            />
                        </div>
                    </div>
                </div>

                <div class="post-comment-footer">
                    <font-awesome-icon
                        icon="fa fa-thumbs-up"
                        class="mr-05 mt-min-3px like-icon"
                        :class="getCommunityPostCommentLikeStatus(childComment)"
                        @click="togglePostCommentLike(childComment)"
                    />
                    <span class="font-weight-500 mr1">
                        {{ childComment.number_of_likes }}
                    </span>
                    <button v-if="communityPost.visibility === 1 && communityPost.disable_comment === 0" class="button is-small post-comment-reply" @click="replyToComment(childComment)">
                        {{ $t('community.community.post.reply') }}
                    </button>
                </div>

                <div class="add-comment-section">
                    <AddComment v-if="childComment.replyShow" owner="reply" />
                </div>

                <comment-view :comment="childComment" />
            </div>
        </div>
    </transition-group>
</template>

<script>

import getMemberName from "../../../../mixins/util";
import getMemberTag from "../../../../mixins/util";
import getMemberGravatar from "../../../../mixins/util";
import getCreatedInfo from "../../../../mixins/util";
import linkify from "../../../../mixins/util";
import updateViewContentForMention from "../../../../mixins/util";
import isManager from "../../../../mixins/util";

import AddComment from "./AddComment";
import EditComment from "./EditComment";
import MediaThumb from "../../../Media/MediaThumb.vue";
import { MemberAccess } from '../../../../data/enums';

export default {
    name: 'CommentView',
    props: ['comment'],
    mixins: [
        getMemberName,
        getMemberTag,
        getMemberGravatar,
        getCreatedInfo,
        linkify,
        isManager,
        updateViewContentForMention
    ],
    components: {
        AddComment,
        EditComment,
        MediaThumb
    },
    data() {
        return {
            MemberAccess,
            displayPostCommentDropdown: false
        }
    },
    computed: {
        /**
         * Return auth status
         */
        auth ()
        {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns member
         */
        member ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist ()
        {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns access of member
         */
        access ()
        {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * Returns role of member
         */
        role ()
        {
            return this.memberExist ? this.member.role : null;
        },

        /**
         * Return online coach community post
         */
        communityPost ()
        {
            return this.$store.state.post.data;
        },
    },
    methods: {
        getCommunityPostCommentLikeStatus (comment) {
            let likeStatusClass = '';
            if (this.memberExist && comment.member_id === this.member.id) {
                likeStatusClass = 'disabled';
            } else {
                likeStatusClass = comment.is_member_like ? 'liked' : '';
            }

            return likeStatusClass;
        },

        /**
         * Toggle post like
         */
        async togglePostCommentLike (comment)
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                await this.$store.dispatch('LIKE_POST_COMMENT', {
                  id: comment.id,
                  post_id: comment.post_id
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
         * Close add comment and adjust height of add comment
         */
        closeAddComment() {
            this.$store.commit('resetCommunityPostComment');

            let activeElement = document.getElementById('add_comment_textarea');
            if (activeElement) {
                activeElement.style.height = "40px";
                const parent = activeElement.parentNode;
                const links = parent.querySelector('.textarea-links');

                if (links) {
                    if (window.innerWidth <= 600) {
                        links.style.marginTop = "0px";
                    } else {
                        links.style.marginTop = "-40px";
                    }
                }

                activeElement = null;
            }
        },

        /**
         * Reply to comment
         */
        replyToComment(comment)
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                this.closeAddComment();
                this.$store.commit('setCommentShowModeToView');

                let content = '';
                if (typeof comment.member !== 'undefined' && comment.member !== null) {
                  content = this.getMemberTag(comment.member) + ' ';
                  this.$store.commit('addMentionedMember', {
                    id: comment.member.id,
                    tag: content
                  });
                }

                this.$store.commit('resetReplyComment', {
                  content,
                  parentId: comment.id
                });
                this.$store.commit('showReplySection', comment);

                this.emitter.emit("closeMentionMenu", {});
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
         * View media detail
         */
        viewMedia(item)
        {
            this.$store.commit('showChildModal', {
                modalType: 'ViewMedia',
                extraData: item
            });
        },

        /**
         * Show login modal
         */
        showLogin ()
        {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },

        showPostCommentDropdown(commentId) {
            if (this.displayPostCommentDropdown) {
                if (this.displayPostCommentDropdown !== commentId) {
                    this.displayPostCommentDropdown = commentId;
                } else {
                    this.displayPostCommentDropdown = false;
                }
            } else {
                this.displayPostCommentDropdown = commentId;
            }
        },

        hidePostCommentDropdown() {
            this.displayPostCommentDropdown = false;
        },

        /**
         * Close add comment and adjust height of add comment
         */
        closeAddComment() {
            this.$store.commit('resetCommunityPostComment');

            let activeElement = document.getElementById('add_comment_textarea');
            if (activeElement) {
                activeElement.style.height = "40px";
                const parent = activeElement.parentNode;
                const links = parent.querySelector('.textarea-links');

                if (links) {
                    if (window.innerWidth <= 600) {
                        links.style.marginTop = "0px";
                    } else {
                        links.style.marginTop = "-40px";
                    }
                }

                activeElement = null;
            }
        },

        commentOwnerRole(comment) {
            return comment.member?.role || '';
        },

        commentOwnerGroups(comment) {
            let commentOwnerGroups = comment.member?.groups || [];
            commentOwnerGroups = commentOwnerGroups.filter(el => el.publish > 0);

            return commentOwnerGroups;
        },

        editPostComment(comment) {
            this.closeAddComment();

            comment.showMode = 'edit';
            this.$store.commit('setEditedComment', comment);
            this.$store.commit('updateCommunityPostComments', comment);
        },

        deletePostComment(comment) {
            this.closeAddComment();

            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.community.post.confirm-delete-comment.title'),
                    desc: this.$t('community.community.post.confirm-delete-comment.desc'),
                    action: 'DELETE_POST_COMMENT',
                    param: comment.id,
                    hideModal: false
                }
            });
        },
    }
}
</script>

<style scoped>
    .comments-list {
        padding-left: 45px;
    }

    .comment-content {
        white-space: pre-wrap;
    }
</style>
