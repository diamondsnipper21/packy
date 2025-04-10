<template>
    <div class="inner-modal-container">
        <div v-if="modalExtraData.id === 0" class="tab-content-title">
            {{ $t('community.classroom.add-course') }}
        </div>
        <div v-else class="tab-content-title">
            {{ $t('community.classroom.edit-course') }}
        </div>

        <div class="flex mt2">
            <!-- Classroom name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.course-name') }}
                </p>
                <input class="input" :placeholder="$t('community.classroom.course-name')"
                    v-model="modalExtraData.title" />
            </div>
        </div>

        <div class="flex mt2">
            <!-- Classroom photo -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.course-photo') }}
                </p>
                <div class="flex align-items-center">
                    <input class="input mr-05" :placeholder="$t('community.classroom.course-photo')"
                        v-model="classroom_photo" @keypress="validateUrl($event)" />
                    <UploadFile filetype="image" :owner="owner" />
                </div>
            </div>
        </div>

        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.course-desc') }}
                </p>
                <textarea class="textarea" :placeholder="$t('community.classroom.course-desc')"
                    v-model="modalExtraData.content" rows="3" />
            </div>
        </div>

        <div class="mt2">
            <div class="flex">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.access-course') }}
                    </p>

                    <select class="input tab-content-select" v-model="modalExtraData.access_type">
                        <option v-for="accessTypeOpt in accessTypeOpts" :value="accessTypeOpt"
                            :selected="modalExtraData.access_type === accessTypeOpt">
                            {{ $t(`community.classroom.access-type-options.${accessTypeOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="modalExtraData.access_type === 'only_member'" class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.member-access') }}
                    </p>

                    <MemberMultiSelect v-model="classroomAccessValue" :init-options="memberOptions" />
                </div>
            </div>

            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.level-access') }}
                    </p>

                    <select class="input tab-content-select" v-model="modalExtraData.level">
                        <option value="" disabled>{{ $t('community.classroom.level-access') }}</option>
                        <option v-for="accessLevelValueOpt in accessLevelValueOpts" :value="accessLevelValueOpt"
                            :selected="modalExtraData.level === accessLevelValueOpt">
                            {{ $t(`community.classroom.access-level-value-options.${accessLevelValueOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex align-items-center jcb mt2 mb2">
            <div class="flex align-items-center">
                <div v-if="publish" class="font-14px mr-05">
                    {{ $t('community.classroom.published') }}
                </div>
                <div v-else class="font-14px mr-05">
                    {{ $t('community.classroom.draft') }}
                </div>

                <Toggle v-model="publish" />
            </div>

            <div class="flex align-items-center">
                <button v-if="classroom.id === 0" class="is-medium community-blue-btn mr-05" :class="saveClassroomBtn"
                    :disabled="classroomConfirmDisabled" @click="saveClassroom">
                    {{ $t('common.add') }}
                </button>

                <button v-else class="is-medium community-blue-btn mr-05" :class="saveClassroomBtn"
                    :disabled="classroomConfirmDisabled" @click="saveClassroom">
                    {{ $t('common.save') }}
                </button>

                <button class="button is-medium community-btn" @click="cancelSaveClassroom">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>

import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'
import MemberMultiSelect from "../../General/MemberMultiSelect";

import UploadFile from "../../General/UploadFile.vue";
import validateUrl from "../../../mixins/util";
import getMemberName from "../../../mixins/util";
import getMemberGravatar from "../../../mixins/util";

export default {
    name: 'AddEditClassroom',
    mixins: [
        getMemberName,
        getMemberGravatar,
        validateUrl
    ],
    data() {
        return {
            accessTypeOpts: ['all', 'only_member'],
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            owner: 'modal'
        };
    },
    components: {
        Toggle,
        UploadFile,
        MemberMultiSelect
    },
    computed: {
        saveClassroomProcessing() {
            return this.$store.state.classroom.dataLoading;
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
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns classroom
         */
        classroom() {
            return this.$store.state.classroom.data;
        },

        /**
         * Get | Set photo
         */
        classroom_photo: {
            get() {
                return this.modalExtraData.photo;
            },
            set(v) {
                this.$store.commit('setModalExtraDataProperty', {
                    key: 'photo',
                    v
                });
            }
        },

        /**
         * Get | Set classroom publish value
         */
        publish: {
            get() {
                let publish = false;
                if (this.modalExtraData.publish) {
                    publish = true;
                }

                return publish;
            },
            set(v) {
                this.$store.commit('setModalExtraDataProperty', {
                    key: 'publish',
                    v
                });
            }
        },

        /**
         * Returns community groups
         */
        groups() {
            return this.community.groups;
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers ()
        {
            return this.$store.state.communitycenter.allowedMembers;
        },

        /**
         * Returns init options
         */
        memberOptions ()
        {
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            let memberOptions = [];
            if (members.length > 0) {
                members.map((member, index) => {
                    memberOptions.push({
                        id: member.id,
                        name: this.getMemberName(member),
                        avatar: this.getMemberGravatar(member)
                    });
                });
            }

            let groups = JSON.parse(JSON.stringify(this.groups));
            if (groups.length > 0) {
                groups.map((group, index) => {
                    memberOptions.push({
                        id: 'group_' + group.id,
                        name: group.name,
                        avatar: ''
                    });
                });
            }

            return memberOptions;
        },

        /**
         * Get | Set classroom access value
         */
        classroomAccessValue: {
            get() {
                return (this.modalExtraData.access_value_items || []);
            },
            set(v) {
                let memberIds = [];
                if (Array.isArray(v)) {
                    v.map(member => {
                        memberIds.push(member.id);
                    });
                } else if (typeof v === 'object') {
                    memberIds.push(v.id);
                }

                this.modalExtraData.access_value = memberIds.join(",")
                this.modalExtraData.access_value_items = v
            }
        },

        /**
         * Save classroom button class
         */
        saveClassroomBtn() {
            let button = 'button ';

            return (this.saveClassroomProcessing)
                ? button + ' is-loading'
                : button;
        },

        /**
         * extra data of child modal
         */
        modalExtraData() {
            return this.$store.state.modal.extraData;
        },

        /**
         * Return confirm button disable status
         */
        classroomConfirmDisabled() {
            return this.modalExtraData.title !== '' ? false : true;
        },
    },
    methods: {
        /**
         * Save classroom
         */
        async saveClassroom() {
            if (this.modalExtraData.title !== '') {
                if (this.modalExtraData.id) {
                    await this.$store.dispatch('UPDATE_CLASSROOM', this.modalExtraData);
                } else {
                    await this.$store.dispatch('CREATE_CLASSROOM', this.modalExtraData);
                }
                this.$store.commit('hideModal');

                this.$router.push('/' + this.community.url + '/ressources/' + this.classroom.id);
            }
        },

        /**
         * Cancel classroom
         */
        cancelSaveClassroom() {
            this.$store.commit('hideModal');
        },

        /**
         * Show login modal
         */
        showLogin() {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },
    }
}
</script>

<style scoped></style>
