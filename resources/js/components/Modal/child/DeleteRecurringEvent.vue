<template>
    <div>
        <p class="title is-4 font-weight-600">
            {{ $t('community.calendar.delete-event-modal.title') }}
        </p>

        <p class="mt1">
            {{ $t('community.calendar.delete-event-modal.desc') }}
        </p>

        <div class="flex jc mt2">
            <button
                class="button community-blue-btn mr-05"
                @click="deleteOneEvent"
            >{{ $t('community.calendar.delete-event-modal.btn-1') }}</button>

            <button
                class="button community-red-btn mr-05"
                @click="deleteAllEvent"
            >{{ $t('community.calendar.delete-event-modal.btn-2') }}</button>

            <button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'DeleteRecurringEvent',
    computed: {
        /**
         * extra data of modal
         */
        extraData ()
        {
            return this.$store.state.modal.childModalExtraData;
        }
    },
    methods: {
        cancel() {
            this.$store.commit('hideChildModal');
        },

        async deleteOneEvent() {
            this.$store.commit('setChildModalExtraDataProperty', {
                key: 'change_to_all',
                v: 0
            });

            await this.$store.dispatch('DELETE_EVENT', this.extraData);
            
            this.cancel();
        },

        async deleteAllEvent() {
            this.$store.commit('setChildModalExtraDataProperty', {
                key: 'change_to_all',
                v: 1
            });

            await this.$store.dispatch('DELETE_EVENT', this.extraData);
            
            this.cancel();
        },
    }
}
</script>
