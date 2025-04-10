<template>
    <div v-if="!lesson?.id"></div>
    <div v-else class="tab-content-item align-items-center">
        <div class="tab-content-item-content flex align-items-center">
            <div class="tab-content-title-without-ellipsis font-weight-300 mr1">
                {{ setIndex == -1 ? '' : `${setIndex + 1}. ` }}{{ elemIndex + 1 }}. {{ lesson.name }}
            </div>
            <div v-if="lesson.publish" class="item-privacy-mark published">
                {{ $t('community.classroom.published') }}
            </div>
            <div v-else class="item-privacy-mark draft">
                {{ $t('community.classroom.draft') }}
            </div>
        </div>
        <div class="tab-content-item-action">
            <button class="button is-medium community-btn mr-05" :class="loading ? 'is-loading' : ''"
                @click="editLesson()">
                {{ $t('community.classroom.edit-lesson') }}
            </button>

            <div :class="showDropdown(`lesson-${lesson.id}`)" class="tab-content-action-dropdown mr-05">
                <div class="dropdown-trigger flex">
                    <button class="button is-medium community-btn tab-content-action-dropdown-link"
                        @click.stop="toggleDropdown(`lesson-${lesson.id}`)">
                        <font-awesome-icon icon="fa fa-bars" class="tab-content-action-dropdown-link-icon" />
                    </button>
                </div>
                <div class="dropdown-menu-items">
                    <div class="tab-content-item-action-item" @click.stop="moveLesson('up')">
                        {{ $t('community.members.setting-modal.admin-settings.move-up') }}
                    </div>
                    <div class="tab-content-item-action-item" @click.stop="moveLesson('down')">
                        {{ $t('community.members.setting-modal.admin-settings.move-down') }}
                    </div>
                    <div class="tab-content-item-action-item" @click.stop="deleteLesson()">
                        {{ $t('common.delete') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ClassroomViewType } from '../../../../../data/enums';
export default {
    name: 'LessonSettingNavItem',
    props: {
        lesson: {
            type: Object,
        },
        classroom: {
            type: Object,
        },
        elemIndex: {
            type: Number,
        },
        setIndex: {
            type: Number,
        },
    },
    data() {
        return {
            loading: false,
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
        dropdownId() {
            return this.$store.state.classroom.dropdownId || '';
        }
    },
    methods: {
        async editLesson() {
            this.loading = true;
            await this.$store.dispatch('GET_CLASSROOM_LESSON', {
                set_id: this.lesson.set_id || 0,
                id: this.lesson.id
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_LESSON);
            this.$store.commit('toggleClassroomDropdownItem', '');
            this.loading = false;
        },

        showDropdown(itemId) {
            return this.dropdownId == itemId ? 'dropdown is-active' : 'dropdown';
        },

        toggleDropdown(dropdownId) {
            this.$store.commit('toggleClassroomDropdownItem', this.dropdownId == dropdownId ? '' : dropdownId);
        },

        moveLesson(direction) {
            this.$store.dispatch('MOVE_CLASSROOM_LESSON', {
                classroom_id: this.classroom.id,
                set_id: 0,
                id: this.lesson.id,
                direction
            });
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

        deleteLesson() {
            this.lesson.classroom_id = this.classroom.id;
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.classroom.confirm-delete-lesson.title'),
                    desc: this.$t('community.classroom.confirm-delete-lesson.desc'),
                    action: 'DELETE_CLASSROOM_LESSON',
                    param: this.lesson,
                    hideModal: false
                }
            });
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

    }
}
</script>
<style lang="scss" scoped></style>