<template>
    <div class="w100 mt1">
        <div class="flex align-items-start mt2">
            <img v-if="memberExist" :src="getMemberGravatar(member)" class="customer-gravatar" />
            <img v-else :src="getUserGravatar(user)" class="customer-gravatar" />
            
            <div class="textarea-with-links-wrapper">
                <InputBoxWithMention 
                    :id="'edit_comment_textarea_' + editedComment.id" 
                    action="edit_comment"
                    :content="editCommentContent" 
                    :placeholder="$t('community.community.post.your-comment')" 
                    :rows="editCommentTextareaRows" 
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
                {{ $t('common.save') }}
            </button>
        </div>
    </div>
</template>

<script>

import md5 from 'md5'
import UploadFile from "../../../General/UploadFile.vue";
import InputBoxWithMention from "../../../General/Elements/InputBoxWithMention";
import MediaThumb from "../../../Media/MediaThumb.vue";

import getMemberGravatar from "../../../../mixins/util";
import getUserGravatar from "../../../../mixins/util";

import updateEditContentForMention from "../../../../mixins/util";
import { MemberAccess } from '../../../../data/enums';

export default {
    name: "EditComment",
    props: ['id'],
    mixins: [
        getMemberGravatar,
        getUserGravatar,
        updateEditContentForMention
    ],
    components: {
        MediaThumb,
        UploadFile,
        InputBoxWithMention
    },
    data () {
        return {
            MemberAccess,
            activeElement: null,
            owner: 'edit-comment'
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
         * Returns edited comment
         */
        editedComment ()
        {
            return this.$store.state.post.editedComment;
        },

        /**
         * Returns save action disabled status
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.editCommentContent !== '' && this.editCommentContent !== null && this.auth) {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Return comment
         */
        postComment ()
        {
            return this.editedComment.content;
        },

        /**
         * Return comment content for edit
         */
        editCommentContent() {
            return this.updateEditContentForMention(this.postComment);
        },

        /**
         * Return edit comment textarea rows
         */
        editCommentTextareaRows ()
        {
            let editCommentTextareaRows = 1;
            if (this.editCommentContent) {
                let contentArray = this.editCommentContent.split('\n');
                if (contentArray.length > 1) {
                    editCommentTextareaRows = contentArray.length;
                }
            }

            return editCommentTextareaRows;
        },

        /**
         * Return community post comment medias
         */
        medias ()
        {
            return this.editedComment.medias || [];
        },

        /**
         * Returns button class
         */
        btnClass ()
        {
            return 'is-medium';
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
            this.$store.commit('revertEditedCommentShow', this.editedComment);
            this.$store.commit('resetEditedComment');

            this.resetTextareaHeight();
        },

        /**
         * Save comment
         */
        async saveComment()
        {
            if (this.auth) {
              if (this.access === MemberAccess.ALLOWED) {
                await this.$store.dispatch('UPDATE_POST_COMMENT', this.editedComment);
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
            if (this.activeElement) {
                this.activeElement.style.height = "40px";
                const parent = this.activeElement.parentNode;
                const links = parent.querySelector('.textarea-links');
                
                if (links) {
                    if (window.innerWidth <= 600) {
                        links.style.marginTop = "0px";
                    } else {
                        links.style.marginTop = "-40px";
                    }
                }

                this.activeElement = null;
            }
        }
    }
}
</script>

<style scoped>
    .padding-left-45px {
        padding-left: 45px;
    }

    .textarea-links {
        margin-top: 0px;
    }
</style>
