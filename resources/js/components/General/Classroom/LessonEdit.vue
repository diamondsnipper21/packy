<template>
    <div class="box">
        <div class="flex">
            <!-- Lesson name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.title') }}
                </p>
                <input class="input" :placeholder="$t('community.community.title')" v-model="editedLesson.title" />
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.set') }}
                </p>
                <select v-model="editedLesson.set_id" class="input">
                    <option :value="null">{{ $t('community.community.filter-options.none') }}
                    </option>
                    <option v-for="item in classroomSets" :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.media-link') }}
                </p>
                <div class="flex align-items-center">
                    <input class="input mr-05" :placeholder="$t('community.community.create-modal.video-desc')"
                        v-model="editedLesson.media" @keypress="validateUrl($event)" />
                    <UploadFile filetype="video" :owner="owner" />
                </div>

                <p v-if="invalidMedia" class="text-left input-label invalid-error">
                    {{ $t('community.classroom.invalid-media-link') }}
                </p>
            </div>
        </div>

        <div class="flex mt2 mb8">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.description') }}
                </p>

                <WysiwygEditor :value="editedLesson.content" :mutator="'setCloneLessonContent'"
                    :placeholder="$t('community.community.description')" :selfobj="self">
                </WysiwygEditor>
            </div>
        </div>

        <div v-if="resources.length > 0" class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.resources') }}
                </p>

                <div v-for="item in resources" :key="item.id" class="flex align-items-center p-0-5">

                    <div v-if="item.type === 'video'" class="resource-icon-section mr-05">
                        <font-awesome-icon icon="fa fa-video" class="resource-icon" />
                    </div>

                    <div v-else-if="item.type === 'audio'" class="resource-icon-section mr-05">
                        <font-awesome-icon icon="fa fa-microphone" class="resource-icon" />
                    </div>

                    <div v-else-if="item.type === 'image'" class="resource-icon-section mr-05">
                        <font-awesome-icon icon="fa fa-image" class="resource-icon" />
                    </div>

                    <div v-else-if="item.type === 'pdf'" class="resource-icon-section mr-05">
                        <font-awesome-icon icon="fa fa-file" class="resource-icon" />
                    </div>

                    <div v-else-if="item.type === 'link'" class="resource-icon-section mr-05">
                        <font-awesome-icon icon="fa fa-link" class="resource-icon" />
                    </div>

                    <div class="tab-content-sub-title font-weight-500 mr1">
                        {{ item.label }}
                    </div>

                    <div class="dropdown">
                        <div
                            :id="'resource_action_trigger_' + item.id"
                            class="dropdown-trigger resource-action-trigger"
                            @click.stop="showResourceActionDropdown(item.id)"
                            v-click-outside="hideResourceActionDropdown"
                        >
                            <font-awesome-icon icon="fa fa-ellipsis" class="font-20px" />
                        </div>
                        <div
                            :id="'resource_action_content_' + item.id"
                            class="dropdown-menu resource-action-content left-align"
                            :class="displayResourceActionDropdown === item.id ? 'show' : ''"
                        >
                            <div class="dropdown-content">
                                <div class="tab-content-item-action-item font-weight-300"
                                    @click.stop="editResource(item)">
                                    <font-awesome-icon icon="fa fa-pencil" /> &nbsp;
                                    {{ $t('community.classroom.edit-resource') }}
                                </div>
                                <div class="tab-content-item-action-item font-weight-300"
                                    @click.stop="deleteResource(item)">
                                    <font-awesome-icon icon="fa fa-trash" /> &nbsp;
                                    {{ $t('community.classroom.delete-resource') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex mt1 mb2">
            <button class="button is-medium community-btn mr-05" @click="addLinkToLesson">
                {{ $t('community.classroom.add-link') }}
            </button>

            <button class="button is-medium community-btn" @click="addFileToLesson">
                {{ $t('community.classroom.add-file') }}
            </button>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.action-items') }}
                </p>
                <textarea class="textarea" :placeholder="$t('community.classroom.action-items')"
                    v-model="editedLesson.action_items" rows="3" />
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.transcript') }}
                </p>
                <textarea class="textarea" :placeholder="$t('community.classroom.transcript')"
                    v-model="editedLesson.transcript" rows="3" />
            </div>
        </div>

        <div class="flex align-items-center mt2">
            <div v-if="editedLesson.discuss" class="font-14px mr-05">
                {{ $t('community.classroom.discussion-on') }}
            </div>
            <div v-else class="font-14px mr-05">
                {{ $t('community.classroom.discussion-off') }}
            </div>

            <Toggle v-model="editedLesson.discuss" :true-value="1" :false-value="0" />
        </div>

        <div class="mt2">
            <div class="flex">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.access-lesson') }}
                    </p>

                    <select class="input tab-content-select" v-model="editedLesson.access_type">
                        <option v-for="accessTypeOpt in accessTypeOpts" :value="accessTypeOpt"
                            :selected="editedLesson.access_type === accessTypeOpt">
                            {{ $t(`community.classroom.access-type-options.${accessTypeOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="editedLesson.access_type === 'only_member'" class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.member-access') }}
                    </p>
                    <MemberMultiSelect v-model="lessonAccessValue" :init-options="editedLesson.access_value_items" />
                </div>
            </div>

            <div class="flex-1 mt2">
                <p class="text-left input-label">
                    {{ $t('community.classroom.level-access') }}
                </p>

                <select class="input tab-content-select" v-model="editedLesson.level">
                    <option value="" disabled>{{ $t('community.classroom.level-access') }}
                    </option>
                    <option v-for="accessLevelValueOpt in accessLevelValueOpts" :value="accessLevelValueOpt"
                        :selected="editedLesson.level === accessLevelValueOpt">
                        {{
                            $t(`community.classroom.access-level-value-options.${accessLevelValueOpt}`)
                        }}
                    </option>
                </select>
            </div>
        </div>

        <div class="flex align-items-center jcb mt2 mb2">
            <div class="flex align-items-center">
                <div v-if="editedLesson.publish" class="font-14px mr-05">
                    {{ $t('community.classroom.published') }}
                </div>
                <div v-else class="font-14px mr-05">
                    {{ $t('community.classroom.draft') }}
                </div>

                <Toggle v-model="editedLesson.publish" :true-value="1" :false-value="0" />
            </div>

            <div class="flex align-items-center">
                <button class="is-medium community-blue-btn mr-05" :class="saveLessonBtn"
                    :disabled="lessonConfirmDisabled" @click="saveLesson">
                    {{ $t('common.save') }}
                </button>

                <button class="button is-medium community-btn" @click="cancelSaveLesson">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

        <div class="lesson-footer" v-if="posts.length > 0">
            <PostElement v-for="post in posts" :key="post.id" :post="post" :postType="PostType.REGULAR" :parentId="editedLesson.id" />
        </div>
    </div>
</template>

<script>
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'
import validateUrl from "../../../mixins/util";
import getVideoIdFromUrl from "../../../mixins/util";
import UploadFile from "../UploadFile.vue";
import WysiwygEditor from "../WysiwygEditor.vue";
import MemberMultiSelect from "../MemberMultiSelect";
import PostElement from '../Posts/PostElement';
import { PostType } from '../../../data/enums';

export default {
    name: 'LessonEdit',
    mixins: [validateUrl, getVideoIdFromUrl],
    components: {
        Loading,
        Toggle,
        MemberMultiSelect,
        WysiwygEditor,
        UploadFile,
        PostElement
    },
    data() {
        return {
            self: null,
            transcriptExpanded: false,
            owner: 'lesson',
            invalidMedia: false,
            accessTypeOpts: ['all', 'only_member'],
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            uploadingText: this.$t('common.uploading'),
            saveLessonProcessing: false,
            PostType,
            displayResourceActionDropdown: false
        }
    },
    created() {
        this.self = this;
    },
    computed: {
        auth() {
            return this.$store.state.auth.loggedIn;
        },

        member() {
            return this.$store.state.member.data;
        },

        memberExist() {
            return this.member?.id ? true : false;
        },

        community() {
            return this.$store.state.community.data;
        },

        editedLesson() {
            return this.$store.state.classroom.cloneLesson || {};
        },

        classroomSets() {
            return (this.$store.state.classroom.data?.items || []).filter(item => item.type == 'set');
        },

        resources() {
            return this.editedLesson.resources || [];
        },

        /**
         * Return confirm button disable status
         */
        lessonConfirmDisabled() {
            return this.editedLesson.title !== '' ? false : true;
        },

        posts() {
            let posts = [];
            if (typeof this.editedLesson.posts !== 'undefined' && this.editedLesson.posts.length > 0) {
                posts = this.editedLesson.posts;
            }

            return posts;
        },

        /**
         * Save lesson button class
         */
        saveLessonBtn() {
            let button = 'button ';

            return (this.saveLessonProcessing)
                ? button + ' is-loading'
                : button;
        },

        /**
         * Get | Set lesson access value
         */
        lessonAccessValue: {
            get() {
                return (this.editedLesson.access_value_items || []);
            },
            set(v) {
                let memberIds = [];
                if (Array.isArray(v)) {
                    v.map(member => {
                        memberIds.push(member.id);
                    });
                } else if (typeof v === 'object') {
                    memberIds.push(v.id);
                }

                this.editedLesson.access_value = memberIds.join(",")
                this.editedLesson.access_value_items = v
            }
        }
    },
    methods: {
        collapseTranscript() {
            this.transcriptExpanded = false;
        },

        expandTranscript() {
            this.transcriptExpanded = true;
        },

        showResourceActionDropdown(itemId) {
            if (this.displayResourceActionDropdown) {
                if (this.displayResourceActionDropdown !== itemId) {
                    this.displayResourceActionDropdown = itemId;
                } else {
                    this.displayResourceActionDropdown = false;
                }
            } else {
                this.displayResourceActionDropdown = itemId;
            }
        },

        hideResourceActionDropdown() {
            this.displayResourceActionDropdown = false;
        },

        editResource(item) {
            this.$store.commit('setLessonFile', item);
            this.$store.commit('showModal', {
                type: 'AddEditLessonResource',
                extraData: {
                    action: 'edit',
                    param: item
                },
                transparent: true
            });

            this.hideResourceActionDropdown();
        },

        deleteResource(item) {
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.classroom.confirm-delete-lesson-resource.title'),
                    desc: this.$t('community.classroom.confirm-delete-lesson-resource.desc'),
                    action: 'DELETE_LESSON_RESOURCE',
                    param: item,
                    hideModal: false
                }
            });

            this.hideResourceActionDropdown();
        },
        /**
         * Add lesson link
         */
        addLinkToLesson() {
            let resourceId = 'resource_' + (this.resources.length + 1);
            let lessonLink = {
                id: resourceId,
                lesson_id: this.editedLesson.id,
                type: 'link',
                label: '',
                url: ''
            };

            this.$store.commit('showModal', {
                type: 'AddEditLessonResource',
                extraData: {
                    action: 'add',
                    param: lessonLink
                },
                transparent: true
            });
        },

        /**
         * Add lesson file
         */
        addFileToLesson() {
            let resourceId = 'resource_' + (this.resources.length + 1);
            let lessonFile = {
                id: resourceId,
                lesson_id: this.editedLesson.id,
                type: 'image',
                label: '',
                url: ''
            };

            this.$store.commit('setLessonFile', lessonFile);
            this.$store.commit('showModal', {
                type: 'AddEditLessonResource',
                extraData: {
                    action: 'add',
                    param: lessonFile
                },
                transparent: true
            });
        },

        async saveLesson() {
            let videoId = this.getVideoIdFromUrl(this.editedLesson.media);

            if (videoId === null) {
                this.invalidMedia = true;
            }
            else {
                this.invalidMedia = false;
                this.editedLesson.media = videoId
            }

            if (!this.invalidMedia) {
                this.saveLessonProcessing = true;
                if (this.editedLesson.id) {
                    await this.$store.dispatch('UPDATE_CLASSROOM_LESSON', this.editedLesson);
                } else {
                    await this.$store.dispatch('CREATE_CLASSROOM_LESSON', this.editedLesson);
                }
                this.saveLessonProcessing = false;

                this.$store.commit('setLessonEdit', false);
            }
        },

        /**
         * Cancel lesson saving
         */
        async cancelSaveLesson() {
            await this.$store.dispatch('GET_CLASSROOM_LESSON', {
                set_id: this.editedLesson.set_id || 0,
                id: this.editedLesson.id
            });

            this.$store.commit('setLessonEdit', false);

            setTimeout(() => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth',
                });
            }, 500);
        },

    }
}
</script>
<style lang="scss" scoped>
$color_1: rgb(144, 144, 144);
$color_2: rgb(0, 158, 93);
$color_3: #fff;
$background-color_1: rgb(228, 228, 228);

.lesson-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px;
}

.lesson-mark {
    border-radius: 50%;
    width: 35px;
    height: 36px;
    padding: 6px;

    &:hover {
        background-color: $background-color_1;
    }
}

.completed-status-icon {
    font-size: 24px;
    cursor: pointer;
    color: $color_1;
}

.completed-status-icon.completed {
    color: $color_2;
}

.lesson-item-section {
    &:hover {
        .completed-status-icon.completed {
            color: $color_3;
        }
    }
}

.lesson-item-section.active {
    .completed-status-icon.completed {
        color: $color_3;
    }
}

.lesson-media-container {
    margin-top: 10px;
    border: 1px solid rgb(228, 228, 228);
    display: block;
    cursor: pointer;
    border-radius: 15px;
    min-height: 280px;

    iframe {
        width: 100%;
        height: 100%;
        border-radius: 15px;
        display: block;
    }

    video {
        width: 100%;
        height: 100%;
        border-radius: 15px;
        display: block;
    }
}

.empty-lesson-box {
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lesson-body {
    padding: 0px 10px;
}

.lesson-content {
    white-space: pre-wrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.community-external-link {
    &:hover {
        text-decoration: underline;
    }
}

.ellipsis-content {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: pre-wrap;
}

.full-content {
    white-space: pre-wrap;
}

.resource-label {
    max-width: calc(100% - 40px);
}

.classroom-edit-content {
    top: 27px;
}

.set-edit-content {
    top: 27px;
}

.lesson-edit-content {
    top: 27px;
}

.resource-action-content {
    top: 27px;
}

.lesson-footer {
    padding: 10px;
}

@media only screen and (max-width: 600px) {
    .lesson-media-container {
        margin-top: 5px;
        min-height: 140px;
    }
}
</style>
