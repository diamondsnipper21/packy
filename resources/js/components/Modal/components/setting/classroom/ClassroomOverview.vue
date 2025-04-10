<template>
    <div>
        <div class="flex align-items-center jcb">
            <div>
                <div class="tab-content-title">
                    {{ classroom.title }}
                </div>
                <div class="go-back-link" @click="goToList">
                    {{ $t('common.go-back') }}
                </div>
            </div>

            <div class="flex align-items-center">
                <button class="button is-medium community-btn mr-05" @click="editClassroom()">
                    {{ $t('community.classroom.edit-course') }}
                </button>

                <div class="dropdown">
                    <button
                        class="button is-medium community-blue-btn dropdown-trigger"
                        @click="addClassroomContent"
                        v-click-outside="hideClassroomContent"
                    >
                        {{ $t('community.classroom.add-content') }}
                        <font-awesome-icon icon="fa fa-chevron-down" class="font-12px ml-05" />
                    </button>
                    <div
                        id="add_classroom_content"
                        class="dropdown-menu"
                        :class="displayAddClassroomContentDropdown ? 'show' : ''"
                    >
                        <div class="dropdown-content">
                            <div @click="addLesson" class="dropdown-item font-weight-600">
                                {{ $t('community.classroom.add-lesson') }}
                            </div>
                            <div @click="addSet" class="dropdown-item font-weight-600">
                                {{ $t('community.classroom.add-set') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="(classroom.items || []).length === 0" class="mt2">
            <div class="flex">
                {{ $t('community.classroom.added-course-desc') }}
            </div>

            <div class="flex mt1">
                {{ $t('community.classroom.add-lesson-desc') }}
            </div>
        </div>

        <div v-else class="mt2">
          <template v-for="(item, classroomIndex) in classrooms">
            <template v-if="classroom.id === item.id">
              <ClassroomNavItem
                  v-for="(element, elemIndex) in item.items"
                  :key="element.id"
                  :element="element"
                  :elem-index="elemIndex"
                  :item="classroom"/>
            </template>
          </template>
        </div>
    </div>
</template>

<script>
import { ClassroomViewType } from '../../../../../data/enums'
import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'
import ClassroomNavItem from "./ClassroomNavItem";

export default {
    name: 'ClassroomOverview',
    props: {

    },
    components: {
        Toggle,
        ClassroomNavItem,
    },
    computed: {
        classroomShow() {
            return this.$store.state.classroom.classroomShow;
        },
        classroom() {
            return this.$store.state.classroom.data;
        },
        classrooms() {
          return this.$store.state.classroom.items || [];
        },
    },
    data() {
        return {
            ClassroomViewType,
            classroomConfirmDisabled: false,
            owner: 'classroom',
            saveClassroomProcessing: false,
            accessTypeOpts: ['all', 'only_member'],
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            displayAddClassroomContentDropdown: false
        }
    },
    methods: {
        async goToList() {
            this.$store.commit('setIsSimpleClassrooms', 1);
            await this.$store.dispatch('GET_CLASSROOMS');
            this.$store.commit('setClassroomShow', ClassroomViewType.LIST);
        },
        editClassroom() {
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_CLASSROOM);
        },

        addClassroomContent() {
            this.displayAddClassroomContentDropdown = !this.displayAddClassroomContentDropdown;
        },

        hideClassroomContent() {
            this.displayAddClassroomContentDropdown = false;
        },

        addSet() {
            this.$store.commit('setClassroomCloneSet', {
                id: 0,
                classroom_id: this.classroom.id,
                name: '',
                access_type: 'all',
                access_value: '',
                level: 1,
                publish: false
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.ADD_SET);
        },

        addLesson() {
            this.$store.commit('setCloneLesson', {
                id: 0,
                classroom_id: this.classroom.id,
                set_id: null,
                title: '',
                content: '',
                media: '',
                action_items: '',
                transcript: '',
                discuss: false,
                access_type: 'all',
                access_value: '',
                level: 1,
                publish: false,
                type: '',
                resources: []
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.ADD_LESSON);
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