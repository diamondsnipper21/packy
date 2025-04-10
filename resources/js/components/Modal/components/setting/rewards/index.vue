<template>
    <div>
        <RewardsOverview v-if="section == RewardsSectionType.OVERVIEW" :changeSection="handleSection" />
        <UpdateLevel v-if="section == RewardsSectionType.UPDATE_LEVEL" :changeSection="handleSection"/>
    </div>
</template>

<script>
import { RewardsSectionType } from "../../../../../data/enums";
import RewardsOverview from "./Overview";
import UpdateLevel from "./UpdateLevel";
export default {
    name: 'Rewards',
    components: {
        RewardsOverview,
        UpdateLevel
    },
    data() {
        return {
            RewardsSectionType,
            section: RewardsSectionType.OVERVIEW,
        };
    },
    computed: {
    },
    methods: {
        handleSection(section) {
            this.section = section
        }
    }
}
</script>

<style scoped></style>
