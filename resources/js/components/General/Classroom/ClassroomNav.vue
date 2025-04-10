<template>
    <div>
        <div class="flex align-items-center jcb">
            <div class="cc-item-card-title"
                :class="isManager(role) ? 'cc-item-card-title-for-admin' : ''">
                {{ title }}
            </div>

            <div v-if="isManager(role)" class="dropdown">
                <div
                    class="dropdown-trigger flex align-items-center jc"
                    @click="showClassroomEditDropdown"
                    v-click-outside="hideClassroomEditDropdown"
                >
                    <span class="edit-ellipsis-icon-section">
                        <font-awesome-icon icon="fa fa-ellipsis" class="edit-ellipsis-icon" />
                    </span>
                </div>
                <div
                    :id="'classroom_edit_content_' + classroom.id"
                    class="dropdown-menu classroom-edit-content"
                    :class="displayClassroomEditContent ? 'show' : false"
                >
                    <div class="dropdown-content">
                        <div class="tab-content-item-action-item" @click.stop="editClassroom(classroom)">
                            {{ $t('community.classroom.edit-course') }}
                        </div>

                        <div class="tab-content-item-action-item" @click.stop="addSetInClassroom(classroom)">
                            {{ $t('community.classroom.add-set') }}
                        </div>

                        <div class="tab-content-item-action-item" @click.stop="addLessonInClassroom(classroom)">
                            {{ $t('community.classroom.add-lesson') }}
                        </div>

                        <div class="tab-content-item-action-item" @click.stop="deleteClassroom(classroom)">
                            {{ $t('community.classroom.delete-course') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cc-item-card-progress mt1">
            <div class="w100 flex align-items-center">
                <progress class="progress is-success" :value="completion" max="100" />
                <p class="ml1 progress-percent">{{ completion }}%</p>
            </div>
        </div>

        <div class="mt1">
            <template v-for="element in elements" :key="element.id">
                <!-- Set element -->
                <template v-if="element.type == 'set'">
                    <div class="tab-content-item align-items-center no-border">
                        <div v-if="isManager(role)"
                            class="tab-content-item-content flex align-items-center set-item-title w100">
                            <div class="tab-content-sub-title font-weight-600 mr-05 pointer" @click.stop="expandCollapseOneSetContent(element)">
                                {{ element.name }}
                            </div>
                            <div class="item-privacy-mark published" :class="element.publish ? 'published' : 'draft'">
                                {{ element.publish ? $t('community.classroom.published') :
                                    $t('community.classroom.draft')
                                }}
                            </div>
                        </div>
                        <div v-else
                            class="tab-content-item-content flex align-items-center font-weight-600 set-item-title no-privacy-mark w100">
                            <div class="tab-content-sub-title font-weight-600 pointer" @click.stop="expandCollapseOneSetContent(element)">
                                {{ element.name }}
                            </div>
                        </div>
                        <div class="tab-content-item-action no-button">
                            <div v-if="currentSetId === element.id" class="tab-content-item-action-loading">
                            </div>
                            <font-awesome-icon v-else icon="fa fa-caret-up" class="set-arrow-link mr-05"
                                :class="element.expand ? 'expanded' : 'collapsed'"
                                @click.stop="expandCollapseOneSetContent(element)" />
                            
                            <div v-if="isManager(role)" class="dropdown">
                                <div 
                                    class="dropdown-trigger flex align-items-center jc"
                                    @click.stop="showSetEditDropdown(element)"
                                    v-click-outside="hideSetEditDropdown"
                                >
                                    <span class="edit-ellipsis-icon-section">
                                        <font-awesome-icon icon="fa fa-ellipsis" class="edit-ellipsis-icon" />
                                    </span>
                                </div>
                                <div 
                                    :id="'set_edit_content_' + element.id" 
                                    class="dropdown-menu set-edit-content"
                                    :class="displaySetEditDropdown === element.id ? 'show' : ''"
                                >
                                    <div class="dropdown-content">
                                        <div class="tab-content-item-action-item" @click.stop="editSet(element)">
                                            {{ $t('community.classroom.edit-set') }}
                                        </div>
                                        <div class="tab-content-item-action-item" @click.stop="deleteSet(element)">
                                            {{ $t('community.classroom.delete-set') }}
                                        </div>

                                        <div class="tab-content-item-action-item" @click.stop="addLessonInSet(element)">
                                            {{ $t('community.classroom.add-lesson-in-set') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="element.expand && (element.lessons || []).length > 0" class="set-content">
                        <template v-for="lesson in element.lessons" :key="lesson.id">
                            <LessonNavItem :element="lesson" />
                        </template>
                    </div>
                </template>

                <!-- Lesson Element -->
                <LessonNavItem :element="element" v-if="element.type == 'lesson'" />
            </template>

        </div>
    </div>
</template>

<script>
import isManager from "../../../mixins/util";
import LessonNavItem from './LessonNavItem';
export default {
    name: 'ClassroomNav',
    mixins: [
        isManager
    ],
    components: {
        LessonNavItem
    },
    props: {},
    data() {
        return {
            currentSetId: 0,
            displayClassroomEditContent: false,
            displaySetEditDropdown: false
        };
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },

        member() {
            return this.$store.state.member.data;
        },

        memberExist() {
            return this.member?.id ? true : false;
        },

        access() {
            return this.memberExist ? this.member.access : null;
        },

        role() {
            return this.memberExist ? this.member.role : null;
        },

        classroom() {
            return this.$store.state.classroom.data || {};
        },

        title() {
            return this.classroom?.title || this.$t('community.classroom.new-course');
        },

        completion() {
            return this.classroom.completion || 0;
        },

        elements() {
            return this.classroom.items || [];
        },

        selectedLesson() {
            return this.$store.state.classroom.selectedLesson;
        },

    },
    methods: {
        showClassroomEditDropdown() {
            this.displayClassroomEditContent = !this.displayClassroomEditContent;
        },

        hideClassroomEditDropdown() {
            this.displayClassroomEditContent = false;
        },

        async editClassroom(classroom) {
            classroom = JSON.parse(JSON.stringify(classroom));
            this.$store.commit('showModal', {
                type: 'AddEditClassroom',
                extraData: classroom,
                transparent: true
            });

            this.hideClassroomEditDropdown();
        },

        async addSetInClassroom() {
            this.$store.commit('setSelectedSet', {
                id: 0,
                publish: 0,
                access_type: 'all',
                access_value: '',
                level: 1
            })

            this.$store.commit('showModal', {
                type: 'EditSet',
                extraData: 'add',
                transparent: true
            });

            this.hideClassroomEditDropdown();
        },

        addLessonInClassroom() {
            const blankLesson = {
                set_id: 0,
                id: 0,
                title: '',
                content: ' ',
                media: '',
                action_items: '',
                transcript: '',
                discuss: 0,
                publish: 0,
                access_type: 'all',
                access_value: '',
                level: 1,
                created_at: '',
                updated_at: '',
                completed: false,
                resources: []
            };
            this.$store.commit('setSelectedLesson', blankLesson)
            this.$store.commit('setCloneLesson', blankLesson)
            this.$store.commit('setLessonEdit', true);

            this.hideClassroomEditDropdown();
        },

        deleteClassroom(classroom) {
            classroom.from = 'detail';
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.classroom.confirm-delete-classroom.title'),
                    desc: this.$t('community.classroom.confirm-delete-classroom.desc'),
                    action: 'DELETE_CLASSROOM',
                    param: classroom,
                    hideModal: false
                }
            });

            this.hideClassroomEditDropdown();
        },

        async expandCollapseOneSetContent(elem) {
            if (!elem.expand && !elem.access_type) {
                this.currentSetId = elem.id;
                await this.$store.dispatch('GET_CLASSROOM_SET', {
                    id: elem.id,
                    classroom_id: elem.classroom_id

                });
                this.currentSetId = 0;
            } else {
                this.$store.commit('setClassroomSetExpand', {
                    id: elem.id,
                    classroom_id: elem.classroom_id,
                    expand: !elem.expand
                });
            }
        },

        showSetEditDropdown(set) {
            if (this.displaySetEditDropdown) {
                if (this.displaySetEditDropdown !== set.id) {
                    this.displaySetEditDropdown = set.id;
                } else {
                    this.displaySetEditDropdown = false;
                }
            } else {
                this.displaySetEditDropdown = set.id;
            }
        },

        hideSetEditDropdown() {
            this.displaySetEditDropdown = false;
        },

        editSet(set) {
            this.displaySetEditDropdown = false;
            this.$store.commit('setModalSize', 'small');
            this.$store.commit('setSelectedSet', set)
            this.$store.commit('showModal', {
                type: 'EditSet',
                extraData: 'edit',
                transparent: true
            });
        },

        deleteSet(set) {
            this.displaySetEditDropdown = false;
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.classroom.confirm-delete-set.title'),
                    desc: this.$t('community.classroom.confirm-delete-set.desc'),
                    action: 'DELETE_CLASSROOM_SET',
                    param: set,
                    hideModal: false
                }
            });
        },

        addLessonInSet(set) {
            this.displaySetEditDropdown = false;
            this.expandCollapseOneSetContent(set)
            const blankLesson = {
                set_id: set.id,
                id: 0,
                classroom_id: 0,
                title: '',
                content: ' ',
                media: '',
                action_items: '',
                transcript: '',
                discuss: 0,
                publish: 0,
                access_type: 'all',
                access_value: '',
                level: 1,
                created_at: '',
                updated_at: '',
                completed: false,
                resources: []
            };
            this.$store.commit('setSelectedLesson', blankLesson)
            this.$store.commit('setCloneLesson', blankLesson)
            this.$store.commit('setLessonEdit', true);
        }
    }
}
</script>
<style lang="scss" scoped>
$color_1: rgb(144, 144, 144);
$color_2: #fff;
$color_3: #979696;
$color_4: #4a4a4a;
$background-color_1: #747bed;
$background-color_2: #7957d5;

