<template>
    <div>
        <div class="flex align-items-center">
            <div class="font-weight-600">
                {{ $t('common.email') }}:
            </div>
            <div class="ml-05">
                {{ userEmail }}
            </div>
        </div>

        <div class="flex align-items-center mt1">
            <div class="font-weight-600">
                {{ $t('community.members.role') }}:
            </div>
            <div class="ml-05">
                {{ $t(`community.members.role-options.${selectedMember.role}`) }}
            </div>
            <div v-if="allowedMemberChecked" class="ml1 go-back-link font-14px" @click="changeMembershipRole">
                ({{ $t('common.change') }})
            </div>
        </div>

        <div v-if="allowedMemberChecked" class="mt1">
            <div class="membership-info">
                <div v-if="selectedMember.online">
                    <font-awesome-icon icon="fa fa-circle" class="mr1 online-now" />
                    {{ $t('community.members.online-now') }}
                </div>
                <div v-else>
                    <font-awesome-icon icon="fa fa-clock" class="mr1" />
                    {{ $t('community.members.active') }} {{ getDiffTimeFromNow(selectedMember.last_activity) }}
                </div>
            </div>
            <div class="membership-info">
                <font-awesome-icon icon="fa fa-calendar" class="mr1" />
                {{ $t('community.members.joined') }} {{ getMemberJoinedInfo(selectedMember) }}
            </div>
            <div class="membership-info">
                <font-awesome-icon icon="fa fa-location" class="mr1" />
                {{ getCountryTxt(selectedMember.country) }}
            </div>
        </div>
        <div v-else-if="bannedMemberChecked" class="mt1">
            <div class="membership-info">
                <font-awesome-icon icon="fa fa-calendar" class="mr1" />
                {{ $t('community.members.banned') }} {{ getMemberBannedInfo(selectedMember) }}
            </div>
        </div>

        <div v-if="allowedMemberChecked" class="mt1">
            <div class="membership-action-link" @click="removeFromGroup">
                {{ $t('community.members.remove-from-group') }}
            </div>

            <div class="membership-action-link" @click="banFromGroup">
                {{ $t('community.members.ban-from-group') }}
            </div>
        </div>
        <div v-else-if="bannedMemberChecked" class="mt1">
            <div class="membership-action-link" @click="unbanToGroup">
                {{ $t('community.members.unban-to-group') }}
            </div>
        </div>

      <div class="membership-action-link" @click="cancelSubscription" v-if="selectedMember.subscription_status && selectedMember.subscription_status !== 'cancelled'">
        {{ $t('community.members.cancel-subscription') }}
      </div>
    </div>
</template>

<script>

import moment from 'moment'
import { MemberAccess, MembershipSettingViewType } from '../../../../data/enums';
import countries from '../../../../data/countries';
import getMemberName from "../../../../mixins/util";

export default {
    name: 'Membership',
    mixins: [
        getMemberName
    ],
    data() {
        return {
            MemberAccess,
            MembershipSettingViewType,
            countries: [],
        };
    },
    created() {
        moment.locale(this.$i18n.locale);
        this.countries = countries;
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

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
         * Return the user's email
         */
        userEmail() {
            return this.selectedMember.user.email;
        },

        /**
         * Check allowed membership role
         */
        allowedMemberChecked() {
            return this.selectedMember?.access === MemberAccess.ALLOWED;
        },

        /**
         * Check banned membership role
         */
        bannedMemberChecked() {
            return [MemberAccess.BANNED].includes(this.selectedMember?.access);
        },
    },
    methods: {
        /**
         * Go to membership role seletion
         */
        changeMembershipRole ()
        {
            this.$store.commit('setModalExtraData', MembershipSettingViewType.MEMBERSHIP_ROLE);
        },

        /**
         * Calculate diff time from now
         */
        getDiffTimeFromNow(date) {
            return moment(date).locale(this.$i18n.locale).fromNow();
        },

        /**
         * Get member joined info
         */
        getMemberJoinedInfo(member) {
            return moment(member.created_at).format("MMM Do, YYYY");
        },

        /**
         * Get member banned info
         */
        getMemberBannedInfo(member) {
            return moment(member.has_updated_at).format("MMM Do, YYYY");
        },

        /**
         * Get country text
         */
        getCountryTxt(value) {
            let countryTxt = '-';
            let selectedCountries = this.countries.filter(el => el.value === value);
            if (selectedCountries.length === 1) {
                countryTxt = selectedCountries[0].text;
            }

            return countryTxt;
        },

        removeFromGroup() {
            this.$store.commit('showChildModal', {
                modalType: 'Confirm',
                extraData: {
                    title: this.$t('community.members.delete-member-title'),
                    desc: this.$t('community.members.delete-member-desc').replace("#memberName#", this.getMemberName(this.selectedMember)).replace("#communityName#", this.community.name),
                    buttonText: this.$t('common.remove'),
                    action: 'REMOVE_MEMBER',
                    param: this.selectedMember.id,
                    hideModal: true
                }
            });
        },

        banFromGroup() {

            console.log('banFromGroup', this.selectedMember)

            this.$store.commit('showChildModal', {
                modalType: 'BanConfirm',
                extraData: {
                    title: this.$t('community.members.ban-from-group') + '?',
                    buttonText: this.$t('community.members.ban'),
                    action: 'BAN_MEMBER',
                    param: this.selectedMember.id,
                    hideModal: true
                }
            });
        },

        cancelSubscription() {
          this.$store.commit('showChildModal', {
            modalType: 'Confirm',
            extraData: {
              title: this.$t('community.members.cancel-membership.title'),
              desc: this.$t('community.members.cancel-membership.desc', {
                memberName: this.getMemberName(this.selectedMember),
                communityName: this.community.name
              }),
              buttonText: this.$t('common.cancel'),
              action: 'CANCEL_MEMBER_SUBSCRIPTION',
              param: this.selectedMember,
              hideModal: true
            }
          });
        },

        async unbanToGroup() {
            await this.$store.dispatch('BAN_MEMBER', this.selectedMember.id);
            this.$store.commit('hideModal');
        },
    }
}
</script>

<style scoped>
    .membership-info {
        padding: 3px;
    }

    .membership-action-link {
        color: #909090;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
        padding: 5px 0px;
    }

    .membership-action-link:hover {
        text-decoration: underline;
        cursor: pointer;
        color: #4a4a4a;
    }
</style>
