<template>
    <div>
        <p class="title is-4 font-weight-600">
            {{ $t('community.calendar.edit-event-modal.title') }}
        </p>

        <p class="mt1">
            {{ $t('community.calendar.edit-event-modal.desc') }}
        </p>

        <div class="flex jc mt2">
            <button
                class="button community-blue-btn mr-05"
                @click="editOneEvent"
            >{{ $t('community.calendar.edit-event-modal.btn-1') }}</button>

            <button
                class="button community-red-btn mr-05"
                @click="editAllEvent"
            >{{ $t('community.calendar.edit-event-modal.btn-2') }}</button>

            <button class="button is-medium" @click="cancel">
                {{ $t('common.cancel') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'EditRecurringEvent',
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

        editOneEvent() {
            this.$store.commit('setChildModalExtraDataProperty', {
                key: 'change_to_all',
                v: 0
            });

            this.emitter.emit("editEventTrigger", this.extraData);
            
            this.cancel();
        },

        editAllEvent() {
            this.$store.commit('setChildModalExtraDataProperty', {
                key: 'change_to_all',
                v: 1
            });

            this.emitter.emit("editEventTrigger", this.extraData);
            
            this.cancel();
        },
    }
}
</script>