.fa-angle-down {
    transition-duration: 500ms;
    color: $color_1;
}

.expanded {
    i {
        transform: rotate(-180deg);
    }
}

.set-header-title {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 16px;
    font-weight: bold;
}

.set-content {
    padding: 0 0 0 10px;
    border-left: 2px solid $color_3;
    margin-left: 10px;
}

.lesson-item-section {
    padding: 7px 10px;
    cursor: pointer;
    margin-bottom: 3px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 10px;

    &:hover {
        background-color: $background-color_2;
        color: $color_2;
    }
}

.lesson-item-section.active {
    color: $color_2;
    background-color: $background-color_1;
}

.lesson-item-title {
    font-size: 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    width: calc(100% - 32px);

    .tab-content-sub-title {
        max-width: 225px;
    }
}

.lesson-item-title.uncompleted {
    width: 100%;

    .tab-content-sub-title {
        max-width: 245px;
    }
}

.set-item-title {
    .tab-content-sub-title {
        max-width: 200px;
    }
}

.set-item-title.no-privacy-mark {
    .tab-content-sub-title {
        max-width: 320px;
    }
}

.lesson-item-title.no-privacy-mark {
    .tab-content-sub-title {
        max-width: 300px;
    }
}

.lesson-item-title.no-privacy-mark.uncompleted {
    .tab-content-sub-title {
        max-width: 300px;
    }
}

.set-arrow-link {
    color: $color_3;
    cursor: pointer;
    -webkit-transition: .2s;
    transition: .2s;
    font-size: 18px;

    &:hover {
        color: $color_4;
    }
}

.set-arrow-link.expanded {
    -webkit-transform: rotate(180deg);
    transform: rotate(180deg);
}

.tab-content-item-action-loading {
    left: 0;
    top: 0;
    position: relative;

}

.tab-content-item {
    padding: 5px 10px;
}
</style>