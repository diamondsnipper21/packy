<template>
    <div class="inner-modal-container">
        <p class="title is-4 font-weight-600">
            {{ $t('decline-join-modal.title') }}
        </p>

        <div class="mt2 font-16px" v-html="desc"></div>

        <div class="flex mt1">
            <!-- Description -->
            <div class="flex-1">
                <p class="text-left input-label" v-html="feedbackDesc"></p>
                <textarea
                    class="textarea"
                    :placeholder="$t('decline-join-modal.feedback-placeholder')"
                    v-model="feedback"
                    rows="5"
                />
            </div>
        </div>

        <div class="mt1 flex align-items-center je">
            <font-awesome-icon :icon="['fas', 'circle-exclamation']" class="mr-05" />

            <div class="font-14px mr-05" v-html="feedbackNotificationDesc"></div>

            <Toggle v-model="shareNotify" class="toggle-blue" />
        </div>

        <div class="mt2 text-right">
            <button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
            <button
                class="button is-medium community-blue-btn ml-05"
                :class="button"
                @click="confirmDecline"
            >{{ $t('common.confirm') }}</button>
        </div>
    </div>
</template>

<script>

import getMemberName from "../../mixins/util";

import Toggle from '@vueform/toggle'
import '@vueform/toggle/themes/default.css'

export default {
	name: 'DeclineMemberRequest',
    mixins: [
        getMemberName
    ],
    components: {
        Toggle
    },
    data () {
        return {
            desc: '',
            feedbackDesc: '',
            feedbackNotificationDesc: '',
            processing: false
        };
    },
    mounted() {
        this.desc = this.$t('decline-join-modal.desc').replace('#member_name#', this.getMemberName(this.extraData)).replace('#community_name#', this.community.name);
        this.feedbackDesc = this.$t('decline-join-modal.feedback-desc').replace('#member_name#', this.getMemberName(this.extraData));
        this.feedbackNotificationDesc = this.$t('decline-join-modal.feedback-notification-desc').replace('#member_name#', this.getMemberName(this.extraData));
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
         * extra data of child modal
         */
        extraData ()
        {
            return this.$store.state.modal.extraData;
        },

        /**
         * Get | Set feedback
         */
        feedback: {
            get () {
                return this.$store.state.communitycenter.declineJoinFeedback;
            },
            set (v) {
                this.$store.commit('setDeclineJoinFeedback', v);
            }
        },

        /**
         * Get | Set shareNotify
         */
        shareNotify: {
            get () {
                return this.$store.state.communitycenter.declineJoinShareNotify;
            },
            set (v) {
                this.$store.commit('setDeclineJoinShareNotify', v);
            }
        },

        /**
         * Returns button status class
         */
        button ()
        {
            return this.processing ? ' is-loading' : '';
        },
    },
    methods: {

        /**
         * Open new post section
         */
        cancel ()
        {
            this.$store.commit('hideModal');
        },

        /**
         * Add community post
         */
        async confirmDecline()
        {
            this.processing = true;
            await this.$store.dispatch('DECLINE_MEMBER_REQUEST', {
                memberId: this.extraData.id,
                feedback: this.feedback,
                shareNotify: this.shareNotify
            });
            this.processing = false;
            this.cancel();
        }
    }
}
</script>

<style scoped>

</style>
