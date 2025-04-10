<template>
	<div class="inner-modal-container">
        <p class="title is-4 font-weight-600">
            {{ $t('pending-modal.title') }}
        </p>

        <div class="mt1">
            <div class="font-16px">
                {{ $t('pending-modal.desc-1').replace("#community_name#", community.name) }}
            </div>
            <div class="font-16px">
                {{ $t('pending-modal.desc-2') }}
            </div>
        </div>

        <button
            class="button is-medium w100 community-blue-btn text-uppercase mt2"
            :class="button"
            @click="close"
        >{{ $t('pending-modal.btn') }}</button>
	</div>
</template>

<script>
export default {
	name: 'Pending',
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
