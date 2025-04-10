<template>
    <div>
        <div class="flex align-items-center jcb">
            <div v-if="classroomShow === ClassroomViewType.ADD_CLASSROOM" class="tab-content-title">
                {{ $t('community.classroom.add-course') }}
            </div>
            <div v-else-if="classroomShow === ClassroomViewType.EDIT_CLASSROOM" class="tab-content-title">
                {{ $t('community.classroom.edit-course') }}
            </div>

            <div class="flex align-items-center">
                <div v-if="classroom.publish" class="font-14px mr-05">
                    {{ $t('community.classroom.published') }}
                </div>
                <div v-else class="font-14px mr-05">
                    {{ $t('community.classroom.draft') }}
                </div>

                <Toggle v-model="classroom.publish" :true-value="1" :false-value="0" />
            </div>
        </div>

        <div class="flex mt2">
            <!-- Classroom name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.course-name') }}
                </p>
                <input class="input" :placeholder="$t('community.classroom.course-name')" v-model="classroom.title"
                    @input="inputClassroomTitle" />
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
                        v-model="classroom.photo" @keypress="validateUrl($event)" />
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
                    v-model="classroom.content" rows="3" />
            </div>
        </div>

        <div class="mt2">
            <div class="flex">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.access-course') }}
                    </p>

                    <select class="input tab-content-select" v-model="classroom.access_type">
                        <option v-for="accessTypeOpt in accessTypeOpts" :value="accessTypeOpt"
                            :selected="classroom.access_type === accessTypeOpt">
                            {{ $t(`community.classroom.access-type-options.${accessTypeOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="classroom.access_type === 'only_member'" class="flex mt2">
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

                    <select class="input tab-content-select" v-model="classroom.level">
                        <option value="" disabled>{{ $t('community.classroom.level-access') }}</option>
                        <option v-for="accessLevelValueOpt in accessLevelValueOpts" :value="accessLevelValueOpt"
                            :selected="classroom.level === accessLevelValueOpt">
                            {{ $t(`community.classroom.access-level-value-options.${accessLevelValueOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex mt2 mb2">
            <button v-if="classroomShow === ClassroomViewType.ADD_CLASSROOM" class="is-medium community-blue-btn mr-05"
                :class="saveClassroomBtn" :disabled="classroomConfirmDisabled" @click="saveClassroom">
                {{ $t('common.add') }}
            </button>

            <button v-else-if="classroomShow === ClassroomViewType.EDIT_CLASSROOM"
                class="is-medium community-blue-btn mr-05" :class="saveClassroomBtn"
                :disabled="classroomConfirmDisabled" @click="saveClassroom">
                {{ $t('common.save') }}
            </button>

            <button class="button is-medium community-btn" @click="cancelSaveClassroom">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>

</template>

<script>
import md5 from 'md5'
import { ClassroomViewType } from '../../../../../data/enums'
import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'
import UploadFile from "../../../../General/UploadFile.vue";
import MemberMultiSelect from "../../../../General/MemberMultiSelect";

import getMemberName from "../../../../../mixins/util";
import getMemberGravatar from "../../../../../mixins/util";
import validateUrl from "../../../../../mixins/util";

export default {
    name: 'ClassroomEditView',
    mixins: [
        getMemberName,
        getMemberGravatar,
        validateUrl
    ],
    props: {

    },
    components: {
        Toggle,
        UploadFile,
        MemberMultiSelect,
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Returns community groups
         */
        groups() {
            return this.community.groups;
        },

        classroomShow() {
            return this.$store.state.classroom.classroomShow;
        },
        classroom() {
            return this.$store.state.classroom.cloneData;
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

        classroomAccessValue: {
            get() {
                return (this.classroom.access_value_items || []);
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

                this.classroom.access_value = memberIds.join(",")
                this.classroom.access_value_items = v
            }
        },
        saveClassroomBtn() {
            let button = 'button ';

            return (this.saveClassroomProcessing)
                ? button + ' is-loading'
                : button;
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
        }
    },
    methods: {
        inputClassroomTitle() {
            if (this.classroom.title !== '') {
                this.classroomConfirmDisabled = false;
            } else {
                this.classroomConfirmDisabled = true;
            }
        },

        getName(member)
        {
            let name = '';
            if (typeof member.firstname !== 'undefined') {
                name = member.firstname;
                if (typeof member.lastname !== 'undefined') {
                    name += ' ' + member.lastname;
                }
            }

            return name;
        },

        async saveClassroom() {
            this.saveClassroomProcessing = true;
            if (this.classroom.id) {
                await this.$store.dispatch('UPDATE_CLASSROOM', this.classroom);
            } else {
                await this.$store.dispatch('CREATE_CLASSROOM', this.classroom);
            }
            this.saveClassroomProcessing = false;

            this.$store.commit('setClassroomShow', ClassroomViewType.OVERVIEW);
        },

        async cancelSaveClassroom() {
            if (this.classroomShow === ClassroomViewType.EDIT_CLASSROOM) {
                await this.$store.dispatch('GET_CLASSROOM', {
                    cid: this.classroom.id
                });
                this.$store.commit('setClassroomShow', ClassroomViewType.OVERVIEW);
            } else if (this.classroomShow === ClassroomViewType.ADD_CLASSROOM) {
                this.$store.commit('setClassroomShow', ClassroomViewType.LIST);
            }
        },

    }
}
</script>
