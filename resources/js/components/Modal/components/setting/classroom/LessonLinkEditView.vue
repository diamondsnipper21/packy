<template>
    <div>
        <div v-if="classroomShow === ClassroomViewType.ADD_LESSON_LINK" class="tab-content-title">
            {{ $t('community.classroom.add-link') }}
        </div>
        <div v-else-if="classroomShow === ClassroomViewType.EDIT_LESSON_LINK" class="tab-content-title">
            {{ $t('community.classroom.edit-link') }}
        </div>

        <div class="flex mt2">
            <!-- Link label -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.label') }}
                </p>
                <input class="input" :placeholder="$t('community.classroom.label')" v-model="lessonLink.label" />
            </div>
        </div>

        <div class="flex mt2">
            <!-- Link url -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.url') }}
                </p>
                <input class="input" :placeholder="$t('community.classroom.url')" v-model="lessonLink.url"
                    @keypress="validateUrl($event)" />
            </div>
        </div>

        <div class="flex mt2 mb2">
            <button v-if="classroomShow === ClassroomViewType.ADD_LESSON_LINK"
                class="button is-medium community-blue-btn mr-05" :disabled="linkConfirmDisabled"
                @click="saveLessonLink">
                {{ $t('common.add') }}
            </button>
            <button v-else-if="classroomShow === ClassroomViewType.EDIT_LESSON_LINK"
                class="button is-medium community-blue-btn mr-05" :disabled="linkConfirmDisabled"
                @click="saveLessonLink">
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
import validateUrl from "../../../../../mixins/util";
export default {
    name: 'LessonLinkEditView',
    mixins: [validateUrl],
    props: {

    },
    components: {
    },
    data() {
        return {
            ClassroomViewType,
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
        lessonLink() {
            return this.$store.state.classroom.lessonLink;
        },
    },
    methods: {
        async saveLessonLink() {
            await this.$store.dispatch('UPDATE_LESSON_RESOURCE', this.lessonLink);
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