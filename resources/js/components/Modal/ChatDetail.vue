<template>
    <div class="inner-modal-container">
        <div class="chat-detail-header">
            <div class="flex align-items-center">

                <div class="image is-48x48 flex align-items-center">
                    <img v-if="chatOpponentUser && chatOpponentUser.photo" class="is-rounded" :src="getUserGravatar(chatOpponentUser)" alt="gravatar-profile-image">
                    <img v-else-if="chatOpponentUser && chatOpponentUser.email" class="is-rounded" :src="getUserGravatar(chatOpponentUser)" alt="gravatar-profile-image">
                    <img v-else class="is-rounded" :src="defaultAvatar" alt="gravatar-profile-image">
                </div>
                    
                <div class="ml-05">
                    <div class="chat-customer-name">
                        {{ getUserName(chatOpponentUser) }}
                    </div>
                    <div class="chat-customer-created">
                        {{ $t('community.members.active') }} {{ getDiffTimeFromNow(chatOpponentUser.created_at) }}
                    </div>
                </div>
            </div>

            <div class="flex align-items-center">
                <div v-if="modalSize === 'large'" class="chat-action-link chat-collapse-link" @click="collapse">
                    <font-awesome-icon icon="fa fa-down-left-and-up-right-to-center" class="link-icon" />
                </div>

                <div v-else class="chat-action-link chat-expand-link" @click="expand">
                    <font-awesome-icon icon="fa fa-up-right-and-down-left-from-center" class="link-icon" />
                </div>

                <div class="dropdown ml-05">
                    <div
                        class="dropdown-trigger chat-action-link"
                        @click="showChatActionDropdown"
                        v-click-outside="hideChatActionDropdown"
                    >
                        <font-awesome-icon icon="fa fa-ellipsis" class="font-20px link-icon" />
                    </div>
                    <div
                        id="chat_action_content"
                        class="dropdown-menu"
                        :class="displayChatActionDropdown ? 'show' : ''"
                    >
                        <div class="dropdown-content">
                            <div
                                class="tab-content-item-action-item"
                                @click.stop="viewProfile"
                            >
                                {{ $t('community.chats.view-profile') }}
                            </div>
                            <div
                                v-if="blocked"
                                class="tab-content-item-action-item"
                                @click.stop="unblockUser"
                            >
                                {{ $t('community.chats.unblock') }} {{ getUserName(chatOpponentUser) }}
                            </div>
                            <div
                                v-else
                                class="tab-content-item-action-item"
                                @click.stop="blockUser"
                            >
                                {{ $t('community.chats.block-user').replace("#user_name#", getUserName(chatOpponentUser)) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="chat-action-link ml-05" @click="close">
                    <font-awesome-icon icon="fa fa-times" class="link-icon" />
                </div>
            </div>
        </div>

        <div class="chat-detail-content" id="chat_detail_content">
            <div :class="modalSize === 'large' ? 'mt5em mb4' : 'mt4 mb3'">
                <div v-if="startCommunity" class="text-center">
                    <img v-if="startCommunity.logo" :src="startCommunity.logo" class="community-logo" />
                </div>
                <div v-else-if="firstMsgPosted" class="text-center">
                    <img v-if="community.logo" :src="community.logo" class="community-logo" />
                </div>

                <div class="flex align-items-center jc mt1">
                    <div class="image is-48x48 flex align-items-center">
                        <img v-if="member" class="is-rounded" :src="getMemberGravatar(member)" alt="gravatar-profile-image">
                        <img v-else class="is-rounded" :src="getUserGravatar(user)" alt="gravatar-profile-image">
                    </div>

                    <font-awesome-icon :icon="['fas', 'repeat']" class="grey ml1 mr1" />

                    <div class="image is-48x48 flex align-items-center">
                        <img v-if="chatOpponentUser && chatOpponentUser.photo" class="is-rounded" :src="getUserGravatar(chatOpponentUser)" alt="gravatar-profile-image">
                        <img v-else class="is-rounded" :src="getUserGravatar(chatOpponentUser)" alt="gravatar-profile-image">
                    </div>
                </div>

                <div v-if="startCommunity" class="mt1">
                    <div class="text-center">
                        {{ $t('community.chats.message-start').replace("#user_name#", getUserName(chatOpponentUser)) }}
                    </div>
                    <div class="text-center font-weight-600">
                        {{ startCommunity.name }}
                    </div>
                </div>

                <div v-else-if="firstMsgPosted" class="mt1">
                    <div class="text-center">
                        {{ $t('community.chats.message-start').replace("#user_name#", getUserName(chatOpponentUser)) }}
                    </div>
                    <div class="text-center font-weight-600">
                        {{ community.name }}
                    </div>
                </div>

                <div v-else class="mt1 text-center">
                    {{ $t('community.chats.break-ice') }}
                </div>
            </div>

            <div
                v-for="chatMessage in chatMessages"
                :key="chatMessage.id"
                class="chat-message-item"
            >
                <div class="chat-message-date">
                    {{ getMessageCreatedDate(chatMessage.created_at) }}
                </div>

                <div class="flex">
                    <div v-if="user.id === chatMessage.from_id" class="image is-48x48 flex align-items-center mr1">
                        <img v-if="chatMessage.from.member && chatMessage.from.member.photo" class="is-rounded" :src="getUserGravatar(chatMessage.from)" alt="gravatar-profile-image">
                        <img v-else class="is-rounded" :src="getUserGravatar(chatMessage.from)" alt="gravatar-profile-image">
                    </div>

                    <div class="chat-message-section">
                        <div class="flex align-items-center jcb">
                            <div class="chat-message-user-name">
                                {{ getChatUserName(chatMessage.from) }}
                            </div>
                            <div class="chat-message-created-time">
                                {{ getMessageCreatedTime(chatMessage.created_at) }}
                            </div>
                        </div>

                        <div class="chat-message-content" v-html="linkify(chatMessage.content)"></div>

                        <div class="community-media-grid-container mt1" v-if="chatMessage.medias.length > 0">
                            <media-thumb
                                v-for="media, i in chatMessage.medias"
                                :key="media.id"
                                :media="media"
                                additionalClass="community-media community-media-grid-item"
                                :viewMedia="true"
                                :hoverMedia="false"
                            />
                        </div>
                    </div>

                    <div v-if="user.id === chatMessage.to_id" class="image is-48x48 flex align-items-center ml1">
                        <img v-if="chatMessage.from.member && chatMessage.from.member.photo" class="is-rounded" :src="getUserGravatar(chatMessage.from)" alt="gravatar-profile-image">
                        <img v-else class="is-rounded" :src="getUserGravatar(chatMessage.from)" alt="gravatar-profile-image">
                    </div>
                </div>
            </div>
        </div>

        <div class="chat-detail-footer" id="chat_detail_footer">
            <div v-if="blocked">
                <div class="blocked-alert-msg">
                    {{ $t('community.chats.unblock-msg').replace("#user_name#", getUserName(chatOpponentUser)) }}
                </div>
                <div class="unblock-link" @click.stop="unblockUser">
                    {{ $t('community.chats.unblock') }}
                </div>
            </div>

            <div v-else class="textarea-with-links-wrapper p0">
                <div class="message-content-area">
                    <InputBoxWithMention 
                        id="add_chat_textarea" 
                        action="add_chat"
                        :owner="owner"
                        :content="content" 
                        :placeholder="$t('community.chats.message-to').replace('#user_name#', getUserName(chatOpponentUser))"
                        styleClass="input textarea-with-links"
                    />

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
                </div>

                <div class="textarea-links">
                    <div class="flex align-items-center">
                        <UploadFile filetype="image" :owner="owner" />
                        <a href="#" :title="$t('general.add-video')" class="control ml-05 tooltip" @click="showAddVideoModal">
                            <font-awesome-icon icon="fa-video" class="link-icon" />
                        </a>
                    </div>

                    <div class="flex align-items-center">
                        <button class="button is-medium mr-05" @click="closeChat">
                            {{ $t('common.cancel') }}
                        </button>
                        <button
                            :class="button"
                            @click="saveChat"
                            :disabled="disabledChatSave"
                        >{{ $t('common.send') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import moment from 'moment'
import getUserName from "../../mixins/util";
import getMemberGravatar from "../../mixins/util";
import getUserGravatar from "../../mixins/util";
import linkify from "../../mixins/util";
import InputBoxWithMention from "../General/Elements/InputBoxWithMention";
import UploadFile from "../General/UploadFile.vue";
import MediaThumb from "../Media/MediaThumb.vue";
import { MemberAccess } from '../../data/enums';

export default {
	name: 'ChatDetail',
    mixins: [
        getUserName,
        getMemberGravatar,
        getUserGravatar,
        linkify
    ],
    components: {
        UploadFile,
        MediaThumb,
        InputBoxWithMention
    },
    data () {
        return {
            MemberAccess,
            processing: false,
            owner: 'chat',
            firstMsgPosted: false,
            defaultAvatar: '/assets/img/default.png',
            displayChatActionDropdown: false
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
         * extra data of child modal
         */
        chatOpponentUser ()
        {
            return this.$store.state.communitycenter.chatOpponentUser;
        },

        /**
         * start chat
         */
        startChat ()
        {
            return this.chatOpponentUser.startChat;
        },

        /**
         * Return all communities
         */
        communities ()
        {
            return this.$store.state.member.communities;
        },

        /**
         * start community of this chat
         */
        startCommunity ()
        {
            let startCommunity = null;
            if (this.startChat) {
                let filteredCommunities = this.communities.filter(el => el.id === this.startChat.community_id);
                if (filteredCommunities.length === 1) {
                    startCommunity = filteredCommunities[0];
                }
            }

            return startCommunity;
        },

        /**
         * size of modal
         */
        modalSize ()
        {
            return this.$store.state.modal.size;
        },

        /**
         * Returns newChat
         */
        newChat ()
        {
            return this.$store.state.communitycenter.newChat;
        },

        /**
         * Returns chat content
         */
        content ()
        {
            return this.newChat.content;
        },

        /**
         * Return new chat medias
         */
        medias ()
        {
            return this.newChat.medias || [];
        },

        /**
         * Add ' is-loading' when processing
         */
        button ()
        {
            let button = 'button is-medium community-blue-btn ';

            return (this.processing)
                ? button + ' is-loading'
                : button;
        },

        /**
         * Return true to disable the Submit File button
         */
        disabledChatSave ()
        {
            return this.content === "";
        },

        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },

        /**
         * Returns chat messages of this user
         */
        chatMessages ()
        {
            return this.$store.state.communitycenter.chatMessages;
        },

        /**
         * Returns blocked user ids
         */
        blockedUserIds ()
        {
            return this.$store.state.communitycenter.blockedUserIds;
        },

        /**
         * Returns blocked status
         */
        blocked ()
        {
            return this.blockedUserIds.includes(this.chatOpponentUser.id);
        },
    },
	methods: {
        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow (date)
        {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        showChatActionDropdown() {
            this.displayChatActionDropdown = !this.displayChatActionDropdown;
        },

        hideChatActionDropdown() {
            this.displayChatActionDropdown = false;
        },

        viewProfile() {
            if (this.chatOpponentUser && this.chatOpponentUser.tag) {
                window.open(window.location.origin + '/view-profile/' + this.chatOpponentUser.tag, '_blank');
            }
        },

        async blockUser() {
            await this.$store.dispatch('BLOCK_CHAT_USER', {
                fromId: this.user.id,
                toId: this.chatOpponentUser.id
            });
        },

        async unblockUser() {
            await this.$store.dispatch('UNBLOCK_CHAT_USER', {
                fromId: this.user.id,
                toId: this.chatOpponentUser.id
            });
        },

        /**
         * Close the modal
         */
        close ()
        {
            this.content = '';
            this.$store.commit('hideModal');
        },

        /**
         * Close the modal
         */
        expand ()
        {
            this.$store.commit('setModalSize', 'large');
        },

        /**
         * Close the modal
         */
        collapse ()
        {
            this.$store.commit('setModalSize', null);
        },

        resetTextareaHeight ()
        {
            if (this.activeElement) {
                this.activeElement.style.height = "40px";
                this.activeElement = null;
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
         * Return message created date
         */
        getMessageCreatedDate(createdAt)
        {
            return moment(createdAt).locale(this.$i18n.locale).format("LL");
        },

        /**
         * Return message created time
         */
        getMessageCreatedTime(createdAt)
        {
            return moment(createdAt).locale(this.$i18n.locale).format("LT");
        },

        /**
         * Return user name
         */
        getChatUserName (user)
        {
            let name = '';
            if (typeof user.firstname !== 'undefined') {
                name = user.firstname;
                if (typeof user.lastname !== 'undefined') {
                    name += ' ' + user.lastname;
                }
            }

            return name;
        },

        /**
         * Reset chat
         */
        resetChat()
        {
          this.$store.commit('resetNewChat');
          this.resetTextareaHeight();
        },

        /**
         * Close chat
         */
        closeChat()
        {
          this.resetChat();
          this.$store.commit('hideModal');
        },

        /**
         * Scroll to bottom
         */
        scrollToBottom()
        {
            setTimeout(() => {
                var objContentDiv = document.getElementById("chat_detail_content");
                if (objContentDiv) {
                    objContentDiv.scrollTo({top: objContentDiv.scrollHeight, behavior: 'smooth'});
                }

                var objFooterDiv = document.getElementById("chat_detail_footer");
                if (objFooterDiv) {
                    objFooterDiv.scrollTo({top: objFooterDiv.scrollHeight, behavior: 'smooth'});
                }
            }, 300);
        },

        /**
         * Reset textarea height
         */
        resetTextareaHeight ()
        {
            if (document.getElementById('add_chat_textarea')) {
                document.getElementById('add_chat_textarea').style.height = "44px";
            }
        },

        /**
         * Save chat
         */
        async saveChat()
        {
            this.processing = true;
            await this.$store.dispatch('SAVE_NEW_CHAT_MESSAGE');
            this.resetChat();

            if (this.startCommunity === null) {
                this.firstMsgPosted = true;
            }

            this.scrollToBottom();
            this.resetTextareaHeight();

            this.processing = false;
        },
	}
}
</script>

<style scoped>

    .chat-detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0px;
    }

    .chat-detail-content {
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
        max-height: 350px;
        overflow: auto;
        padding: 12px 10px 20px;
    }

    .chat-detail-content::-webkit-scrollbar {
        background-color: #f4f5f8;
        width: 5px;
    }
    
    .chat-detail-content::-webkit-scrollbar-thumb {
        background-color: #c0c0c0;
        border: 0.25em solid #c0c0c0;
        border-radius: 1em;
    }

    .chat-customer-name {
        font-weight: 600;
        font-size: 16px;
        text-align: left;
      text-transform: capitalize;
    }

    .chat-customer-name:hover {
        text-decoration: underline;
    }

    .chat-customer-created {
        font-size: 13px;
        color: #909090;
        text-align: left;
    }

    .chat-action-link {
        border: none;
        background: transparent;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .chat-action-link .link-icon {
        opacity: 0.6;
    }

    .chat-action-link.active,
    .chat-action-link:hover {
        background: rgb(228, 228, 228);
    }

    .chat-action-link.active .link-icon,
    .chat-action-link:hover .link-icon {
        opacity: 1;
    }

    .chat-detail-footer {
        padding: 12px 5px;
        max-height: 300px;
        overflow: auto;
    }

    .textarea-with-links-wrapper {
        background: none;
        border: none;
    }

    .textarea-links {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 20px;
        margin-bottom: 10px;
    }

    .chat-message-item {
        padding: 15px 10px;
    }

    .chat-message-date {
        color: rgb(144, 144, 144);
        font-size: 14px;
    }

    .chat-message-section {
        width: 100%;
        border: 1px solid #E4E4E4;
        padding: 8px 13px;
        border-radius: 15px;
    }

    .chat-message-user-name {
        font-weight: 600;
    }

    .chat-message-created-time {
        font-size: 13px;
        color: rgb(144, 144, 144);
    }

    .chat-message-content {
        text-align: left;
        white-space: pre-wrap;
    }

    .blocked-alert-msg {
        text-align: left;
        color: rgb(231, 76, 60);
        padding: 15px;
    }

    .unblock-link {
        font-size: 16px;
        font-weight: bold;
        color: rgb(144, 144, 144);
        padding: 15px;
        border: 1px solid #E4E4E4;
        border-radius: 5px;
        cursor: pointer;
        margin-bottom: 15px;
    }

    .unblock-link:hover {
        color: rgb(32, 33, 36);
    }

    .chat-detail-footer::-webkit-scrollbar {
        background-color: #f4f5f8;
        width: 5px;
    }

    .chat-detail-footer::-webkit-scrollbar-thumb {
        background-color: #c0c0c0;
        border: 0.25em solid #c0c0c0;
        border-radius: 1em;
    }

    @media only screen and (max-width: 600px)
    {
        .inner-modal-container {
            height: 100%;
        }

        .chat-detail-header {
            padding: 6px 0px;
        }

        .chat-detail-content {
            padding: 6px 10px 15px;
            max-height: calc(100% - 250px);
        }

        .chat-customer-name {
            font-size: 14px;
        }

        .chat-customer-created {
            font-size: 12px;
        }

        .chat-detail-footer {
            padding: 6px 5px;
            max-height: 200px;
        }

        .chat-message-item {
            padding: 10px 7px;
        }

        .chat-message-date {
            font-size: 12px;
        }

        .chat-message-section {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 10px;
        }

        .chat-message-created-time {
            font-size: 12px;
        }

        .blocked-alert-msg {
            padding: 10px;
        }

        .unblock-link {
            font-size: 14px;
        }
    }
</style>
