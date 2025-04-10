<template>
	<div class="inner-modal-container">
        <p class="title is-4 font-weight-600">
            {{ $t('join-modal.title') }}
        </p>

        <div class="mt1 font-16px">
            {{ $t('join-modal.desc').replace("#community_name#", community.name) }}
        </div>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
            :class="button"
            @click="JoinGroup"
        >{{ $t('join-modal.title') }}</button>
	</div>
</template>

<script>
export default {
	name: 'Join',
    data () {
        return {
            processing: false
        };
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
         * Returns button status class
         */
        button ()
        {
            return this.processing ? ' is-loading' : '';
        },
    },
	methods: {
        async JoinGroup()
        {
            this.processing = true;

            await new Promise((resolve)=>setTimeout(() => { resolve(); }, 2000));

            await this.$store.dispatch('JOIN_TO_COMMUNITY');

            this.close();

            this.processing = false;
        },

        /**
         * Close the modal
         */
        close ()
        {
            this.$store.commit('hideModal');
        }
	}
}
</script>

<style scoped>
    .home-logo {
        height: 40px;
    }

    .action-link {
        cursor: pointer;
        color: #7957d5;
    }

    .action-link:hover {
        text-decoration: underline;
    }
</style>
