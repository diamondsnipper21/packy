<template>
    <div class="lesson-item-section" :class="element.id === selectedLesson?.id ? 'active' : ''"
        @click="selectLesson()">
        <div v-if="isManager(role)" class="lesson-item-title"
            :class="element.completed ? '' : 'uncompleted'">
            <div class="tab-content-sub-title font-weight-300 mr-05">
                {{ element.name }}
            </div>

            <div class="item-privacy-mark published" :class="element.publish ? 'published' : 'draft'">
                {{ element.publish ? $t('community.classroom.published') : $t('community.classroom.draft')
                }}
            </div>
        </div>
        <div v-else class="lesson-item-title font-weight-300 no-privacy-mark"
            :class="element.completed ? '' : 'uncompleted'">
            {{ element.name }}
        </div>

        <div class="tab-content-item-action no-button">
            <font-awesome-icon v-if="element.completed" icon="fa fa-circle-check"
                class="completed-status-icon completed mr-05" />
            <div v-if="isManager(role)" class="dropdown">
                <div
                    class="dropdown-trigger flex align-items-center jc"
                    @click.stop="showLessonEditContent(element)"
                    v-click-outside="hideLessonEditDropdown"
                >
                    <span class="edit-ellipsis-icon-section">
                        <font-awesome-icon icon="fa fa-ellipsis" class="edit-ellipsis-icon" />
                    </span>
                </div>
                <div
                    :id="'lesson_edit_content_' + element.id"
                    class="dropdown-menu lesson-edit-content"
                    :class="displayLessonEditDropdown === element.id ? 'show' : ''"
                >
                    <div class="dropdown-content">
                        <div class="tab-content-item-action-item" @click.stop="selectLesson(true)">
                            {{ $t('community.classroom.edit-lesson') }}
                        </div>

                        <div class="tab-content-item-action-item" @click.stop="duplicateLesson(element)">
                            {{ $t('community.classroom.duplicate') }}
                        </div>

                        <div class="tab-content-item-action-item" @click.stop="deleteLesson(element)">
                            {{ $t('community.classroom.delete-lesson') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import isManager from "../../../mixins/util";

export default {
    name: 'LessonNavItem',
    mixins: [
        isManager
    ],
    props: {
        element: {
            type: Object,
        },
    },
    data() {
        return {
            displayLessonEditDropdown: false,
        }
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

        selectedLesson() {
            return this.$store.state.classroom.selectedLesson;
        },

        classroom() {
            return this.$store.state.classroom.data || {};
        },
    },
    methods: {
        async selectLesson(isEdit) {
            if (isEdit) {
                this.hideLessonEditDropdown();
            }
            
            await this.$store.dispatch('GET_CLASSROOM_LESSON', {
                set_id: this.element.set_id || 0,
                id: this.element.id
            });
            await this.$router.push('/' + this.community.url + '/ressources/' + this.classroom.id + '/lesson/' + this.element.id);
            this.$store.commit('setLessonEdit', isEdit);

            setTimeout(() => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth',
                });
            }, 500);
        },

        showLessonEditContent(lesson) {
            if (this.displayLessonEditDropdown) {
                if (this.displayLessonEditDropdown !== lesson.id) {
                    this.displayLessonEditDropdown = lesson.id;
                } else {
                    this.displayLessonEditDropdown = false;
                }
            } else {
                this.displayLessonEditDropdown = lesson.id;
            }
        },

        hideLessonEditDropdown() {
            this.displayLessonEditDropdown = false;
        },

        async duplicateLesson(lesson) {
            this.hideLessonEditDropdown();
            this.$store.dispatch('DUPLICATE_CLASSROOM_LESSON', lesson);
            this.$store.commit('setLessonEdit', false);
        },

        deleteLesson(lesson) {
            this.hideLessonEditDropdown();
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.classroom.confirm-delete-lesson.title'),
                    desc: this.$t('community.classroom.confirm-delete-lesson.desc'),
                    action: 'DELETE_CLASSROOM_LESSON',
                    param: lesson,
                    hideModal: false
                }
            });
        },

    }
}
</script>
<style lang="scss" scoped>
$color_1: rgb(144, 144, 144);
$color_2: #fff;
$background-color_1: #747bed;
$background-color_2: #7957d5;

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

.lesson-item-title.no-privacy-mark {
    .tab-content-sub-title {
        max-width: 346px;
    }
}

.lesson-item-title.no-privacy-mark.uncompleted {
    .tab-content-sub-title {
        max-width: 346px;
    }
}
</style>