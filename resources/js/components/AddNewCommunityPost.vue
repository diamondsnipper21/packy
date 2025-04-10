<template>
    <div class="box p0">
        <div class="add-community-post-section">
            <div class="flex align-items-center">
                <div class="flex align-items-center">
                    <img v-if="member" class="customer-avatar-photo size-34px" :src="getMemberGravatar(member)" alt="gravatar-profile-image">
                </div>
                <div>
                    <span v-if="member" class="font-weight-600 mr-05">{{ getMemberName(member) }}</span>
                    <span>{{ $t('community.community.add-post.header') }}</span>
                    <span class="font-weight-600 ml-05">{{ community.name }}</span>
                </div>
            </div>

            <div class="flex mt1">
                <!-- Title -->
                <div class="flex-1">
                    <p class="text-left input-label">{{ $t('community.community.title') }}</p>
                    <input class="input" required :placeholder="$t('community.community.title-placeholder')"
                        v-model="title" autofocus />
                </div>
            </div>

            <div class="flex mt1">
                <!-- Description -->
                <div class="flex-1">
                    <p class="text-left input-label">{{ $t('community.community.description') }}</p>

                    <InputBoxWithMention 
                        id="add_post_textarea" 
                        action="add_post"
                        owner="post"
                        :content="content" 
                        :placeholder="$t('community.community.description')" 
                        :rows="rows" 
                    />
                </div>
            </div>

            <div v-if="addPollShow" class="mt1 add-poll-section">
                <div class="flex align-items-center jcb mb1">
                    <div class="font-weight-600">
                        {{ $t('community.poll.poll') }}
                    </div>
                    <div class="remove-poll-link" @click="hideAddPoll">
                        {{ $t('common.remove') }}
                    </div>
                </div>

                <div v-for="poll in polls" class="poll-option">
                    <input class="input poll-option-input"
                        :placeholder="$t('community.poll.option') + ' ' + poll.tmp_id" v-model="poll.content" />
                    <a href="#" class="control ml-05" @click="removePollOption(poll.tmp_id)">
                        <font-awesome-icon icon="fa fa-times" class="link-icon" />
                    </a>
                </div>

                <div class="mt1 flex align-items-center">
                    <button class="button is-medium community-btn add-option-btn mr-05" @click="addNewPollOption">
                        <font-awesome-icon icon="fa fa-plus" /> &nbsp; {{ $t('community.poll.add-option') }}
                    </button>

                    <div class="dropdown">
                        <div
                            class="dropdown-trigger"
                            @click.stop="togglePollOptionSetting"
                            v-click-outside="hidePollOptionSetting"
                        >
                            <button class="button is-medium community-btn poll-option-setting-btn">
                                <font-awesome-icon icon="fa fa-gear" class="poll-option-setting-gear mr-05" />
                                <font-awesome-icon icon="fa fa-caret-up" class="poll-option-setting-arrow"
                                    :class="pollOptionSettingMenuExpanded ? 'expanded' : 'collapsed'" />
                            </button>
                        </div>

                        <div
                            id="poll_setting_menu"
                            class="dropdown-menu poll-setting-menu"
                            :class="pollOptionSettingMenuExpanded ? 'show' : ''"
                        >
                            <div class="dropdown-content">
                                <div class="dropdown-item font-weight-500">
                                    <label for="allow-multiple-answers" class="pointer allow-multiple-answers-label"
                                        @click="checkPollMultiAnswersAllow">
                                        {{ $t('community.poll.allow-multiple-answers') }}
                                    </label>
                                    <input type="checkbox" v-model="postPollMultiAnswerAllowChecked" class="ml1" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="community-media-grid-container mt1" v-if="medias.length > 0">
                <media-thumb v-for="media, i in medias" :key="media.id" :media="media"
                    additionalClass="community-media community-media-grid-item" :viewMedia="true" :hoverMedia="true"
                    owner="post" />
            </div>

            <div class="flex align-items-center mt2">
                <select v-model="categoryId" class="input">
                    <option value="0" disabled>{{ $t('community.community.category-placeholder') }}</option>
                    <option v-for="categoryItem in categories" :value="categoryItem.id">
                        {{ categoryItem.title }}
                    </option>
                </select>

                <div class="flex align-items-center">
                    <UploadFile filetype="attachment" :owner="owner" />
                    <a href="#" :title="$t('general.add-video')" class="control ml-05 tooltip"
                        @click="showAddVideoModal">
                        <font-awesome-icon icon="fa-video" class="link-icon" />
                    </a>
                    <a href="#" :title="$t('general.add-link')" class="control ml-05 tooltip" @click="showAddLinkModal">
                        <font-awesome-icon icon="fa-link" class="link-icon" />
                    </a>
                    <a href="#" :title="pollLinkTitle" class="control ml-05 tooltip last-child" @click="showHideAddPoll">
                        <font-awesome-icon icon="fa-square-poll-vertical" class="link-icon" />
                    </a>
                </div>
            </div>

            <div class="mt2 text-right">
                <button class="button is-medium" @click="closeAddPost">
                    {{ $t('common.cancel') }}
                </button>
                <button class="button is-medium community-blue-btn ml-05" :class="button" @click="addCommunityPost"
                    :disabled="disabledConfirm">{{ $t('community.community.add-post.post-now') }}</button>
                <button v-if="isManager(role)" class="button is-medium ml-05" @click="showSchedulePostModal"
                    :disabled="disabledConfirm">
                    <font-awesome-icon icon="fa-calendar-days" class="link-icon" />
                </button>
            </div>

            <div v-if="isManager(role) && leftTimestampForNextSent === 0" class="mt2 flex align-items-center je">
                <font-awesome-icon :icon="['fas', 'circle-exclamation']" class="mr-05" />

                <div class="font-14px mr-05">
                    {{ $t('community.community.send-email-to-members') }}
                </div>

                <Toggle v-model="broadcast" class="toggle-blue" />
            </div>

            <div v-if="isManager(role) && leftTimestampForNextSent > 0 && leftTimeString !== ''"
                class="mt1 flex align-items-center je">
                {{ $t('community.community.left-time-desc').replace("#left_time_string#", leftTimeString) }}
            </div>
        </div>
    </div>
