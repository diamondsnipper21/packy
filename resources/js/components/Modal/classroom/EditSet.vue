<template>
    <div class="inner-modal-container">
        <div v-if="modalExtraData === 'add'" class="tab-content-title">
            {{ $t('community.classroom.add-set') }}
        </div>
        <div v-else class="tab-content-title">
            {{ $t('community.classroom.edit-set') }}
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
                    <MemberMultiSelect v-model="setAccessValue" :init-options="memberOptions" />
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
                <button class="is-medium community-blue-btn mr-05" :class="saveSetBtn" :disabled="setConfirmDisabled"
                    @click="saveSet">
                    {{ $t('common.save') }}
                </button>

                <button class="button is-medium community-btn" @click="cancelSaveSet">
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

import getMemberName from "../../../mixins/util";
import getMemberGravatar from "../../../mixins/util";

export default {
    name: 'EditSet',
    data() {
        return {
            accessTypeOpts: ['all', 'only_member'],
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            saveSetProcessing: false,
        };
    },
    mixins: [
        getMemberName,
        getMemberGravatar
    ],
    components: {
        Toggle,
        MemberMultiSelect
    },
    mounted() {
        this.loadSetData()
    },
    computed: {
        /**
         * extra data of child modal
         */
        modalExtraData() {
            return this.$store.state.modal.extraData;
        },
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        set() {
            return this.$store.state.classroom.selectedSet;
        },

        /**
         * Get | Set classroom publish value
         */
        publish: {
            get() {
                let publish = false;
                if (this.set.publish) {
                    publish = true;
                }

                return publish;
            },
            set(v) {
                this.$store.commit('setSelectedSetProp', {
                    key: 'publish',
                    v
                });
            }
        },

        /**
         * Get | Set set access value
         */
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

        /**
         * Save set button class
         */
        saveSetBtn() {
            let button = 'button ';

            return (this.saveSetProcessing)
                ? button + ' is-loading'
                : button;
        },

        /**
         * Return confirm button disable status
         */
        setConfirmDisabled() {
            return this.set.name !== '' ? false : true;
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
    },
    methods: {
        /**
         * Save set
         */
        async saveSet() {
            if (this.set.name !== '') {
                this.saveSetProcessing = true;
                if (this.set.id) {
                    await this.$store.dispatch('UPDATE_CLASSROOM_SET', this.set);
                } else {
                    await this.$store.dispatch('CREATE_CLASSROOM_SET', this.set);
                }
                this.saveSetProcessing = false;

                this.$store.commit('hideModal');
            }
        },

        async loadSetData() {
            if (this.set.id && !this.set.access_type) {
                this.saveSetProcessing = true;
                await this.$store.dispatch('GET_CLASSROOM_SET', {
                    id: this.set.id,
                    classroom_id: this.set.classroom_id
                });
                this.saveSetProcessing = false;
            }
        },

        /**
         * Cancel set
         */
        cancelSaveSet() {
            this.$store.commit('hideModal');
        },
    }
}
</script>

<style scoped></style>
