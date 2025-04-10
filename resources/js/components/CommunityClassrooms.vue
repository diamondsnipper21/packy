<template>
    <div class="container pl-0 pr-0">
        <loading v-if="loading" :active.sync="loading" :is-full-page="true" />
        <template v-else>
            <!-- No classrooms Exist -->
            <div v-if="typeof community.name === 'undefined' || ![CommunityStatus.PUBLISHED, CommunityStatus.PENDING].includes(community.status)"
                class="empty-section">
                {{ $t('community.community.empty-community-placeholder') }}
            </div>
            <div v-else-if="classrooms.length > 0">
                <div class="columns" style="flex-wrap: wrap;" ref="classRoomRef">
                    <div v-for="item, i in classrooms" :key="item.id" class="column is-one-third"
                        :class="accessClassroom(item) ? '' : 'access-locked'">
                        <div v-if="item.id === 0 && isManager(role)" class="cc-item-card"
                            @click="addNewClassroom(item)">
                            <div v-if="newCourseSaveLoading" class="new-course-save-loading"></div>
                            <div v-else class="new-course-section">
                                + {{ $t('community.classroom.new-course') }}
                            </div>
                        </div>
                        <div v-else class="cc-item-card" @click="goToClassroomDetail(item)">
                            <div class="cc-item-card-header">
                                <img v-if="item.photo" :src="item.photo" class="cc-item-card-img" />
                                <div v-if="accessLevel(item) > 0" class="cc-lock-section">
                                    <font-awesome-icon icon="fa fa-lock" class="access-locked-level-icon" />
                                    <div class="access-locked-level-desc">
                                        {{ $t('community.classroom.unlock-at-level').replace("#level#",
                                            accessLevel(item)) }}
                                    </div>
                                </div>
                                <div v-else-if="!accessMember(item)" class="cc-lock-section">
                                    <font-awesome-icon icon="fa fa-lock" class="access-locked-level-icon" />
                                    <div class="access-locked-level-desc">
                                        {{ $t('community.classroom.private-course') }}
                                    </div>
                                </div>
                                <div v-if="isManager(role)" class="cc-item-action">
                                    <div class="dropdown">
                                        <div class="dropdown-trigger flex align-items-center jc"
                                            @click.stop="showClassroomDropdown(item)"
                                             v-click-outside="hideClassroomDropdown"
                                        >
                                            <span class="edit-ellipsis-icon-section">
                                                <font-awesome-icon icon="fa fa-ellipsis" class="edit-ellipsis-icon" />
                                            </span>
                                        </div>
                                        <div :id="'classroom_edit_content_' + item.id"
                                            class="dropdown-menu classroom-edit-content"
                                            :class="displayClassroomDropdown === item.id ? 'show' : ''">
                                            <div class="dropdown-content">
                                                <div class="tab-content-item-action-item">
                                                    <div v-if="classroomEditLoadingId === item.id"
                                                        class="tab-content-item-action-loading"></div>
                                                    <div v-else @click.stop="goToClassroomDetail(item)">
                                                        {{ $t('community.classroom.edit-course') }}
                                                    </div>
                                                </div>

                                                <div class="tab-content-item-action-item"
                                                    @click.stop="deleteClassroom(item)">
                                                    {{ $t('community.classroom.delete-course') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="cc-item-card-content">
                                <div class="cc-item-card-title">
                                    {{ item.title }}
                                </div>
                                <div class="cc-item-card-desc mt1">
                                    {{ item.content }}
                                </div>
                                <div class="cc-item-card-progress mt1">
                                    <div class="w100 flex align-items-center">
                                        <progress class="progress is-success" :value="item.completion" max="100" />
                                        <p class="ml1 progress-percent">{{ item.completion }}%</p>
                                    </div>
                                </div>

                                <button class="button is-medium mt1 w100 classroom-open-btn"
                                    :disabled="!accessClassroom(item)">
                                    {{ $t('community.community.classroom.open') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <Pagination :total="total" :perPage="perPage" :current="currentPage" pageAction="GET_CLASSROOMS"
                    class="mb1" />
            </div>
        </template>
    </div>
</template>

<script>

import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/css/index.css'
import Pagination from "../components/General/Elements/Pagination.vue";
import { notify } from "@kyvg/vue3-notification";
import { CommunityStatus } from '../data/enums';
import isManager from "../mixins/util";

export default {
    name: 'CommunityClassrooms',
    mixins: [
        isManager
    ],
    components: {
        Loading,
        Pagination
    },
    data() {
        return {
            CommunityStatus,
            newCourseSaveLoading: false,
            classroomEditLoadingId: 0,
            displayClassroomDropdown: false
        };
    },
    async created() {
        this.$store.commit('setIsSimpleClassrooms', 0);
        await this.$store.dispatch('GET_CLASSROOMS');
    },
    watch: {
        'currentPage': function (v) {
            if (this.$refs.classRoomRef) {
                window.scroll({
                    top: this.$refs.classRoomRef.offsetTop,
                    left: 0,
                    behavior: 'smooth'
                })
            }
        },
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        loading() {
            return this.$store.state.classroom.loading;
        },

        /**
         * Returns community posts
         */
        classrooms() {
            return this.$store.state.classroom.items || [];
        },

        /**
         * Return auth status
         */
        auth() {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns member
         */
        member() {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist() {
            return (typeof this.member.id !== 'undefined' && parseInt(this.member.id) > 0) ? true : false;
        },

        /**
         * Returns access of member
         */
        access() {
            return this.memberExist ? this.member.access : null;
        },

        /**
         * Returns role of member
         */
        role() {
            return this.memberExist ? this.member.role : null;
        },

        /**
         * Returns leaderboard level
         */
        level() {
            let level = 0;
            if (this.memberExist) {
                level = this.member.level;
            }

            return level;
        },

        /**
         * Returns community groups
         */
        groups() {
            return this.community.groups;
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

    },
    methods: {
        /**
         * Return access level
         */
        accessLevel(item) {
            let accessLevel = 0;
            if (!this.accessClassroom(item)) {
                if (parseInt(this.level) < parseInt(item.level)) {
                    accessLevel = item.level;
                }
            }

            return accessLevel;
        },

        /**
         * Return member access ability
         */
        accessMember(item) {
            let accessMember = true;
            if (!this.accessClassroom(item) && !this.accessLevel(item)) {
                if (item.access_type === 'only_member') {
                    accessMember = false;
                }
            }

            return accessMember;
        },

        /**
         * Go to classroom detail
         */
        goToClassroomDetail(item) {
            if (this.accessClassroom(item)) {
                this.$router.push('/' + this.community.url + '/ressources/' + item.id);
            } else {
                if (this.accessLevel(item) > 0) {
                    this.$store.commit('setModalSize', 'small');
                    this.$store.commit('showModal', {
                        type: 'LevelAccessAlert',
                        extraData: item,
                        transparent: true
                    });
                } else {
                    notify({
                        text: this.$t('community.classroom.cannot-access-classroom'),
                        type: 'error'
                    });
                }
            }
        },

        /**
         * Go to new classroom detail
         */
        async addNewClassroom(classroom) {
            this.newCourseSaveLoading = true;

            await new Promise((resolve) => setTimeout(() => { resolve(); }, 500));

            classroom = JSON.parse(JSON.stringify(classroom));
            this.$store.commit('showModal', {
                type: 'AddEditClassroom',
                extraData: classroom,
                transparent: true
            });

            this.newCourseSaveLoading = false;
        },

        /**
         * Return classroom accessability
         */
        accessClassroom(item) {
            let accessClassroom = false;
            if (this.isManager(this.role)) {
                accessClassroom = true;
            } else {
                if (item.access_type === 'all') {
                    accessClassroom = true;
                } else if (item.access_type === 'only_member') {
                    let values = (item.access_value || '').split(",");
                    let memberGroups = (this.member.groups || []).map(item => `group_${item.id}`)
                    memberGroups = [...memberGroups, this.member.id.toString()]

                    for (const elem of values) {
                        const value = elem.toString();
                        if (memberGroups.includes(value)) {
                            accessClassroom = true;
                            break;
                        }
                    }
                }

                if (accessClassroom && parseInt(item.level) > parseInt(this.member.level)) {
                    accessClassroom = false;
                }
            }

            return accessClassroom;
        },

        showClassroomDropdown(classroom) {
            if (this.displayClassroomDropdown) {
                if (this.displayClassroomDropdown !== classroom.id) {
                    this.displayClassroomDropdown = classroom.id;
                } else {
                    this.displayClassroomDropdown = false;
                }
            } else {
                this.displayClassroomDropdown = classroom.id;
            }
        },

        hideClassroomDropdown() {
            this.displayClassroomDropdown = false;
        },

        /**
         * Handler for deleting classroom
         */
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
        },
    }
}
</script>

<style scoped>
.classroom-open-btn {
    color: rgb(144, 144, 144);
    font-weight: 600;
}

.classroom-open-btn:hover {
    color: rgb(32, 33, 36);
}
.dropdown-menu {
    top: 35px;
}
</style>
