<template>
    <div class="w100 mt1">
        <div class="flex align-items-start mt2">
            <img v-if="member" :src="getMemberGravatar(member)" class="customer-gravatar" />
            <img v-else :src="getUserGravatar(user)" class="customer-gravatar" />
            
            <div class="textarea-with-links-wrapper">
                <InputBoxWithMention 
                    :id="'add_' + owner + '_textarea'" 
                    action="add_comment"
                    :owner="owner"
                    :content="postComment" 
                    :placeholder="$t('community.community.post.your-comment')" 
                    styleClass="input textarea-with-links"
                />

                <div class="textarea-links">
                    <UploadFile filetype="attachment" :owner="owner" />
                    <a href="#" :title="$t('general.add-video')" class="control ml-05 tooltip" @click="showAddVideoModal">
                        <font-awesome-icon icon="fa-video" class="link-icon" />
                    </a>
                    <a href="#" :title="$t('general.add-link')" class="control ml-05 tooltip last-child" @click="showAddLinkModal">
                        <font-awesome-icon icon="fa-link" class="link-icon" />
                    </a>
                </div>
            </div>
        </div>

        <div class="community-media-grid-container mt1 padding-left-45px" v-if="medias.length > 0">
            <media-thumb
                v-for="media, i in medias"
                :key="media.id"
                :media="media"
                additionalClass="community-media community-media-grid-item"
                :viewMedia="true"
                :hoverMedia="true"
                :owner="owner"
            />
        </div>

        <div class="post-comment-add-action">
            <button
                v-if="!disabledConfirm"
                class="button"
                :class="btnClass"
                @click="closeComment"
            >
                {{ $t('common.cancel') }}
            </button>
            <button
                v-if="!disabledConfirm"
                class="button community-blue-btn ml-05"
                :class="btnClass"
                :disabled="disabledConfirm"
                @click="saveComment"
            >
                {{ $t('community.community.post.comment') }}
            </button>
        </div>
    </div>
</template>

<script>

import UploadFile from "../../../General/UploadFile.vue";
import InputBoxWithMention from "../../../General/Elements/InputBoxWithMention";
import MediaThumb from "../../../Media/MediaThumb.vue";

import getMemberGravatar from "../../../../mixins/util";
import getUserGravatar from "../../../../mixins/util";

import { MemberAccess } from '../../../../data/enums';

export default {
    name: "AddComment",
    props: ['owner'],
    mixins: [
        getMemberGravatar,
        getUserGravatar
    ],
    components: {
        MediaThumb,
        UploadFile,
        InputBoxWithMention
    },
    data () {
        return {
            MemberAccess,
            activeElement: null
        };
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
         * Returns user
         */
        user ()
        {
            return this.$store.state.auth.user;
        },

        /**
         * Returns community post comment
         */
        comment ()
        {
            return this.$store.state.post.comment;
        },

        /**
         * Returns reply comment
         */
        replyComment ()
        {
            return this.$store.state.post.replyComment;
        },

        /**
         * Returns save action disabled status
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.postComment !== '' && this.postComment !== null && this.auth) {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Return comment
         */
        postComment ()
        {
            let content = '';
            if (this.owner === 'comment') {
                content = this.comment.content;
            } else if (this.owner === 'reply') {
                content = this.replyComment.content;
            }

            if (content === '') {
                this.resetTextareaHeight();
            }

            return content;
        },

        /**
         * Return community post comment medias
         */
        medias ()
        {
            let medias = [];
            if (this.owner === 'comment') {
                medias = this.comment.medias || [];
            } else if (this.owner === 'reply') {
                medias = this.replyComment.medias || [];
            }

            return medias;
        },

        /**
         * Returns button class
         */
        btnClass ()
        {
            let btnClass = 'is-medium';
            if (this.owner === 'reply') {
                btnClass = 'is-small';
            }

            return btnClass;
        },
    },
    methods: {
        /**
         * Show Add Video modal
         */
        showAddVideoModal()
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                this.$store.commit('showChildModal', {
                  modalType: 'AddLink',
                  extraData: {
                    type: 'video',
                    owner: this.owner
                  }
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
         * Show Add Link modal
         */
        showAddLinkModal()
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                this.$store.commit('showChildModal', {
                  modalType: 'AddLink',
                  extraData: {
                    type: 'link',
                    owner: this.owner
                  }
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
         * Close comment
         */
        closeComment()
        {
            if (this.owner === 'comment') {
                this.$store.commit('resetCommunityPostComment');
            } else if (this.owner === 'reply') {
                this.$store.commit('resetReplyComment');
                this.$store.commit('closeReplySection');
            }

            this.resetTextareaHeight();
        },

        /**
         * Save comment
         */
        async saveComment()
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                if (this.owner === 'comment') {
                  this.comment.owner = this.owner;
                  await this.$store.dispatch('CREATE_POST_COMMENT', this.comment);

                  this.scrollToBottom();
                } else if (this.owner === 'reply') {
                  this.replyComment.owner = this.owner;
                  await this.$store.dispatch('CREATE_POST_COMMENT', this.replyComment);
                }

                this.resetTextareaHeight();
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
         * Scroll to bottom
         */
        scrollToBottom()
        {
            var objDiv = document.getElementById("post_content_container");
            objDiv.scrollTo({top: objDiv.scrollHeight, behavior: 'smooth'});
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

        resetTextareaHeight ()
        {
            let activeElement = document.getElementById('add_' + this.owner + '_textarea');
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
        }
    }
}
</script>

<style scoped>
    .padding-left-45px {
        padding-left: 45px;
    }
</style>
