<template>
    <div>
        <div v-if="classroomShow === ClassroomViewType.ADD_LESSON_FILE" class="tab-content-title">
            {{ $t('community.classroom.add-file') }}
        </div>
        <div v-else-if="classroomShow === ClassroomViewType.EDIT_LESSON_FILE" class="tab-content-title">
            {{ $t('community.classroom.edit-file') }}
        </div>

        <div class="flex mt2">
            <!-- File label -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.label') }}
                </p>
                <input class="input" :placeholder="$t('community.classroom.label')" v-model="lessonFile.label" />
            </div>
        </div>

        <div class="flex mt2">
            <!-- File url -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.url') }}
                </p>

                <div class="flex align-items-center">
                    <input class="input mr-05" :placeholder="$t('community.classroom.url')" v-model="lessonFile.url"
                        @keypress="validateUrl($event)" />
                    <UploadFile filetype="attachment" :owner="owner" />
                </div>
            </div>
        </div>

        <div class="flex mt2 mb2">
            <button v-if="classroomShow === ClassroomViewType.ADD_LESSON_FILE"
                class="button is-medium community-blue-btn mr-05" @click="saveLessonFile">
                {{ $t('common.add') }}
            </button>
            <button v-else-if="classroomShow === ClassroomViewType.EDIT_LESSON_FILE"
                class="button is-medium community-blue-btn mr-05" @click="saveLessonFile">
                {{ $t('common.save') }}
            </button>

            <button class="button is-medium community-btn" @click="goToPrevShow">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
import { ClassroomViewType } from '../../../../../data/enums'
import UploadFile from "../../../../General/UploadFile.vue";
import validateUrl from "../../../../../mixins/util";
export default {
    name: 'LessonFileEditView',
    mixins: [validateUrl],
    props: {

    },
    components: {
        UploadFile
    },
    data() {
        return {
            ClassroomViewType,
            owner: 'resource',
            linkConfirmDisabled: false,
        }
    },
    computed: {
        classroomShow() {
            return this.$store.state.classroom.classroomShow;
        },
        classroom() {
            return this.$store.state.classroom.data;
        },
        lessonFile() {
            return this.$store.state.classroom.lessonFile;
        },
    },
    methods: {
        async saveLessonFile() {
            await this.$store.dispatch('UPDATE_LESSON_RESOURCE', this.lessonFile);
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_LESSON);
        },

        goToPrevShow() {
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_LESSON);
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
</style>