</template>

<script>

import MediaThumb from "./Media/MediaThumb.vue";

import getMemberName from "../mixins/util";
import getMemberGravatar from "../mixins/util";
import isManager from "../mixins/util";

import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'

import moment from 'moment'
import slugify from '@sindresorhus/slugify'
import UploadFile from "./General/UploadFile.vue";

import InputBoxWithMention from "./General/Elements/InputBoxWithMention";

export default {
    name: 'AddNewCommunityPost',
    mixins: [
        getMemberName,
        getMemberGravatar,
        isManager
    ],
    components: {
        MediaThumb,
        Toggle,
        UploadFile,
        InputBoxWithMention
    },
    data() {
        return {
            processing: false,
            disabledConfirm: true,
            leftTimestampForNextSent: 0,
            leftTimeString: '',
            owner: 'post',
            addPollShow: false,
            pollOptionSettingMenuExpanded: false,
            rows: 5
        };
    },
    async created() {
        // set current time with flicking effect
        let self = this;
        self.getLeftTime();
        let intervalId = window.setInterval(function () {
            self.getLeftTime();
        }, 1000);
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
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
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
         * Return online coach community post
         */
        communityPost() {
            return this.$store.state.post.data;
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

                this.$store.commit('setCommunityPostProperty', {
                    key: 'path',
                    v: slugify(v)
                });

                this.disabledConfirm = !(v);
            }
        },

        /**
         * Return post content
         */
        content() {
            return this.communityPost.content;
        },

        /**
         * Get | Set Category Id
         */
        categoryId: {
            get() {
                return this.communityPost.category_id;
            },
            set(v) {
                this.$store.commit('setCommunityPostProperty', {
                    key: 'category_id',
                    v
                });
            }
        },

        /**
         * Return post poll multiple answers allow checked
         */
        postPollMultiAnswerAllowChecked: {
            get() {
                return (this.communityPost.allowMultipleAnswersChecked === 1 || this.communityPost.allowMultipleAnswersChecked === true);
            },
            set(v) {
                this.communityPost.allowMultipleAnswersChecked = !this.communityPost.allowMultipleAnswersChecked;
            }
        },

        /**
         * Return community post medias
         */
        medias() {
            return this.communityPost.medias || [];
        },

        /**
         * Return community post polls
         */
        polls() {
            return this.communityPost.polls;
        },

        /**
         * Get | Set broadcast
         */
        broadcast: {
            get() {
                return this.$store.state.community.broadcast;
            },
            set(v) {
                this.$store.commit('setCommunityBroadcast', v);

                if (v) {
                    this.$store.commit('setLastSentNotification', moment().format("YYYY-MM-DD HH:mm:ss"));
                } else {
                    this.$store.commit('setLastSentNotification', null);
                }
            }
        },

        /**
         * Get last_sent_notification
         */
        lastSentNotification() {
            return this.$store.state.community.lastSentNotification;
        },

        /**
         * Get community's last_sent_notification
         */
        communityLastSentNotification() {
            return this.community.last_sent_notification;
        },

        /**
         * Returns button status class
         */
        button() {
            return this.processing ? ' is-loading' : '';
        },

        /**
         * Returns poll link title
         */
        pollLinkTitle() {
            return this.addPollShow ? this.$t('community.poll.remove-poll') : this.$t('general.add-poll');
        },
    },
    methods: {
        show() {
            this.processing = false;
        },

        /**
         * Show Add Image modal
         */
        showAddImageModal() {
            this.$store.commit('showChildModal', {
                modalType: 'AddLink',
                extraData: {
                    type: 'image',
                    owner: 'post'
                }
            });
        },

        /**
         * Show Add Video modal
         */
        showAddVideoModal() {
            this.$store.commit('showChildModal', {
                modalType: 'AddLink',
                extraData: {
                    type: 'video',
                    owner: 'post'
                }
            });
        },

        /**
         * Show Add Link modal
         */
        showAddLinkModal() {
            this.$store.commit('showChildModal', {
                modalType: 'AddLink',
                extraData: {
                    type: 'link',
                    owner: 'post'
                }
            });
        },

        /**
         * Open new post section
         */
        closeAddPost() {
            this.emitter.emit("closeMentionMenu", {});
            this.$store.commit('resetCommunityPost');
            this.$store.commit('setAddPostShow', false);

            this.$store.commit('setLastSentNotification', this.community.last_sent_notification);
        },

        /**
         * Removes post media
         */
        removeCommunityPostMedia(item) {
            this.$store.commit('removeCommunityPostMedia', item);
        },

        /**
         * Add community post
         */
        async addCommunityPost() {
            this.processing = true;

            await this.$store.dispatch('CREATE_COMMUNITY_POST', {
                sendEmail: this.broadcast
            });

            this.$store.commit('setAddPostShow', false);
            this.$store.commit('setCommunityDataProp', { key: 'number_of_posts', value: 1 });
            this.$store.commit('setMentionedMembers', []);

            this.processing = false;
        },

        /**
         * Get left time
         */
        getLeftTime() {
            let diffTimeFromLastSent = 0;
            if (this.communityLastSentNotification) {
                let now = moment();
                let notificationTS = moment(this.communityLastSentNotification);
                diffTimeFromLastSent = now.diff(notificationTS, 'seconds');
            }

            this.leftTimestampForNextSent = 0;
            if (diffTimeFromLastSent > 0) {
                this.leftTimestampForNextSent = 3600 * 24 - diffTimeFromLastSent;
                if (this.leftTimestampForNextSent < 0) {
                    this.leftTimestampForNextSent = 0;
                }
            }

            this.leftTimeString = '';
            if (this.leftTimestampForNextSent > 0) {
                this.leftTimeString = moment.utc(this.leftTimestampForNextSent * 1000).format('HH:mm:ss');
            }
        },

        /**
         * Reset polls
         */
        resetPolls() {
            let polls = [];
            for (let i = 1; i <= 3; i++) {
                polls.push({
                    id: 0,
                    tmp_id: i,
                    owner: 'post',
                    owner_id: this.communityPost.id,
                    content: '',
                    voted: ''
                });
            }

            this.$store.commit('setCommunityPostProperty', {
                key: 'polls',
                v: polls
            });
        },

        /**
         * Hide add poll section
         */
        hideAddPoll() {
            this.$store.commit('setCommunityPostProperty', {
                key: 'polls',
                v: []
            });

            this.addPollShow = false;
        },

        /**
         * Show | Hide add poll section
         */
        showHideAddPoll() {
            if (this.addPollShow) {
                this.hideAddPoll();
            } else {
                this.resetPolls();
                this.addPollShow = true;
            }
        },

        /**
         * Remove poll option
         */
        removePollOption(tmp_id) {
            let polls = JSON.parse(JSON.stringify(this.polls));
            polls = polls.filter(el => el.tmp_id !== tmp_id);
            this.$store.commit('setCommunityPostProperty', {
                key: 'polls',
                v: polls
            });
        },

        /**
         * Add new poll option
         */
        addNewPollOption() {
            let polls = JSON.parse(JSON.stringify(this.polls));
            let lastPollId = 0;
            polls.map(poll => {
                if (poll.tmp_id > lastPollId) {
                    lastPollId = poll.tmp_id;
                }
            });

            polls.push({
                id: 0,
                tmp_id: lastPollId + 1,
                owner: 'post',
                owner_id: this.communityPost.id,
                content: '',
                voted: ''
            });

            this.$store.commit('setCommunityPostProperty', {
                key: 'polls',
                v: polls
            });
        },

        /**
         * Toggle poll option setting
         */
        togglePollOptionSetting() {
            this.pollOptionSettingMenuExpanded = !this.pollOptionSettingMenuExpanded;
        },

        hidePollOptionSetting() {
            this.pollOptionSettingMenuExpanded = false;
        },

        /**
         * Toggle for allowing multiple answers
         */
        checkPollMultiAnswersAllow() {
            this.communityPost.allowMultipleAnswersChecked = !this.communityPost.allowMultipleAnswersChecked;
        },

        /**
         * Show schedule post modal
         */
        showSchedulePostModal() {
            this.$store.commit('showModal', {
                type: 'SchedulePost',
                transparent: true
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
    }
}
</script>

<style scoped>
.add-community-post-section {
    padding: 20px;
}

.poll-option-setting-arrow,
.poll-option-setting-gear {
    color: #979696;
    font-size: 18px;
}

.poll-option-setting-arrow {
    -webkit-transition: .2s;
    transition: .2s;
    font-size: 18px;
}

.poll-option-setting-btn:hover .poll-option-setting-arrow,
.poll-option-setting-btn:hover .poll-option-setting-gear {
    color: #4a4a4a;
}

.poll-option-setting-arrow.collapsed {
    -webkit-transform: rotate(180deg);
    transform: rotate(180deg);
}

#poll_setting_menu {
    top: 100%;
}

@media only screen and (max-width: 600px) {
    .add-community-post-section {
        padding: 10px;
    }
}
</style>
