<template>
    <div>
        <div v-if="groupShow === 'list'">
            <div v-if="typeof groups === 'undefined' || groups.length === 0" class="w100">
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.admin-settings.groups') }}
                </div>
                
                <p class="tab-content-desc mt1" v-html="$t('community.members.setting-modal.admin-settings.add-group-desc')">
                </p>

                <div class="mt1">
                    <button class="button is-medium community-blue-btn" @click="addGroup">
                        {{ $t('community.members.setting-modal.admin-settings.add-first-group') }}
                    </button>
                </div>
            </div>

            <div v-else class="w100">
                <div class="flex align-items-center jcb">
                    <div class="tab-content-title">
                        {{ $t('community.members.setting-modal.admin-settings.groups') }}
                    </div>

                    <button class="button is-medium community-blue-btn" @click="addGroup">
                        {{ $t('community.members.setting-modal.admin-settings.add-group') }}
                    </button>
                </div>

                <div class="mt2 mb2">
                    <div
                        v-for="group in groups"
                        :key="group.id"
                        class="tab-content-item"
                    >
                        <div class="tab-content-item-content">
                            <div class="flex align-items-start">
                                <div class="tab-content-sub-title font-weight-600 mr1">
                                    {{ group.name }}
                                </div>

                                <div v-if="group.publish" class="item-privacy-mark published">
                                    {{ $t('community.community.public-group') }}
                                </div>
                                <div v-else class="item-privacy-mark draft">
                                    {{ $t('community.community.private-group') }}
                                </div>
                            </div>
                                
                            <div class="tab-content-desc">
                                {{ group.description }}
                            </div>
                        </div>
                        <div class="tab-content-item-action">
                            <button class="button is-medium community-btn mr-05" @click="editGroup(group)">
                                {{ $t('common.edit') }}
                            </button>

                            <div :class="showDropdown(group)" class="tab-content-action-dropdown" v-click-outside="onClickOutside">
                                <div class="dropdown-trigger flex">
                                    <button class="button is-medium community-btn tab-content-action-dropdown-link" @click.stop="toggleDropdown(group)">
                                        <font-awesome-icon icon="fa fa-bars" class="tab-content-action-dropdown-link-icon" />
                                    </button>
                                </div>
                                <div class="dropdown-menu-items">
                                    <div
                                        class="tab-content-item-action-item"
                                        @click.stop="deleteGroup(group)"
                                    >
                                        {{ $t('common.delete') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="groupShow === 'add'">

            <div class="flex align-items-center jcb">
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.admin-settings.add-group') }}
                </div>

                <div class="flex align-items-center">
                    <div v-if="newGroup.publish" class="font-14px mr-05">
                        {{ $t('community.community.public-group') }}
                    </div>
                    <div v-else class="font-14px mr-05">
                        {{ $t('community.community.private-group') }}
                    </div>

                    <Toggle v-model="newGroup.publish" />
                </div>
            </div>
                
            <div class="flex mt2">
                <!-- Group url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.create-modal.group-name') }}
                    </p>
                    <input
                        class="input"
                        :placeholder="$t('community.community.create-modal.group-name')"
                        v-model="newGroup.name"
                        @input="inputNewGroupName"
                    />
                </div>
            </div>
            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.description') }}
                    </p>
                    <textarea
                        class="textarea"
                        :placeholder="$t('community.community.description')"
                        v-model="newGroup.description"
                        rows="3"
                    />
                </div>
            </div>

            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.members.setting-modal.admin-settings.select-member-for-group') }}
                    </p>

                    <multiselect v-model="newGroupMembers"
                        :options="ordinaryMembers"
                        :multiple="true"
                        :close-on-select="true"
                        :clear-on-select="true"
                        :preserve-search="true"
                        :hide-selected="true"
                        label="name"
                        track-by="name"
                        :placeholder="$t('community.members.setting-modal.admin-settings.select-member-for-group')"
                        :select-label="$t('common.press-enter-to-select')"
                        :selected-label="$t('common.selected')"
                        :deselect-label="$t('common.press-enter-to-deselect')"
                    >
                        <template #option="props">
                            <div class="flex align-items-center">
                                <img class="multi-select-option-avatar mr-05" :src="props.option.avatar"/>
                                <span class="multi-select-option-name">{{ props.option.name }}</span>
                            </div>
                        </template>
                    </multiselect>
                </div>
            </div>

            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="addConfirm" @click="saveAddGroup">
                    {{ $t('common.add') }}
                </button>
                <button class="button is-medium community-btn" @click="close">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

        <div v-else-if="groupShow === 'edit'">

            <div class="flex align-items-center jcb">
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.admin-settings.edit-group') }}
                </div>

                <div class="flex align-items-center">
                    <div v-if="editedGroup.publish" class="font-14px mr-05">
                        {{ $t('community.community.public-group') }}
                    </div>
                    <div v-else class="font-14px mr-05">
                        {{ $t('community.community.private-group') }}
                    </div>

                    <Toggle v-model="editedGroup.publish" />
                </div>
            </div>

            <div class="flex mt2">
                <!-- Group url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.create-modal.group-name') }}
                    </p>
                    <input
                        class="input"
                        :placeholder="$t('community.community.create-modal.group-name')"
                        v-model="editedGroup.name"
                        @input="inputEditGroupName"
                    />
                </div>
            </div>
            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.description') }}
                    </p>
                    <textarea
                        class="textarea"
                        :placeholder="$t('community.community.description')"
                        v-model="editedGroup.description"
                        rows="3"
                    />
                </div>
            </div>
            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.members.setting-modal.admin-settings.select-member-for-group') }}
                    </p>

                    <multiselect v-model="editedGroupMembers"
                        :options="ordinaryMembers"
                        :multiple="true"
                        :close-on-select="true"
                        :clear-on-select="true"
                        :preserve-search="true"
                        :hide-selected="true"
                        label="name"
                        track-by="name"
                        :placeholder="$t('community.members.setting-modal.admin-settings.select-member-for-group')"
                        :select-label="$t('common.press-enter-to-select')"
                        :selected-label="$t('common.selected')"
                        :deselect-label="$t('common.press-enter-to-deselect')"
                    >
                        <template #option="props">
                            <div class="flex align-items-center">
                                <img class="multi-select-option-avatar mr-05" :src="props.option.avatar"/>
                                <span class="multi-select-option-name">{{ props.option.name }}</span>
                            </div>
                        </template>
                    </multiselect>
                </div>
            </div>
            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="editConfirm" @click="saveEditGroup">
                    {{ $t('common.save') }}
                </button>
                <button class="button is-medium community-btn" @click="close">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

    </div>
</template>

<script>

import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'

import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'

import getMemberName from "../../../../mixins/util";
import getMemberGravatar from "../../../../mixins/util";

export default {
	name: 'GroupsSetting',
    data () {
        return {
            editedGroup: null,
            newGroup: null,
            addConfirm: true,
            editConfirm: true,
        };
    },
    mixins: [
        getMemberName,
        getMemberGravatar
    ],
    components: {
        Toggle,
        Multiselect
    },
    computed: {
        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },

        /**
         * Returns community groups
         */
        groups ()
        {
            return this.community.groups;
        },

        /**
         * Returns community community settings
         */
        communitySettings ()
        {
            return this.$store.state.communitycenter.communitySettings;
        },

        /**
         * Returns setting group show
         */
        groupShow ()
        {
            return this.communitySettings.groupShow;
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers ()
        {
            return this.$store.state.communitycenter.allowedMembers;
        },

        /**
         * Returns requested admin members
         */
        adminMembers ()
        {
            return this.$store.state.communitycenter.adminMembers;
        },

        /**
         * Returns requested admin member ids
         */
        adminMemberIds ()
        {
            let adminMemberIds = [];
            if (this.adminMembers.length > 0) {
                this.adminMembers.map((member, index) => {
                    adminMemberIds.push(member.id);
                });
            }

            return adminMemberIds;
        },

        /**
         * Returns ordinary members
         */
        ordinaryMembers ()
        {
            let ordinaryMembers = [];
            if (this.allowedMembers.length > 0) {
                this.allowedMembers.map((member, index) => {
                    if (!this.adminMemberIds.includes(member.id)) {
                        ordinaryMembers.push({
                            id: member.id,
                            name: this.getMemberName(member),
                            avatar: this.getMemberGravatar(member)
                        });
                    }
                });
            }

            return ordinaryMembers;
        },

        /**
         * Get | Set new group members
         */
        newGroupMembers: {
            get () {
                let groupMembers = [];
                let memberIds = [];

                if (this.newGroup.members.length > 0) {
                    this.newGroup.members.map((member, index) => {
                        memberIds.push(member.id);
                    });
                }

                if (this.ordinaryMembers.length > 0) {
                    this.ordinaryMembers.map((member, index) => {
                        if (memberIds.includes(member.id)) {
                            groupMembers.push(member);
                        }
                    });
                }

                return groupMembers;
            },
            set (v) {
                let members = [];
                if (Array.isArray(v)) {
                    v.map(member => {
                        members.push(member);
                    });
                } else if (typeof v === 'object') {
                    members.push(v);
                }

                this.newGroup.members = members;
            }
        },

        /**
         * Get | Set edited group members
         */
        editedGroupMembers: {
            get () {
                let groupMembers = [];
                let memberIds = [];

                if (this.editedGroup.members.length > 0) {
                    this.editedGroup.members.map((member, index) => {
                        memberIds.push(member.id);
                    });
                }

                if (this.ordinaryMembers.length > 0) {
                    this.ordinaryMembers.map((member, index) => {
                        if (memberIds.includes(member.id)) {
                            groupMembers.push(member);
                        }
                    });
                }

                return groupMembers;
            },
            set (v) {
                let members = [];
                if (Array.isArray(v)) {
                    v.map(member => {
                        members.push(member);
                    });
                } else if (typeof v === 'object') {
                    members.push(v);
                }

                this.editedGroup.members = members;
            }
        },
    },
	methods: {
        addGroup ()
        {
            this.newGroup = {
                id: 0,
                community_id: this.community.id,
                name: '',
                description: '',
                publish: 0,
                members: []
            };

            this.addConfirm = true;

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'groupShow',
                v: 'add'
            });
        },

        editGroup (group)
        {
            this.editedGroup = group;
            this.editConfirm = false;
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'groupShow',
                v: 'edit'
            });
        },

        close ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'groupShow',
                v: 'list'
            });
        },

        /**
         * Show or hide the dropdown
         */
        showDropdown (group)
        {
            return (typeof group.dropdown !== 'undefined' && group.dropdown) ? 'dropdown is-active' : 'dropdown';
        },

        /**
         * Show or hide the dropdown
         */
        toggleDropdown (group)
        {
            this.$store.commit('toggleGroupDropdownItem', group);
        },

        /**
         * When we click outside of the dropdown, close it
         */
        onClickOutside ()
        {
            this.$store.commit('resetGroupDropdownItem');
        },

        async deleteGroup (group)
        {
            await this.$store.dispatch('DELETE_GROUP', group);
        },

        /**
         * When we input new group name
         */
        inputNewGroupName ()
        {
            if (this.newGroup.name !== '') {
                this.addConfirm = false;
            } else {
                this.addConfirm = true;
            }
        },

        async saveAddGroup ()
        {
            await this.$store.dispatch('SAVE_GROUP', this.newGroup);
            this.close();
        },

        /**
         * When we input edit group name
         */
        inputEditGroupName ()
        {
            if (this.editedGroup.name !== '') {
                this.editConfirm = false;
            } else {
                this.editConfirm = true;
            }
        },

        async saveEditGroup ()
        {
            await this.$store.dispatch('SAVE_GROUP', this.editedGroup);
            this.close();
        },
	},
    directives: {
        'click-outside': {
            bind: function(el, binding, vNode) {
                // Provided expression must evaluate to a function.
                if (typeof binding.value !== 'function') {
                    const compName = vNode.context.name
                    let warn = `[Vue-click-outside:] provided expression '${binding.expression}' is not a function, but has to be`
                    if (compName) { warn += `Found in component '${compName}'` }
                    // console.warn(warn)
                }
                // Define Handler and cache it on the element
                const bubble = binding.modifiers.bubble
                const handler = (e) => {
                    if (bubble || (!el.contains(e.target) && el !== e.target)) {
                        binding.value(e)
                    }
                }
                el.__vueClickOutside__ = handler

                // add Event Listeners
                document.addEventListener('click', handler)
            },

            unbind: function(el, binding) {
                // Remove Event Listeners
                document.removeEventListener('click', el.__vueClickOutside__)
                el.__vueClickOutside__ = null
            }
        }
    }
}
</script>

<style scoped>
    
</style>
