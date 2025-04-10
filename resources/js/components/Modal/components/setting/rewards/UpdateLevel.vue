<template>
    <div>
        <div class="tab-content-title mb-05">
            {{ $t('community.members.setting-modal.admin-settings.rewards.edit-level') }}
        </div>

        <div class="mt1">
            <div class="flex mt1">
                <!-- Name -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.create-modal.group-name') }}
                    </p>
                    <input class="input" required v-model="level.name" />
                </div>
            </div>
            <div class="mt1 text-left mb1 flex">
                <button class="button is-medium community-blue-btn" :class="updateClass" :disabled="loading"
                    @click="handleUpdate">
                    {{ $t('common.save') }}
                </button>
                <button class="button is-medium ml1" :disabled="loading" @click="handleCancel">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

        <div class="mb05">
            {{
                !level.classrooms?.length ?
                    $t('community.members.setting-modal.admin-settings.rewards.no_classroom_unlock') :
                    $t('community.members.setting-modal.admin-settings.rewards.classroom_unlock', {
                        count: level.classrooms?.length
                    })
            }}
        </div>

        <div v-if="level.classrooms?.length" class="w100 pl1">
            <div v-for="item in level.classrooms" :key="item.id" class="w100">
                <div class="tab-content-item">
                    <div class="tab-content-item-content">
                        <div class="tab-content-title-without-ellipsis font-weight-600 mr1">
                            {{ item.title }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt2 mb1">
            {{ $t('community.members.setting-modal.admin-settings.rewards.level-desc') }}
        </div>
    </div>
</template>

<script>
import { RewardsSectionType } from "../../../../../data/enums";
export default {
    name: 'UpdateLevel',
    components: {
    },
    props: {
        changeSection: {
            type: Function
        },
    },
    data() {
        return {
        };
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },
        level() {
            return this.$store.state.community.rewardLevel;
        },
        loading() {
            return this.$store.state.community.loading;
        },
    },
    mounted() {
        this.loadLevel();
    },
    methods: {
        loadLevel() {
            this.$store.dispatch('GET_REWARD_LEVEL', {
                communityId: this.community.id,
                id: this.level.id,
            });
        },

        async handleUpdate() {
            await this.$store.dispatch('UPDATE_REWARD_LEVEL', {
                communityId: this.community.id,
                id: this.level.id,
                name: this.level.name
            });
            this.changeSection(RewardsSectionType.OVERVIEW)
        },

        handleCancel() {
            this.changeSection(RewardsSectionType.OVERVIEW)
        },

        /**
         * Returns button status class
         */
        updateClass() {
            return this.loading ? ' is-loading' : '';
        },
    }
}
</script>

<style scoped>
.card-selection {
    display: flex;
    justify-content: space-between;
}
</style>
