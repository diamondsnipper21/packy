<template>
    <div>
        <div class="flex align-items-center jcb">
            <div v-if="classroomShow === ClassroomViewType.ADD_SET" class="tab-content-title">
                {{ $t('community.classroom.add-set') }}
            </div>
            <div v-else-if="classroomShow === ClassroomViewType.EDIT_SET" class="tab-content-title">
                {{ $t('community.classroom.edit-set') }}
            </div>

            <div class="flex align-items-center">
                <div v-if="set.publish" class="font-14px mr-05">
                    {{ $t('community.classroom.published') }}
                </div>
                <div v-else class="font-14px mr-05">
                    {{ $t('community.classroom.draft') }}
                </div>

                <Toggle v-model="set.publish" :true-value="1" :false-value="0" />
            </div>
        </div>

        <div class="flex mt2">
            <!-- Set name -->
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.classroom.set-name') }}
                </p>
                <input class="input" :placeholder="$t('community.classroom.set-name')" v-model="set.name"
                    @input="inputSetName" />
            </div>
        </div>

        <div class="mt2">
            <div class="flex">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.access-module') }}
                    </p>

                    <select class="input tab-content-select" v-model="set.access_type">
                        <option v-for="accessTypeOpt in accessTypeOpts" :value="accessTypeOpt"
                            :selected="set.access_type === accessTypeOpt">
                            {{ $t(`community.classroom.access-type-options.${accessTypeOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="set.access_type === 'only_member'" class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.member-access') }}
                    </p>
                    <MemberMultiSelect v-model="setAccessValue" :init-options="set.access_value_items" />
                </div>
            </div>

            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.classroom.level-access') }}
                    </p>

                    <select class="input tab-content-select" v-model="set.level">
                        <option value="" disabled>{{ $t('community.classroom.level-access') }}</option>
                        <option v-for="accessLevelValueOpt in accessLevelValueOpts" :value="accessLevelValueOpt"
                            :selected="set.level === accessLevelValueOpt">
                            {{ $t(`community.classroom.access-level-value-options.${accessLevelValueOpt}`) }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex mt2 mb2">
            <button v-if="classroomShow === ClassroomViewType.ADD_SET" class="is-medium community-blue-btn mr-05"
                :class="saveSetBtn" :disabled="setConfirmDisabled" @click="saveSet">
                {{ $t('common.add') }}
            </button>
            <button v-else-if="classroomShow === ClassroomViewType.EDIT_SET" class="is-medium community-blue-btn mr-05"
                :class="saveSetBtn" :disabled="setConfirmDisabled" @click="saveSet">
                {{ $t('common.save') }}
            </button>

            <button class="button is-medium community-btn" @click="cancelSaveSet">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
import { ClassroomViewType } from '../../../../../data/enums'
import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'
import MemberMultiSelect from "../../../../General/MemberMultiSelect";


export default {
    name: 'SetEditView',
    props: {

    },
    components: {
        Toggle,
        MemberMultiSelect,
    },
    computed: {
        classroomShow() {
            return this.$store.state.classroom.classroomShow;
        },
        classroom() {
            return this.$store.state.classroom.data;
        },
        set() {
            return this.$store.state.classroom.cloneSet;
        },
        setAccessValue: {
            get() {
                return (this.set.access_value_items || []);
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

                this.set.access_value = memberIds.join(",")
                this.set.access_value_items = v
            }
        },
        saveSetBtn() {
            let button = 'button ';

            return (this.saveSetProcessing)
                ? button + ' is-loading'
                : button;
        },

    },
    data() {
        return {
            ClassroomViewType,
            owner: 'set',
            setConfirmDisabled: false,
            saveSetProcessing: false,
            accessTypeOpts: ['all', 'only_member'],
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
        }
    },
    methods: {
        inputSetName() {
            if (this.set.name !== '') {
                this.setConfirmDisabled = false;
            } else {
                this.setConfirmDisabled = true;
            }
        },

        cancelSaveSet() {
            this.$store.commit('setClassroomShow', ClassroomViewType.OVERVIEW);
        },
        async saveSet() {
            this.saveSetProcessing = true;
            if (this.set.id) {
                await this.$store.dispatch('UPDATE_CLASSROOM_SET', this.set);
            } else {
                await this.$store.dispatch('CREATE_CLASSROOM_SET', this.set);
            }
            this.saveSetProcessing = false;
            this.$store.commit('setClassroomShow', ClassroomViewType.OVERVIEW);
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