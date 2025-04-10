<template>
    <template v-if="element.type == 'set'">
        <div class="tab-content-item align-items-center">
            <div class="tab-content-item-content flex align-items-center">
                <div class="expand-collapse-section" :class="!element.expand ? 'collapsed' : 'expanded'"
                    @click.stop="expandCollapseClassroomSetContent(item.id, element)">
                    <div v-if="setCollapseLoadingId === item.id" class="classroom-loading">
                    </div>
                    <font-awesome-icon v-else icon="fa fa-caret-right" class="expand-collapse-section-icon" />
                </div>

                <div class="tab-content-title-without-ellipsis font-weight-600 mr1">
                    {{ elemIndex + 1 }}. {{ element.name }}
                </div>
                <div v-if="element.publish" class="item-privacy-mark published">
                    {{ $t('community.classroom.published') }}
                </div>
                <div v-else class="item-privacy-mark draft">
                    {{ $t('community.classroom.draft') }}
                </div>
            </div>
            <div class="tab-content-item-action">
                <button class="button is-medium community-btn mr-05" @click="editSet(element)">
                    {{ $t('community.classroom.edit-set') }}
                </button>

                <div :class="showDropdown(`set-${element.id}`)" class="tab-content-action-dropdown mr-05">
                    <div class="dropdown-trigger flex">
                        <button class="button is-medium community-btn tab-content-action-dropdown-link"
                            @click.stop="toggleDropdown(`set-${element.id}`)">
                            <font-awesome-icon icon="fa fa-bars" class="tab-content-action-dropdown-link-icon" />
                        </button>
                    </div>
                    <div class="dropdown-menu-items">
                        <div class="tab-content-item-action-item" @click.stop="moveUpSet(element, item.id)">
                            {{ $t('community.members.setting-modal.admin-settings.move-up')
                            }}
                        </div>
                        <div class="tab-content-item-action-item" @click.stop="moveDownSet(element, item.id)">
                            {{
                                $t('community.members.setting-modal.admin-settings.move-down')
                            }}
                        </div>
                        <div class="tab-content-item-action-item" @click.stop="deleteSet(element, item.id)">
                            {{ $t('common.delete') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="element.expand" class="pl-1-5">
            <template v-for="(lesson, lessonIndex) in element.lessons" :key="lesson.id">
                <LessonSettingNavItem
                    :lesson="lesson"
                    :classroom="item"
                    :elem-index="lessonIndex"
                    :set-index="elemIndex" />
            </template>
        </div>
    </template>

    <LessonSettingNavItem
        :lesson="element"
        :classroom="item"
        :elem-index="elemIndex"
        :set-index="-1"
        v-if="element.type == 'lesson'" />
</template>

<script>
import { ClassroomViewType } from '../../../../../data/enums'
import LessonSettingNavItem from "./LessonSettingNavItem";
export default {
    name: 'ClassroomNavItem',
    props: {
        element: Object,
        elemIndex: Number,
        item: Object
    },
    components: {
        LessonSettingNavItem
    },
    data() {
        return {
            ClassroomViewType,
            classroomCollapseLoadingId: 0,
            classroomEditLoadingId: 0,
            setCollapseLoadingId: 0,
        }
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },
        dropdownId() {
            return this.$store.state.classroom.dropdownId || '';
        }
    },
    methods: {
        showDropdown(itemId) {
            return this.dropdownId == itemId ? 'dropdown is-active' : 'dropdown';
        },

        toggleDropdown(dropdownId) {
            this.$store.commit('toggleClassroomDropdownItem', this.dropdownId == dropdownId ? '' : dropdownId);
        },

        async expandCollapseClassroomSetContent(classroom_id, elem) {
            if (!elem.expand && !elem.access_value) {
                this.setCollapseLoadingId = elem.id;
                await this.$store.dispatch('GET_CLASSROOM_SET', {
                    id: elem.id,
                    classroom_id: elem.classroom_id
                });
                this.setCollapseLoadingId = 0;
            } else {
                this.$store.commit('setClassroomListSet', {
                    classroom_id: elem.classroom_id,
                    id: elem.id,
                    expand: !elem.expand
                });
            }
        },

        async editSet(set) {
            this.setEditLoadingId = set.id;
            await this.$store.dispatch('GET_CLASSROOM_SET', {
                id: set.id,
                classroom_id: set.classroom_id
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_SET);
            this.setEditLoadingId = 0;
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

        moveUpSet(item, classroomId) {
            this.$store.dispatch('MOVE_CLASSROOM_SET', {
                classroom_id: classroomId,
                id: item.id,
                direction: 'up'
            });
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

        moveDownSet(item, classroomId) {
            this.$store.dispatch('MOVE_CLASSROOM_SET', {
                classroom_id: classroomId,
                id: item.id,
                direction: 'down'
            });
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

        deleteSet(set, classroomId) {
            set.classroom_id = classroomId
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
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

    }
}
</script>
<style lang="scss" scoped>
$color_1: #979696;
$color_2: #4a4a4a;

.clickable-classroom-title {
    cursor: pointer;

    &:hover {
        text-decoration: underline;
    }
}

.expand-collapse-section {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    cursor: pointer;
    margin-right: 8px;
    position: relative;

    &:hover {
        .expand-collapse-section-icon {
            color: $color_2;
        }
    }
}

.expand-collapse-section-icon {
    color: $color_1;
    -webkit-transition: .2s;
    transition: .2s;
    font-size: 18px;
}

.expand-collapse-section.expanded {
    .expand-collapse-section-icon {
        -webkit-transform: rotate(90deg);
        transform: rotate(90deg);
    }
}

.classroom-content-section {
    position: relative;
    padding-left: 1.5em;
}
</style>