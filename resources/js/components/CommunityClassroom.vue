<template>
    <div class="container pl-0 pr-0">
        <loading v-if="loading" :active.sync="loading" :is-full-page="true" />
        <div v-else>
            <div v-if="typeof community.name === 'undefined' || ![CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status)"
                class="empty-section">
                {{ $t('community.community.empty-community-placeholder') }}
            </div>
            <div v-else class="columns">
                <ClassroomNav class="column is-one-third" style="padding-left: 0;" />
                <div class="column is-two-thirds" style="padding-right: 0; flex: auto;">
                    <LessonEdit v-if="lessonEdit" />
                    <LessonView v-else />
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import linkify from "../mixins/util";

import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'

import { notify } from "@kyvg/vue3-notification";

import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'

import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

import WysiwygEditor from "./General/WysiwygEditor.vue";
import ClassroomNav from "./General/Classroom/ClassroomNav";
import LessonView from "./General/Classroom/LessonView";
import LessonEdit from "./General/Classroom/LessonEdit";
import { CommunityStatus } from '../data/enums';

export default {
    name: 'CommunityClassroom',
    mixins: [
        linkify
    ],
    data() {
        return {
            CommunityStatus,
            loading: false,

            newClassroomLessonAddLoadingId: 0,
            lessonDuplicateLoadingId: 0,
            invalidMedia: false,
            saveLessonProcessing: false,
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            uploadingText: this.$t('common.uploading'),
            owner: 'lesson',
            self: null
        };
    },
    components: {
        Loading,
        Toggle,
        Multiselect,
        WysiwygEditor,
        ClassroomNav,
        LessonView,
        LessonEdit
    },
    async created() {
        this.self = this;

        this.loading = true;

        await this.$store.dispatch('GET_CLASSROOM', {
            cid: this.$route.params.cid
        });

        await this.initSelectedLesson();
        this.loading = false;
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },

        lessonEdit() {
            return this.$store.state.classroom.lessonEdit;
        },

        classroom() {
            return this.$store.state.classroom.data || {};
        },

        firstLesson() {
            return this.classroom.first_lesson || null;
        }
    },
    methods: {
        /**
         * Initialize selected lesson
         */
        async initSelectedLesson() {
            let lessonId = this.$route.params.lid;
            if (!lessonId && this.firstLesson) {
                lessonId = this.firstLesson.id;
            }

            if (lessonId) {
                const lesson = await this.$store.dispatch('GET_CLASSROOM_LESSON', {
                    set_id: 0,
                    id: lessonId
                });
                if (lesson.set_id) {
                    this.$store.dispatch('GET_CLASSROOM_SET', {
                        id: lesson.set_id
                    });
                }
                this.$store.commit('setLessonEdit', false);
            } else {
                this.$store.commit('setSelectedLesson', {});
            }
        },
    }
}
</script>

<style scoped>
.classroom-set {
    margin-top: 7px;
}

.set-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 10px;
}
</style>
