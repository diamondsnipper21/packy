<template>
    <div @click="outclick">
        <ClassroomListView v-if="classroomShow === ClassroomViewType.LIST" />
        <ClassroomEditView
            v-else-if="classroomShow === ClassroomViewType.ADD_CLASSROOM || classroomShow === ClassroomViewType.EDIT_CLASSROOM" />
        <ClassroomOverview v-else-if="classroomShow === ClassroomViewType.OVERVIEW" />
        <SetEditView
            v-else-if="classroomShow === ClassroomViewType.ADD_SET || classroomShow === ClassroomViewType.EDIT_SET" />
        <LessonEditView
            v-else-if="classroomShow === ClassroomViewType.ADD_LESSON || classroomShow === ClassroomViewType.EDIT_LESSON" />
        <LessonLinkEditView
            v-else-if="classroomShow === ClassroomViewType.ADD_LESSON_LINK || classroomShow === ClassroomViewType.EDIT_LESSON_LINK" />
        <LessonFileEditView
            v-else-if="classroomShow === ClassroomViewType.ADD_LESSON_FILE || classroomShow === ClassroomViewType.EDIT_LESSON_FILE" />

    </div>
</template>

<script>
import { ClassroomViewType } from "../../../../data/enums";
import ClassroomListView from "../setting/classroom/ClassroomListView";
import ClassroomEditView from "../setting/classroom/ClassroomEditView";
import ClassroomOverview from "../setting/classroom/ClassroomOverview";
import SetEditView from "../setting/classroom/SetEditView";
import LessonEditView from "../setting/classroom/LessonEditView";
import LessonLinkEditView from "../setting/classroom/LessonLinkEditView";
import LessonFileEditView from "../setting/classroom/LessonFileEditView";

export default {
    name: 'ClassroomSetting',
    mixins: [
    ],
    components: {
        ClassroomListView,
        ClassroomEditView,
        ClassroomOverview,
        SetEditView,
        LessonEditView,
        LessonFileEditView,
        LessonLinkEditView
    },
    data() {
        return {
            ClassroomViewType,
        };
    },
    created() {
    },
    computed: {
        classroomShow() {
            return this.$store.state.classroom.classroomShow;
        },
    },
    methods: {
        outclick(e) {
            if (!e.target.classList.contains('resource-action-trigger') && !e.target.classList.contains('dropdown') && !e.target.classList.contains('fa-ellipsis') && e.target.parentElement && !e.target.parentElement.classList.contains('fa-ellipsis')) {
                Array.prototype.forEach.call(document.getElementsByClassName('resource-action-trigger'), function (el) {
                    el.classList.remove("active");
                });
                Array.prototype.forEach.call(document.getElementsByClassName('resource-action-content'), function (el) {
                    el.classList.remove("show");
                });
            }
        },
    },
}
</script>
