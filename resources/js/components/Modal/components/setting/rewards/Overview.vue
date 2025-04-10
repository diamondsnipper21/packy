<template>
    <div class="wrapper">
        <p class="tab-content-title">
            {{ $t('community.members.setting-modal.admin-settings.level-rewards') }}
        </p>
        <p class="tab-content-desc">
            {{ $t('community.members.setting-modal.admin-settings.rewards.description') }}
        </p>

        <div v-if="loading" class="setting-content-loading"></div>
        <div v-if="!loading" class="w100 mt1">
            <div v-for="item in levels" :key="item.id" class="w100">
                <div class="tab-content-item">
                    <div class="tab-content-item-content">
                        <div class="tab-content-title-without-ellipsis font-weight-600 mr1 clickable-classroom-title">
                            {{ item.name }}
                        </div>
                        <div class="sub-description">
                            {{
                                $t('community.members.setting-modal.admin-settings.rewards.member_percent', {
                                    percent: item.member_percent
                                })
                            }} -
                            {{
                                !item.number_of_rooms ?
                                    $t('community.members.setting-modal.admin-settings.rewards.no_classroom_unlock') :
                                    $t('community.members.setting-modal.admin-settings.rewards.classroom_unlock', {
                                        count: item.number_of_rooms
                                    })
                            }}
                        </div>
                    </div>

                    <div class="tab-content-item-action">
                        <button class="button is-medium community-btn" @click="editLevel(item)">
                            {{ $t('common.edit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { RewardsSectionType } from "../../../../../data/enums";
export default {
    name: 'RewardsOverview',
    props: {
        changeSection: {
            type: Function
        },
    },
    data() {
        return {
            RewardsSectionType,

        }
    },
    mounted() {
        this.loadLevels();
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },
        levels() {
            return this.$store.state.community.levels;
        },
        loading() {
            return this.$store.state.community.loading;
        },
    },
    methods: {
        loadLevels() {
            this.$store.dispatch('GET_REWARDS_LEVELS', {
                communityId: this.community.id,
            });
        },

        editLevel(item) {
            this.$store.commit('setRewardLevel', item);
            this.changeSection(RewardsSectionType.UPDATE_LEVEL)
        }
    }
}
</script>

<style scoped>
.sub-description {
    font-size: 14px;
}
</style>
