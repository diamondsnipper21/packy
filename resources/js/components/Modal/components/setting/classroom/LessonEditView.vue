<template>
    <div>
        <div class="flex align-items-center jcb">
            <div v-if="classroomShow === ClassroomViewType.ADD_LESSON" class="tab-content-title">
                {{ $t('community.classroom.add-lesson') }}
            </div>
            <div v-else-if="classroomShow === ClassroomViewType.EDIT_LESSON" class="tab-content-title">
                {{ $t('community.classroom.edit-lesson') }}
            </div>

            <div class="flex align-items-center">
                <div v-if="lesson.publish" class="font-14px mr-05">
                    {{ $t('community.classroom.published') }}
                </div>
                <div v-else class="font-14px mr-05">
                    {{ $t('community.classroom.draft') }}
                </div>

                <Toggle v-model="lesson.publish" :true-value="1" :false-value="0" />
            </div>
        </div>

        <div class="flex mt2">
            <!-- Lesson name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.title') }}
                </p>
                <input class="input" :placeholder="$t('community.community.title')" v-model="lesson.title" />
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.set') }}
                </p>
                <select v-model="lesson.set_id" class="input">
                    <option :value="null">{{ $t('community.community.filter-options.none') }}</option>
                    <option v-for="item in classroom.sets" :value="item.id">
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
                        v-model="lesson.media" @keypress="validateUrl($event)" />
                    <UploadFile filetype="video" :owner="owner" />
                </div>

                <p v-if="invalidMedia" class="text-left input-label invalid-error">
                    {{ $t('community.classroom.invalid-media-link') }}
                </p>
            </div>
        </div>

        <div class="flex mt2 mb6">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.community.description') }}
                </p>

                <WysiwygEditor :value="lesson.content" :mutator="'setCloneLessonContent'"
                    :placeholder="$t('community.community.description')" :selfobj="self"></WysiwygEditor>
            </div>
        </div>

        <div v-if="lesson.resources && lesson.resources.length > 0" class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.resources') }}
                </p>

                <div v-for="item, i in lesson.resources" :key="item.id" class="flex align-items-center p-0-5">

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
                            @click="showResourceActionDropdown(item.id)"
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
                    v-model="lesson.action_items" rows="3" />
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.transcript') }}
                </p>
                <textarea class="textarea" :placeholder="$t('community.classroom.transcript')"
                    v-model="lesson.transcript" rows="3" />
            </div>
        </div>

        <div class="flex align-items-center mt2">
            <div v-if="lesson.discuss" class="font-14px mr-05">
                {{ $t('community.classroom.discussion-on') }}
            </div>
            <div v-else class="font-14px mr-05">
                {{ $t('community.classroom.discussion-off') }}
            </div>

            <Toggle v-model="lesson.discuss" :true-value="1" :false-value="0" />
        </div>

        <div class="mt2">
            <div class="flex">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.access-lesson') }}
                    </p>

                    <select class="input tab-content-select" v-model="lesson.access_type">
                        <option v-for="accessTypeOpt in accessTypeOpts" :value="accessTypeOpt"
                            :selected="lesson.access_type === accessTypeOpt">
                            {{ $t(`community.classroom.access-type-options.${accessTypeOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="lesson.access_type === 'only_member'" class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.member-access') }}
                    </p>

                    <MemberMultiSelect v-model="lessonAccessValue" :init-options="lesson.access_value_items" />
                </div>
            </div>

            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.level-access') }}
                    </p>

                    <select class="input tab-content-select" v-model="lesson.level">
                        <option value="" disabled>{{ $t('community.classroom.level-access') }}</option>
                        <option v-for="accessLevelValueOpt in accessLevelValueOpts" :value="accessLevelValueOpt"
                            :selected="lesson.level === accessLevelValueOpt">
                            {{ $t(`community.classroom.access-level-value-options.${accessLevelValueOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex mt2 mb2">
            <button v-if="classroomShow === ClassroomViewType.ADD_LESSON" class="is-medium community-blue-btn mr-05"
                :class="saveLessonBtn" :disabled="lessonConfirmDisabled" @click="saveLesson">
                {{ $t('common.add') }}
            </button>

            <button v-else-if="classroomShow === ClassroomViewType.EDIT_LESSON"
                class="is-medium community-blue-btn mr-05" :class="saveLessonBtn" :disabled="lessonConfirmDisabled"
                @click="saveLesson">
                {{ $t('common.save') }}
            </button>

            <button class="button is-medium community-btn" @click="goToOverview">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
import { ClassroomViewType } from '../../../../../data/enums'
import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'
import MemberMultiSelect from "../../../../General/MemberMultiSelect";
import UploadFile from "../../../../General/UploadFile.vue";
import WysiwygEditor from "../../../../General/WysiwygEditor.vue";
import validateUrl from "../../../../../mixins/util";
import getVideoIdFromUrl from "../../../../../mixins/util";
export default {
    name: 'LessonEditView',
    mixins: [validateUrl, getVideoIdFromUrl],
    props: {

    },
    components: {
        Toggle,
        MemberMultiSelect,
        UploadFile,
        WysiwygEditor,
    },
    created() {
        this.self = this;
    },
    data() {
        return {
            ClassroomViewType,
            owner: 'lesson',
            invalidMedia: false,
            self: null,

            lessonConfirmDisabled: false,
            saveLessonProcessing: false,
            accessTypeOpts: ['all', 'only_member'],
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            displayResourceActionDropdown: false
        }
    },
    computed: {
        classroomShow() {
            return this.$store.state.classroom.classroomShow;
        },
        classroom() {
            return this.$store.state.classroom.data;
        },
        lesson() {
            return this.$store.state.classroom.cloneLesson;
        },
        lessonAccessValue: {
            get() {
                return (this.lesson.access_value_items || []);
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

                this.lesson.access_value = memberIds.join(",")
                this.lesson.access_value_items = v
            }
        },
        saveLessonBtn() {
            let button = 'button ';

            return (this.saveLessonProcessing)
                ? button + ' is-loading'
                : button;
        },

    },
    methods: {
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

        /**
         * Edit resource
         */
        editResource(item) {
            item = JSON.parse(JSON.stringify(item));
            if (item.type === 'link') {
                this.$store.commit('setLessonLink', item);
            } else {
                this.$store.commit('setLessonFile', item);
            }

            if (item.type === 'link') {
                this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_LESSON_LINK);
            } else {
                this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_LESSON_FILE);
            }

            this.hideResourceActionDropdown();
        },

        /**
         * Delete resource
         */
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

        addLinkToLesson() {
            this.$store.commit('setLessonLink', {
                type: 'link',
                label: '',
                url: ''
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_LESSON_LINK);
        },

        addFileToLesson() {
            this.$store.commit('setLessonFile', {
                type: 'image',
                label: '',
                url: ''
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_LESSON_FILE);
        },

        goToOverview() {
            this.$store.commit('setClassroomShow', ClassroomViewType.OVERVIEW);
        },

        async saveLesson() {
            let videoId = this.getVideoIdFromUrl(this.lesson.media);

            if (videoId === null) {
                this.invalidMedia = true;
            } else {
                this.invalidMedia = false;
                this.lesson.media = videoId
            }

            if (!this.invalidMedia) {
                this.saveLessonProcessing = true;
                if (this.lesson.id) {
                    await this.$store.dispatch('UPDATE_CLASSROOM_LESSON', this.lesson);
                } else {
                    await this.$store.dispatch('CREATE_CLASSROOM_LESSON', this.lesson);
                }
                this.saveLessonProcessing = false;

                this.goToOverview();
            }
        },

    }
}
</script>
<style lang="scss" scoped>
.classroom-content {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.resource-action-trigger {
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

.resource-action-trigger:hover,
.resource-action-trigger.active {
    background-color: rgb(228, 228, 228);
}

.resource-action-trigger .fa-ellipsis {
    opacity: 0.6;
}

.resource-action-trigger:hover .fa-ellipsis,
.resource-action-trigger.active .fa-ellipsis {
    opacity: 1;
}
</style>