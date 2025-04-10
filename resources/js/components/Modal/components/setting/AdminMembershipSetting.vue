<template>
    <div>
        <div class="flex mt2">
            <div class="flex-1">
                <p class="text-left input-label">
                    {{ $t('community.members.setting-modal.admin-settings.post-approbation') }}
                </p>
                <div class="radio-select-section mt-0">
                    <div class="radio-select-item" :class="parseInt(autoPostApprobation) === 1 ? 'selected-item' : ''">
                        <input
                            class="mr-05"
                            type="radio"
                            v-model="autoPostApprobation"
                            value="1"
                            @click="checkAutoPostApprobation('1')"
                        />
                        <label class="text-left input-label pointer" @click="checkAutoPostApprobation('1')">
                            {{ $t('community.members.setting-modal.admin-settings.automatic') }}
                        </label>
                    </div>
                    <div class="radio-select-item" :class="parseInt(autoPostApprobation) === 0 ? 'selected-item' : ''">
                        <input
                            class="mr-05"
                            type="radio"
                            v-model="autoPostApprobation"
                            value="0"
                            @click="checkAutoPostApprobation('0')"
                        />
                        <label class="text-left input-label pointer" @click="checkAutoPostApprobation('0')">
                            {{ $t('community.members.setting-modal.admin-settings.manual') }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="submit-button mt1">
            <button class="button is-medium community-blue-btn" @click="updateSettings">
                {{ $t('community.members.setting-modal.admin-settings.update-settings') }}
            </button>
        </div>
    </div>
</template>

<script>
export default {
	name: 'AdminMembershipSetting',
    data () {
        return {
            editedLink: null,
            newLink: null,
        };
    },
    computed: {
        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.clone;
        },

        /**
         * Returns community auto_post_approbation
         */
        autoPostApprobation ()
        {
            return this.community.auto_post_approbation;
        },
    },
	methods: {

        checkAutoPostApprobation (v)
        {
            this.$store.commit('setCommunityProperty', {
                key: 'auto_post_approbation',
                v
            });
        },

        async updateSettings ()
        {
            await this.$store.dispatch('UPDATE_COMMUNITY');
        },
	}
}
</script>
