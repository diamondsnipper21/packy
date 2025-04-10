<template>
    <div v-if="classrooms.length === 0" class="w100">
        <div class="tab-content-title">
            {{ $t('community.tabs.ressources') }}
        </div>
        <p class="tab-content-desc mt1" v-html="$t('community.classroom.add-course-desc')"/>
        <div class="mt1">
            <button class="button is-medium community-blue-btn" @click="addClassroom">
                {{ $t('community.classroom.add-first-course') }}
            </button>
        </div>
    </div>

    <div v-else class="w100">
        <div class="flex align-items-center jcb">
            <div class="tab-content-title">
                {{ $t('community.tabs.ressources') }}
            </div>

            <button class="button is-medium community-blue-btn text-uppercase" @click="addClassroom">
                {{ $t('community.classroom.add-course') }}
            </button>
        </div>

        <div class="mt2">
            <!-- No classrooms Exist -->
            <div v-if="classrooms.length === 0" class="empty-section">
                {{ $t('community.community.empty-classroom-placeholder') }}
            </div>

            <div v-else class="w100">
                <div v-for="item, classroomIndex in classrooms" :key="item.id" class="w100">
                    <div class="tab-content-item" :class="item.content === '' ? 'align-items-center' : ''">
                        <div class="tab-content-item-content">
                            <div class="flex align-items-center">
                                <div class="expand-collapse-section" :class="!item.expand ? 'collapsed' : 'expanded'"
                                    @click.stop="expandCollapseClassroomContent(item)">
                                    <div v-if="classroomCollapseLoadingId === item.id" class="classroom-loading"></div>
                                    <font-awesome-icon v-else icon="fa fa-caret-right"
                                        class="expand-collapse-section-icon" />
                                </div>

                                <div class="tab-content-title-without-ellipsis font-weight-600 mr1 clickable-classroom-title"
                                    @click="overviewClassroom(item)">
                                    {{ item.title }}
                                </div>
                                <div v-if="item.publish" class="item-privacy-mark published">
                                    {{ $t('community.classroom.published') }}
                                </div>
                                <div v-else class="item-privacy-mark draft">
                                    {{ $t('community.classroom.draft') }}
                                </div>
                            </div>
                            <div v-if="item.content !== ''" class="tab-content-desc classroom-content">
                                {{ item.content }}
                            </div>
                        </div>

                        <div class="tab-content-item-action">
                            <button class="button is-medium community-btn mr-05"
                                :class="classroomEditLoadingId === item.id ? 'is-loading' : ''"
                                @click="editClassroom(item)">
                                {{ $t('common.edit') }}
                            </button>

                            <div :class="showDropdown(`classroom-${item.id}`)" class="tab-content-action-dropdown">
                                <div class="dropdown-trigger flex">
                                    <button class="button is-medium community-btn tab-content-action-dropdown-link"
                                        @click.stop="toggleDropdown(`classroom-${item.id}`)">
                                        <font-awesome-icon icon="fa fa-bars"
                                            class="tab-content-action-dropdown-link-icon" />
                                    </button>
                                </div>
                                <div class="dropdown-menu-items">
                                    <div class="tab-content-item-action-item">
                                        <div v-if="classroomEditLoadingId === item.id"
                                            class="tab-content-item-action-loading"></div>
                                        <div v-else @click.stop="editClassroom(item)">
                                            {{ $t('community.classroom.edit-content') }}
                                        </div>
                                    </div>
                                    <div class="tab-content-item-action-item"
                                        @click.stop="moveUpClassroom(item, classroomIndex)">
                                        {{ $t('community.members.setting-modal.admin-settings.move-up') }}
                                    </div>
                                    <div class="tab-content-item-action-item"
                                        @click.stop="moveDownClassroom(item, classroomIndex)">
                                        {{ $t('community.members.setting-modal.admin-settings.move-down') }}
                                    </div>
                                    <div class="tab-content-item-action-item" @click.stop="deleteClassroom(item)">
                                        {{ $t('common.delete') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="item.expand" class="classroom-content-section">
                        <ClassroomNavItem :element="element" :elem-index="elemIndex" :item="item"
                            v-for="(element, elemIndex) in item.items" :key="element.id" />
                    </div>
                </div>
                <Pagination :total="total" :perPage="perPage" :current="currentPage" pageAction="GET_CLASSROOMS"
                    class="mb1" />
            </div>
        </div>
    </div>
</template>

<script>
import { ClassroomViewType } from '../../../../../data/enums'
import Pagination from "../../../../../components/General/Elements/Pagination.vue";
import LessonSettingNavItem from "./LessonSettingNavItem";
import ClassroomNavItem from "./ClassroomNavItem";
export default {
    name: 'ClassroomListView',
    props: {
    },
    components: {
        Pagination,
        LessonSettingNavItem,
        ClassroomNavItem
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
        classrooms() {
            return this.$store.state.classroom.items || [];
        },
        total() {
            return this.$store.state.classroom.pagination?.total || 0;
        },

        currentPage() {
            return this.$store.state.classroom.pagination?.current_page || 0;
        },

        perPage() {
            return this.$store.state.classroom.pagination?.per_page || 1;
        },
        dropdownId() {
            return this.$store.state.classroom.dropdownId || '';
        }
    },
    methods: {
        addClassroom() {
            this.$store.commit('setClassroomCloneData', {
                id: 0,
                title: '',
                content: '',
                publish: false,
                photo: '',
                media: '',
                access_type: 'all',
                access_value: '',
                level: 1,
                sets: [],
                lessons: [],
                setsCount: 0,
                lessonsCount: 0
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.ADD_CLASSROOM);
            this.$store.commit('toggleClassroomDropdownItem', '');
        },
        async expandCollapseClassroomContent(elem) {
            if (!elem.expand && !elem.items) {
                this.classroomCollapseLoadingId = elem.id;
                await this.$store.dispatch('GET_CLASSROOM', {
                    cid: elem.id,
                });
                this.classroomCollapseLoadingId = 0;
            } else {
                this.$store.commit('setClassroomExpand', {
                    id: elem.id,
                    expand: !elem.expand
                });
            }
            this.$store.commit('toggleClassroomDropdownItem', '');
        },
        async overviewClassroom(classroom) {
            this.classroomCollapseLoadingId = classroom.id;
            await this.$store.dispatch('GET_CLASSROOM', {
                cid: classroom.id
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.OVERVIEW);
            this.classroomCollapseLoadingId = 0;
        },

        async editClassroom(classroom) {
            this.classroomEditLoadingId = classroom.id;
            await this.$store.dispatch('GET_CLASSROOM', {
                cid: classroom.id
            });
            this.$store.commit('setClassroomShow', ClassroomViewType.EDIT_CLASSROOM);
            this.classroomEditLoadingId = 0;
        },

        showDropdown(itemId) {
            return this.dropdownId == itemId ? 'dropdown is-active' : 'dropdown';
        },

        toggleDropdown(dropdownId) {
            this.$store.commit('toggleClassroomDropdownItem', this.dropdownId == dropdownId ? '' : dropdownId);
        },

        async moveUpClassroom(item) {
            await this.$store.dispatch('MOVE_CLASSROOM', {
                id: item.id,
                direction: 'up'
            });
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

        async moveDownClassroom(item) {
            await this.$store.dispatch('MOVE_CLASSROOM', {
                id: item.id,
                direction: 'down'
            });
            this.$store.commit('toggleClassroomDropdownItem', '');
        },

        deleteClassroom(classroom) {
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