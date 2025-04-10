<template>
    <div>
        <div class="tab-content-title">
            {{ $t('community.members.select-membership-role') }}
        </div>

        <div class="mt1">
            <div
                v-for="(item) in membershipRoles"
                :key="item.id"
                class="radio-select-item"
                :class="memberRoleClass(item.id)"
                @click="selectMemberRoleOption(item.id)"
            >
                <div class="flex align-items-center pointer">
                    <input
                        type="radio"
                        :id="item.id"
                        v-model="item.selected"
                        :value="selected"
                        class="mr-05"
                    />
                    <label class="text-left input-label w100 pointer">
                        {{ $t(`community.members.role-options.${item.id}`) }}
                    </label>
                </div>
            </div>
        </div>

        <div class="flex mt2">
            <button class="button is-medium community-blue-btn mr-05" @click="saveMembershipRole">
                {{ $t('common.change') }}
            </button>
            <button class="button is-medium community-btn" @click="close">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>

import { MemberRole, MembershipSettingViewType } from '../../../../data/enums';

export default {
    name: 'MembershipRole',
    data() {
        return {
            selected: 1,
            MemberRole,
            MembershipSettingViewType
        };
    },
    computed: {
        /**
         * Returns membership settings
         */
        membershipSettings() {
            return this.$store.state.member.membershipSettings;
        },

        /**
         * Returns selected member
         */
        selectedMember() {
            return this.membershipSettings?.member;
        },

        /**
         * Returns membership roles
         */
        membershipRoles ()
        {
            let membershipRoles = [MemberRole.MEMBER, MemberRole.MODERATOR, MemberRole.ADMIN];
            membershipRoles = membershipRoles.map(role => {
                let item = {
                    id: role,
                    selected: 0
                };
                if (item.id === this.selectedMember.role) {
                    item.selected = 1;
                }

                return { ...item };
            });

            return membershipRoles;
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers() {
            return this.$store.state.communitycenter.allowedMembers;
        },
    },
    methods: {
        /**
         * Returns selected from member class
         */
        memberRoleClass (id)
        {
            let memberRoleClass = '';
            if (id === this.selectedMember.role) {
                memberRoleClass += ' selected-item';
            }

            return memberRoleClass;
        },

        /**
         * Select media option
         */
        selectMemberRoleOption (v)
        {
            this.$store.commit('setMembershipSettingsMemberProperty', {
                key: 'role',
                v
            });
        },

        /**
         * Go to membership page
         */
        goToMembership ()
        {
            this.$store.commit('setModalExtraData', MembershipSettingViewType.MEMBERSHIP);
        },

        /**
         * Change membership role
         */
        async saveMembershipRole ()
        {
            this.$store.commit('setMembershipSettingsProperty', {
                key: 'contentLoading',
                v: true
            });

            console.log('save member', this.selectedMember)
            await this.$store.dispatch('SAVE_MEMBER', this.selectedMember);

            this.$store.commit('setMembershipSettingsProperty', {
                key: 'contentLoading',
                v: false
            });

            this.goToMembership();
        },

        /**
         * Close membership role select page
         */
        close ()
        {
            let selectedMember = {};
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            if (members.length > 0) {
                for (let i = 0; i < members.length; i++) {
                    const member = members[i];
                    if (this.selectedMember.id === member.id) {
                        selectedMember = member;
                        break;
                    }
                }
            }

            this.$store.commit('setMembershipSettingsProperty', {
                key: 'member',
                v: selectedMember
            });

            this.goToMembership();
        },

    }
}
</script>

<style scoped>

</style>
