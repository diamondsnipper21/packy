<template>
    <div class="inner-modal-container" @click="outclick">

        <div class="post-header-container">
            <div class="flex align-items-center">
                <img :src="getMemberGravatar(communityPost.member)" class="community-customer-gravatar" />
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

                    <div class="post-created" v-html="getPostCreatedInfo(communityPost)"></div>
                </div>
            </div>

            <div class="flex align-items-center">
                <div v-if="viewPostMode === 'view' && communityPost.visibility === 1 && (isManager(role) || (memberExist && communityPost.member_id === member.id))"
                    class="dropdown mr1">
                    <div
                        class="dropdown-trigger post-action-link"
                         @click="showPostAction"
                        v-click-outside="hidePostAction"
                    >
                        <font-awesome-icon icon="fa fa-ellipsis" class="font-20px" />
                    </div>
                    <div
                        id="community_post_action"
                        class="dropdown-menu"
                        :class="displayPostAction ? 'show' : ''"
                    >
                        <div class="dropdown-content">
                            <template v-if="(isManager(role)) && !scheduled">
                                <div class="tab-content-item-action-item" @click.stop="togglePinPost">
                                    <font-awesome-icon icon="fa fa-thumbtack" /> &nbsp;
                                    <span v-if="communityPost.pinned === 1">
                                        {{ $t('community.community.post.unpin') }}
                                    </span>
                                    <span v-else>
                                        {{ $t('community.community.post.pin') }}
                                    </span>
                                </div>

                                <div class="tab-content-item-action-item" @click.stop="togglePinPostPage(PostAction.PIN_TO_PAGE)">
                                    <font-awesome-icon icon="fa fa-thumbtack" /> &nbsp;
                                    <span>
                                        {{ $t('community.community.post.pin-to-course-page') }}
                                    </span>
                                </div>

                                <div class="tab-content-item-action-item" @click.stop="togglePinPostPage(PostAction.UNPIN_FROM_PAGE)">
                                    <font-awesome-icon icon="fa fa-thumbtack" /> &nbsp;
                                    <span>
                                        {{ $t('community.community.post.unpin-from-course-page') }}
                                    </span>
                                </div>

                            </template>

                            <div v-if="(scheduled || communityPost.editable) && memberExist && communityPost.member_id === member.id"
                                class="tab-content-item-action-item" @click.stop="editPost">
                                <font-awesome-icon icon="fa fa-pencil" /> &nbsp;
                                {{ $t('community.community.post.edit-post') }}
                            </div>
                            <div v-if="communityPost.disable_comment === 0 && !scheduled"
                                class="tab-content-item-action-item" @click.stop="disableCommenting">
                                <font-awesome-icon icon="fa fa-comment" /> &nbsp;
                                {{ $t('community.community.post.disable-post-comment') }}
                            </div>
                            <div v-if="communityPost.disable_comment === 1 && !scheduled"
                                class="tab-content-item-action-item" @click.stop="enableCommenting">
                                <font-awesome-icon icon="fa fa-comment" /> &nbsp;
                                {{ $t('community.community.post.enable-post-comment') }}
                            </div>
                            <div class="tab-content-item-action-item" @click.stop="deletePost(communityPost)">
                                <font-awesome-icon icon="fa fa-trash" /> &nbsp;
                                {{ $t('community.community.post.delete-post') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Close button -->
                <font-awesome-icon icon="fa fa-times" class="pointer" @click="close" />
            </div>
        </div>

        <div class="post-content-container" id="post_content_container">
            <div v-if="viewPostMode === 'view'">
                <div class="post-title" v-html="communityPost.title">
                </div>
                <p class="post-content" v-html="postViewContent"></p>
            </div>
            <div v-else-if="viewPostMode === 'edit'">
                <div class="flex mt1">
                    <!-- Title -->
                    <div class="flex-1">
                        <p class="text-left input-label">{{ $t('community.community.title') }}</p>
                        <input class="input" required :placeholder="$t('community.community.title-placeholder')"
                            v-model="title" autofocus />
                    </div>
                </div>

                <div class="flex mt1">
                    <!-- Category -->
                    <div class="flex-1">
                        <p class="text-left input-label">{{ $t('community.community.category') }}</p>
                        <select v-model="communityPost.category_id" class="input mb1">
                            <option value="0">{{ $t('community.community.category-options.all') }}</option>
                            <option v-for="categoryItem in categories" :value="categoryItem.id">
                                {{ categoryItem.title }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex mt1">
                    <!-- Description -->
                    <div class="flex-1">
                        <p class="text-left input-label">{{ $t('community.community.description') }}</p>

                        <InputBoxWithMention 
                            id="edit_post_textarea" 
                            action="edit_post"
                            owner="post"
                            :content="postEditContent" 
                            :placeholder="$t('community.community.description')" 
                            :rows="editPostTextareaRows" 
                        />
                    </div>
                </div>
            </div>

            <div v-if="polls.length > 0" class="mt1">
                <div v-if="viewPostMode === 'view'">
                    <div v-for="poll in polls" class="poll-option">
                        <template v-if="poll.type === 'checkbox'">
                            <input class="mr-05 pointer" type="checkbox" v-model="poll.votedPoll"
                                @click="votePostPoll(poll)" />
                            <div class="poll-option-section" @click="votePostPoll(poll)">
                                <div class="poll-option-progress" :style="voteProgressCss(poll)"></div>
                                <div class="poll-option-left" :class="poll.votedCnt === 0 ? 'full-width' : ''"
                                    :style="pollOptionLeftCss(poll)">
                                    <div class="poll-option-label">
                                        {{ poll.content }}
                                    </div>
                                </div>
                                <div v-if="voted && poll.votedCnt > 0" class="poll-option-right"
                                    :style="pollOptionRightCss(poll)">
                                    <div v-if="poll.voted !== null && poll.voted !== ''">
                                        <div class="font-12px font-weight-600">
                                            {{ $t('community.poll.votes', { count: poll.votedCnt }) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <input class="mr-05 pointer" type="radio" v-model="votedPoll" :value="poll.id"
                                @click="votePostPoll(poll)" />
                            <div class="poll-option-section" @click="votePostPoll(poll)">
                                <div class="poll-option-progress" :style="voteProgressCss(poll)"></div>
                                <div class="poll-option-left" :class="poll.votedCnt === 0 ? 'full-width' : ''"
                                    :style="pollOptionLeftCss(poll)">
                                    <div class="poll-option-label">
                                        {{ poll.content }}
                                    </div>
                                </div>
                                <div v-if="voted && poll.votedCnt > 0" class="poll-option-right"
                                    :style="pollOptionRightCss(poll)">
                                    <div v-if="poll.voted !== null && poll.voted !== ''">
                                        <div class="font-12px font-weight-600">
                                            {{ $t('community.poll.votes', { count: poll.votedCnt }) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div v-if="communityPost.pollVotedMemberCount > 0" class="voted-count">
                        {{ $t('community.poll.votes', { count: communityPost.pollVotedMemberCount }) }}
                    </div>
                </div>

                <div v-else-if="viewPostMode === 'edit'" class="add-poll-section">
                    <div class="flex align-items-center jcb mb1">
                        <div class="font-weight-600">
                            {{ $t('community.poll.poll') }}
                        </div>
                        <div class="remove-poll-link" @click="hideAddPoll">
                            {{ $t('common.remove') }}
                        </div>
                    </div>

                    <div v-for="poll in polls" class="poll-option">
                        <div class="poll-option-section ml0">
                            <div class="poll-option-left w100">
                                <div class="poll-option-label">
                                    {{ poll.content }}
                                </div>
                            </div>
                        </div>
                        <a href="#" class="control ml-05" @click="removePollOption(poll.id)">
                            <font-awesome-icon icon="fa fa-times" class="link-icon" />
                        </a>
                    </div>
                </div>
            </div>

            <div class="community-media-grid-container mt1" v-if="medias.length > 0">
                <media-thumb v-for="media, i in medias" :key="media.id" :media="media"
                    additionalClass="community-media community-media-grid-item" :viewMedia="true" :hoverMedia="false" />
            </div>

            <div v-if="viewPostMode === 'edit'" class="mt1 text-right">
                <button class="button is-medium" @click="closeEditPost">
                    {{ $t('common.cancel') }}
                </button>
                <button :class="button" @click="saveCommunityPost" :disabled="disabledConfirm"
                    class="ml-05 community-blue-btn">{{
                        $t('common.submit') }}</button>
            </div>

            <div v-if="viewPostMode === 'view'" class="mt1">
                <div class="post-content-footer">
                    <div class="post-likes-section">
                        <div class="post-likes-label"
                            :class="memberExist && communityPost.member_id === member.id ? 'disabled' : ''"
                            @click="togglePostLike">
                            <font-awesome-icon icon="fa fa-thumbs-up" class="mr-05 mt-min-3px"
                                :class="communityPost.is_member_like ? 'liked' : ''" />
                            <span v-if="communityPost.is_member_like">
                                {{ $t('community.community.post.liked') }}
                            </span>
                            <span v-else>
                                {{ $t('community.community.post.like') }}
                            </span>
                        </div>
                        <div class="post-likes-value">
                            {{ communityPost.number_of_likes }}
                        </div>
                    </div>

                    <div class="post-comments-count-section">
                        <font-awesome-icon icon="fa fa-comment" :class="'mr-05'" />
                        <div class="post-comments-count">
                            {{ comments.length }}
                            {{ $t('community.community.post.comments') }}
                        </div>
                    </div>
                </div>

                <div class="w100" v-if="hierarchicalComments.length > 0">
                    <div v-for="comment in hierarchicalComments" :key="comment.id" class="w100">
                        <EditComment v-if="comment.showMode === 'edit'" />

                        <div v-else class="w100">
                            <div class="post-comment-section">
                                <img v-if="comment.member" :src="getMemberGravatar(comment.member)" class="customer-gravatar" />
                                <img v-else src="/assets/img/default.png" class="customer-gravatar" />
                                <div class="post-comment-content">
                                    <div v-if="memberExist && (comment.member_id === member.id || isManager(role))"
                                        class="dropdown post-comment-action-section">
                                        <div
                                            :id="'community_post_comment_link_' + comment.id"
                                            class="dropdown-trigger post-comment-action-link"
                                            @click.stop="showPostCommentDropdown(comment.id)"
                                            v-click-outside="hidePostCommentDropdown"
                                        >
                                            <font-awesome-icon icon="fa fa-ellipsis" class="font-16px link-icon" />
                                        </div>
                                        <div
                                            :id="'community_post_comment_action_content_' + comment.id"
                                            class="dropdown-menu post-comment-action-content"
                                            :class="displayPostCommentActionDropdown === comment.id ? 'show' : ''"
                                        >
                                            <div class="dropdown-content">
                                                <div v-if="comment.editable && comment.member_id === member.id"
                                                    class="tab-content-item-action-item"
                                                    @click.stop="editPostComment(comment)">
                                                    <font-awesome-icon icon="fa fa-pencil" /> &nbsp;
                                                    {{ $t('community.community.post.edit-post-comment') }}
                                                </div>
                                                <div class="tab-content-item-action-item"
                                                    @click.stop="deletePostComment(comment)">
                                                    <font-awesome-icon icon="fa fa-trash" /> &nbsp;
                                                    {{ $t('community.community.post.delete-post-comment') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="post-comment-content-title">
                                        <div class="flex align-items-center">
                                            <div class="customer-name">
                                                {{ getMemberName(comment.member) }}
                                            </div>
                                            <div v-if="isManager(commentOwnerRole(comment))" class="customer-role ml-05">
                                                {{ $t(`community.members.role-options.${commentOwnerRole(comment)}`) }}
                                            </div>
                                            <template v-for="commentGroup, i in commentOwnerGroups(comment)" :key="commentGroup.id">
                                                <div class="customer-group ml-05">
                                                    {{ commentGroup.name }}
                                                </div>
                                            </template>
                                        </div>

                                        <div class="post-comment-created-at">
                                            • {{ getCreatedInfo(comment) }}
                                        </div>
                                    </div>

                                    <div class="mb-05 comment-content" v-html="linkify(updateViewContentForMention(comment.content))">
                                    </div>

                                    <div class="community-media-grid-container" v-if="(comment.medias || []).length > 0">
                                        <media-thumb v-for="media, i in comment.medias" :key="media.id" :media="media"
                                            additionalClass="community-media community-media-grid-item"
                                            :viewMedia="true" :hoverMedia="false" />
                                    </div>
                                </div>
                            </div>

                            <div class="post-comment-footer">
                                <font-awesome-icon icon="fa fa-thumbs-up" class="mr-05 mt-min-3px like-icon"
                                    :class="getCommunityPostCommentLikeStatus(comment)"
                                    @click="togglePostCommentLike(comment)" />
                                <span class="font-weight-500 mr1">
                                    {{ comment.number_of_likes }}
                                </span>

                                <button v-if="communityPost.visibility === 1 && communityPost.disable_comment === 0"
                                    class="button is-small post-comment-reply" @click="replyToComment(comment)">
                                    {{ $t('community.community.post.reply') }}
                                </button>
                            </div>

                            <div class="add-comment-section">
                                <AddComment v-if="comment.replyShow" owner="reply" />
                            </div>

                            <comment-view :comment="comment" />
                        </div>
                    </div>
                </div>

                <AddComment v-if="communityPost.visibility === 1 && communityPost.disable_comment === 0 && !scheduled"
                    owner="comment" />
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment'

import getMemberName from "../../mixins/util";
import getMemberTag from "../../mixins/util";
import getMemberGravatar from "../../mixins/util";
import getCreatedInfo from "../../mixins/util";
import linkify from "../../mixins/util";

import MediaThumb from "../Media/MediaThumb.vue";
import AddComment from "./components/comment/AddComment";
import EditComment from "./components/comment/EditComment";
import CommentView from "./components/comment/CommentView";
import { MemberAccess, PostAction, PostType } from '../../data/enums';

import isManager from "../../mixins/util";
import updateViewContentForMention from "../../mixins/util";
import updateEditContentForMention from "../../mixins/util";

import InputBoxWithMention from "../General/Elements/InputBoxWithMention";

export default {
    name: 'ViewCommunityPost',
    mixins: [
        getMemberName,
        getMemberTag,
        getMemberGravatar,
        getCreatedInfo,
        linkify,
        isManager,
        updateViewContentForMention,
        updateEditContentForMention
    ],
    components: {
        MediaThumb,
        AddComment,
        EditComment,
        CommentView,
        InputBoxWithMention
    },
    data() {
        return {
            MemberAccess,
            PostAction,
            PostType,
            processing: false,
            voted: false,
            pollVotedCount: 0,
            isPollChanged: false,
            editPostTextareaRows: 5,
            displayPostAction: false,
            displayPostCommentActionDropdown: false,
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
         * Add ' is-loading' when processing
         */
        button() {
            let button = 'button is-medium ';

            return (this.processing)
                ? button + ' is-loading'
                : button;
        },

        /**
         * Return online coach community post
         */
        communityPost() {
            return this.$store.state.post.data;
        },

        postOwnerName() {
            return this.getMemberName(this.communityPost.member);
        },
        postOwnerRole() {
            return this.communityPost.member?.role || '';
        },
        postOwnerGroups() {
            let postOwnerGroups = this.communityPost.member?.groups || [];
            postOwnerGroups = postOwnerGroups.filter(el => el.publish > 0);

            return postOwnerGroups;
        },

        /**
         * Return scheduled props
         */
        scheduled() {
            return this.communityPost.scheduled;
        },

        /**
         * Return edit save button disable status
         */
        disabledConfirm() {
            let disabledConfirm = true;
            if (this.title !== '') {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * extra data of child modal
         */
        modalExtraData() {
            return this.$store.state.modal.extraData;
        },

        /**
         * Get | Set title
         */
        title: {
            get() {
                return this.communityPost.title;
            },
            set(v) {
                this.$store.commit('setCommunityPostProperty', {
                    key: 'title',
                    v
                });
            }
        },

        /**
         * Get | Set content
         */
        content: {
            get() {
                return this.communityPost.content;
            },
            set(v) {
                this.$store.commit('setCommunityPostProperty', {
                    key: 'content',
                    v
                });
            }
        },

        /**
         * Return post content for view
         */
        postViewContent() {
            return this.linkify(this.updateViewContentForMention(this.content));
        },

        /**
         * Return post content for edit
         */
        postEditContent() {
            return this.updateEditContentForMention(this.content);
        },

        /**
         * Return post likes array
         */
        postLikes() {
            let postLikes = [];
            if (typeof this.communityPost.likes !== 'undefined' && this.communityPost.likes !== null && this.communityPost.likes !== '') {
                postLikes = this.communityPost.likes.split(",");
            }

            return postLikes;
        },

        /**
         * Get community post likes status
         */
        getCommunityPostLikeStatus() {
            let likeStatus = false;
            if (this.memberExist) {
                let memberId = this.member.id.toString();
                if (this.postLikes.includes(memberId)) {
                    likeStatus = true;
                }
            }

            return likeStatus;
        },

        /**
         * Get community post likes count
         */
        getCommunityPostLikesCount() {
            let postLikes = [];
            if (typeof this.communityPost.likes !== 'undefined' && this.communityPost.likes !== null && this.communityPost.likes !== '') {
                postLikes = this.communityPost.likes.split(",");
            }

            return postLikes.length;
        },

        /**
         * Return community post comments
         */
        comments() {
            let comments = [];
            if (typeof this.communityPost.comments !== 'undefined') {
                comments = this.communityPost.comments;
            }

            return comments;
        },

        /**
         * Return community post hierarchical comments
         */
        hierarchicalComments() {
            let hierarchicalComments = [];
            if (typeof this.communityPost.hierarchicalComments !== 'undefined') {
                hierarchicalComments = this.communityPost.hierarchicalComments;
            }

            return hierarchicalComments;
        },

        /**
         * Return community post medias
         */
        medias() {
            return this.communityPost.medias || [];
        },

        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns community owner
         */
        communityOwner() {
            return this.community.user;
        },

        /**
         * Return community post polls
         */
        polls() {
            this.pollVotedCount = 0;

            let thisMemberId = null;
            thisMemberId = this.member?.id || null;
            if (thisMemberId) {
                thisMemberId = thisMemberId.toString();
            }

            let polls = JSON.parse(JSON.stringify(this.communityPost.polls || []));
            if (polls.length > 0) {
                polls = polls.map((poll) => {
                    let postVoted = [];
                    poll.votedPoll = false;
                    if (poll.voted !== null && poll.voted !== '') {
                        postVoted = poll.voted.split(",");
                        this.pollVotedCount += postVoted.length;
                        if (thisMemberId && postVoted.includes(thisMemberId)) {
                            this.voted = true;
                            poll.votedPoll = true;
                        }
                    }

                    poll.votedCnt = postVoted.length;
                    return { ...poll };
                });
            }

            return polls;
        },

        /**
         * Returns viewPostMode
         */
        viewPostMode() {
            return this.$store.state.post.viewPostMode;
        },

        /**
         * Return parent page of this post
         */
        parentPage() {
            return this.$store.state.post.parentPage;
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
         * Get | Set voted poll
         */
        votedPoll: {
            get() {
                let votedPoll = 0;

                let thisMemberId = null;
                thisMemberId = this.member?.id || null;
                if (thisMemberId) {
                    thisMemberId = thisMemberId.toString();
                }

                let polls = JSON.parse(JSON.stringify(this.polls));
                polls.map(poll => {
                    let voted = poll.voted;
                    if (voted && voted !== '') {
                        let postVoted = voted.split(",");
                        if (thisMemberId && postVoted.includes(thisMemberId)) {
                            votedPoll = poll.id;
                        }
                    }
                });

                return votedPoll;
            },
            set(v) {

            }
        },

        /**
         * Get lessons of this community
         */
        lessons() {
            return this.$store.state.classroom.lessons;
        },

        /**
         * Return active tab
         */
        selectedTab() {
            return this.$store.state.communitycenter.selectedTab;
        },
    },
    methods: {
        /**
         * Toggle post like
         */
        async togglePostLike() {
            if (this.communityPost.visibility === 1) {
                if (this.auth) {
                  if (this.access === MemberAccess.ALLOWED) {
                    await this.$store.dispatch('LIKE_COMMUNITY_POST', {
                      id: this.communityPost.id
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
            }
        },

        /**
         * Get comment likes status
         */
        getCommunityPostCommentLikeStatus(comment) {
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
        async togglePostCommentLike(comment) {
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
         * Reply to comment
         */
        replyToComment(comment) {
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
        viewMedia(item) {
            this.$store.commit('showChildModal', {
                modalType: 'ViewMedia',
                extraData: item
            });
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
         * Show login modal
         */
        showLogin() {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },

        showPostAction() {
            this.displayPostAction = !this.displayPostAction;
        },

        hidePostAction() {
            this.displayPostAction = false;
        },

        showPostCommentDropdown(commentId) {
            if (this.displayPostCommentActionDropdown) {
                if (this.displayPostCommentActionDropdown !== commentId) {
                    this.displayPostCommentActionDropdown = commentId;
                } else {
                    this.displayPostCommentActionDropdown = false;
                }
            } else {
                this.displayPostCommentActionDropdown = commentId;
            }
        },

        hidePostCommentDropdown() {
            this.displayPostCommentActionDropdown = false;
        },

        async togglePinPost() {
            this.$store.commit('setCommunityPostProperty', {
                key: 'pinned',
                v: this.communityPost.pinned === 1 ? 0 : 1
            });
            await this.$store.dispatch('UPDATE_COMMUNITY_POST', {
                hideModal: false,
                notification: false,
                id: this.communityPost.id,
            });
        },

        async togglePinPostPage(action) {
            await this.$store.dispatch('GET_LESSONS', {
                id: this.communityPost.id,
                action
            });

            this.$store.commit('showChildModal', {
                modalType: 'TogglePinPostPage',
                extraData: {
                    pages: this.lessons,
                    action
                }
            });
        },

        editPost() {
            this.$store.commit('setViewPostMode', 'edit');
        },

        deletePost(post) {
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.community.post.confirm-delete.title'),
                    desc: this.$t('community.community.post.confirm-delete.desc'),
                    action: 'DELETE_COMMUNITY_POST',
                    param: { id: post.id },
                    hideModal: true
                }
            });
        },

        async disableCommenting(post) {
            this.$store.commit('setCommunityPostProperty', {
                key: 'disable_comment',
                v: 1
            });
            await this.$store.dispatch('UPDATE_COMMUNITY_POST', {
                hideModal: false,
                notification: false,
                id: this.communityPost.id,
            });
        },

        async enableCommenting(post) {
            this.$store.commit('setCommunityPostProperty', {
                key: 'disable_comment',
                v: 0
            });
            await this.$store.dispatch('UPDATE_COMMUNITY_POST', {
                hideModal: false,
                notification: false,
                id: this.communityPost.id,
            });
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

        /**
         * Close edit post
         */
        async closeEditPost() {
            this.$store.commit('hideModal');
        },

        /**
         * Strip html
         */
        stripHtml(html) {
            let tmp = document.createElement("DIV");
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || "";
        },

        /**
         * Removes post media
         */
        removeCommunityPostMedia(item) {
            this.$store.commit('removeCommunityPostMedia', item);
        },

        async votePostPoll(poll) {
            const id = poll.id;
            const type = poll.type;
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                if (this.scheduled) return;
                let memberId = this.member.id.toString();
                let polls = JSON.parse(JSON.stringify(this.polls));
                polls = polls.map((poll) => {
                  let postVoted = [];
                  if (poll.voted !== null && poll.voted !== '') {
                    postVoted = poll.voted.split(",");
                  }

                  if (poll.id === id) {
                    if (!postVoted.includes(memberId)) {
                      postVoted.push(memberId);
                    } else {
                      const index = postVoted.indexOf(memberId);
                      postVoted.splice(index, 1);
                    }
                  } else {
                    if (type !== 'checkbox') {
                      if (postVoted.includes(memberId)) {
                        const index = postVoted.indexOf(memberId);
                        postVoted.splice(index, 1);
                      }
                    }
                  }

                  poll.voted = postVoted.join(",");

                  return { ...poll };
                });

                const votedPoll = polls.find(item => item.id == poll.id)
                this.$store.commit('setCommunityPostProperty', {
                  key: 'polls',
                  v: polls
                });

                await this.$store.dispatch('VOTE_COMMUNITY_POST', {
                  postId: this.communityPost.id,
                  votedPoll
                });

                this.voted = true;
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
        },

        /**
         * Save community post
         */
        async saveCommunityPost() {
            this.processing = true;
            this.$store.commit('setCommunityPostProperty', {
                key: 'isPollChanged', v: this.isPollChanged
            });

            if (this.communityPost.scheduled) {
                this.$store.commit('setCommunityPostProperty', {
                    key: 'scheduledInfo', v: false
                });
                await this.$store.dispatch('UPDATE_SCHEDULED_POST', {
                    communityId: this.community.id,
                    id: this.communityPost.id,
                });
            } else {
                await this.$store.dispatch('UPDATE_COMMUNITY_POST', {
                    hideModal: false,
                    id: this.communityPost.id,
                });
            }
            this.processing = false;
            this.$store.commit('setViewPostMode', 'view');
        },

        outclick(e) {
            if (!e.target.classList.contains('post-comment-action-section') && !e.target.classList.contains('post-comment-action-link') && !e.target.classList.contains('post-action-link') && !e.target.classList.contains('dropdown') && !e.target.classList.contains('fa-ellipsis') && e.target.parentElement && !e.target.parentElement.classList.contains('fa-ellipsis')) {
                Array.prototype.forEach.call(document.getElementsByClassName('post-comment-action-link'), function (el) {
                    el.classList.remove("active");
                });
                Array.prototype.forEach.call(document.getElementsByClassName('post-comment-action-content'), function (el) {
                    el.classList.remove("show");
                });
            }
        },

        /**
         * Close the modal
         */
        close() {
            this.voted = false;
            this.emitter.emit("closeMentionMenu", {});
            this.$store.commit('hideModal');

            // Go to parent page if exist
            const params = this.parentPage.split("/");
            if (this.parentPage !== '' && typeof params[2] !== 'undefined' && this.selectedTab === params[2]) {
                this.$router.push(this.parentPage).catch(() => {});
                this.$store.commit('setCommunityTab', this.selectedTab);
            }
        },

        /**
         * Hide add poll section
         */
        hideAddPoll() {
            this.isPollChanged = true;
            this.$store.commit('setCommunityPostProperty', {
                key: 'polls',
                v: []
            });
        },

        /**
         * Remove poll option
         */
        removePollOption(id) {
            this.isPollChanged = true;
            let polls = JSON.parse(JSON.stringify(this.polls));
            polls = polls.filter(el => el.id !== id);
            this.$store.commit('setCommunityPostProperty', {
                key: 'polls',
                v: polls
            });
        },

        /**
         * Generate vote progress percent css
         */
        voteProgressCss(poll) {
            let css = '';
            let votePercent = 0;
            if (this.pollVotedCount > 0 && poll.voted !== null && poll.voted !== '') {
                votePercent = Math.round(poll.votedCnt * 100 / parseInt(this.pollVotedCount));
            }

            // if this member has not voted, then set progress percent to 0
            if (!this.voted) {
                votePercent = 0;
            }

            css = 'width: ' + votePercent + '%;';

            return css;
        },

        /**
         * Calculate option right width
         */
        pollOptionRightWidth(poll) {
            let imgWidth = 0;
            let firstImgWidth = 40;
            let oneImgWidth = 35;

            // phone view
            if (window.innerWidth <= 600) {
                firstImgWidth = 30;
                oneImgWidth = 30;
            }

            if (poll.votedCnt === 1) {
                imgWidth = firstImgWidth;
            } else if (poll.votedCnt > 1) {
                imgWidth = firstImgWidth + (poll.votedCnt - 1) * oneImgWidth;
            }

            if (imgWidth > 0) {
                if (imgWidth < 60) {
                    imgWidth = 60;
                }
            }

            // if this member has not voted, then hide vote members section
            if (!this.voted) {
                imgWidth = 0;
            }

            return imgWidth;
        },

        /**
         * Generate poll option left css
         */
        pollOptionLeftCss(poll) {
            let css = 'width: 100%;';
            let imgWidth = this.pollOptionRightWidth(poll);
            if (imgWidth > 0) {
                css = 'width: calc(100% - ' + imgWidth + 'px);';
            }

            return css;
        },

        /**
         * Generate poll option right css
         */
        pollOptionRightCss(poll) {
            let imgWidth = this.pollOptionRightWidth(poll);

            return 'width: ' + imgWidth + 'px;';
        },

        commentOwnerRole(comment) {
            return comment.member?.role || '';
        },

        commentOwnerGroups(comment) {
            let commentOwnerGroups = comment.member?.groups || [];
            commentOwnerGroups = commentOwnerGroups.filter(el => el.publish > 0);

            return commentOwnerGroups;
        },

        getPostCreatedInfo(post) {
            let getPostCreatedInfo = '';
            if (this.modalExtraData == PostType.REGULAR) {
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
    }
}
</script>

<style scoped>
.post-customer {
    display: flex;
    align-items: center;
}

.post-created {
    font-size: 13px;
    color: #909090;
    text-align: left;
}

.post-title {
    font-weight: bold;
    font-size: 24px;
    margin-top: 10px;
    text-align: left;
    word-break: break-word;
}

.post-content {
    margin-top: 10px;
    text-align: left;
    font-size: 16px !important;
    font-weight: normal !important;
    white-space: pre-wrap;
    min-height: 100px;
    word-break: break-word;
}

.post-content-footer {
    border-bottom: 1px solid #ddd;
    padding: 15px 0px;
    text-align: left;
    display: flex;
    align-items: center;
}

.post-likes-section {
    display: flex;
    align-items: center;
    border: 1px solid #E4E4E4;
    border-radius: 6px;
    margin-right: 25px;
}

.post-likes-label {
    padding: 8px 10px;
    border-right: 1px solid #E4E4E4;
    color: rgb(144, 144, 144);
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
}

.post-likes-label:hover {
    color: #4a4a4a;
}

.post-likes-label.disabled {
    pointer-events: none;
}

.post-likes-value {
    padding: 8px 10px;
    color: rgb(144, 144, 144);
    font-weight: bold;
}

.post-comments-count-section {
    display: flex;
    align-items: center;
    color: rgb(144, 144, 144);
    font-weight: bold;
}

.post-action-link {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgb(228, 228, 228);
    border-radius: 3px;
    background-color: #fff;
    cursor: pointer;
}

.post-action-link:hover {
    background-color: rgb(228, 228, 228);
}

.post-action-link .fa-ellipsis {
    color: rgb(144, 144, 144);
}

.post-action-link:hover .fa-ellipsis {
    color: rgb(32, 33, 36);
}

.comment-content {
    white-space: pre-wrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.post-header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0px;
    border-bottom: 1px solid #ddd;
}

.post-content-container {
    max-height: calc(100vh - 160px);
    padding: 10px;
    overflow: auto;
}

.post-content-container::-webkit-scrollbar {
    background-color: #f4f5f8;
    width: 5px;
    height: 5px;
}

.post-content-container::-webkit-scrollbar-thumb {
    background-color: #c0c0c0;
    border: 0.25em solid #c0c0c0;
    border-radius: 1em;
}

.poll-option-section {
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-left: 10px;
    width: 100%;
    display: flex;
    align-items: center;
    position: relative;
    cursor: pointer;
}

.poll-option-progress {
    position: absolute;
    height: 100%;
    background-color: #f7f8fc;
    border-radius: 5px;
}

.poll-option-left {
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
    padding: 7px 5px 7px 10px;
    text-align: left;
    height: 64px;
    display: flex;
    align-items: center;
    z-index: 1;
}

.poll-option-left.full-width {
    border-radius: 5px;
}

.poll-option-label {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: pre-wrap;
}

.poll-option-right {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
    padding: 3px 10px 3px 3px;
    text-align: right;
    z-index: 1;
}

.voted-count {
    margin-top: 15px;
    font-size: 14px;
    font-weight: bold;
    text-align: left;
    color: rgb(144, 144, 144);
}

.voted-avatar {
    margin-left: -12px;
    border: 2px solid #fff;
    width: 40px;
    height: 40px;
}

@media only screen and (max-width: 600px) {
    .post-created {
        font-size: 12px;
    }

    .post-title {
        font-size: 20px;
    }

    .post-content {
        font-size: 14px !important;
    }

    .post-content-footer {
        padding: 10px 0px;
        font-size: 14px;
    }

    .post-likes-section {
        margin-right: 15px;
    }

    .post-likes-label {
        padding: 3px 5px;
    }

    .post-likes-value {
        padding: 3px 5px;
    }

    .post-action-link {
        width: 30px;
        height: 30px;
    }

    .post-header-container {
        padding: 5px 0px;
    }

    .post-content-container {
        max-height: calc(100% - 50px);
    }

    .inner-modal-container {
        height: 100%;
    }

    .poll-option-left {
        padding: 5px 3px 5px 7px;
        height: 56px;
    }

    .poll-option-right {
        padding: 3px 7px 3px 3px;
    }

    .voted-count {
        margin-top: 10px;
        font-size: 13px;
    }

    .voted-avatar {
        margin-left: -8px;
        width: 30px;
        height: 30px;
    }
}
</style>
