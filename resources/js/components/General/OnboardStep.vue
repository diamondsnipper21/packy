<template>
    <div class="onboard-step">
        <div class="step-header" :class="curStep == step ? 'active' : ''" @click="toggle">
            <div class="icon-box" :class="checked ? 'checked' : ''">
                <font-awesome-icon :icon="['fas', 'circle-check']" v-if="checked" />
                <font-awesome-icon v-else :icon="['far', 'circle']" />
            </div>
            <slot name="title" />
        </div>
        <slot v-if="curStep == step" />
    </div>
</template>

<script>
import { CommunityOnboardSteps } from '../../data/enums';
export default {
    name: 'OnboardStep',
    components: {
    },
    props: {
        curStep: {
            type: Number,
            default: CommunityOnboardSteps.COVER_IMAGE
        },
        step: {
            type: Number,
            default: CommunityOnboardSteps.COVER_IMAGE
        },
        checked: {
            type: Boolean,
        },
    },
    data() {
        return {
            CommunityOnboardSteps,
        };
    },
    computed: {
    },
    methods: {
        toggle() {
            this.$emit('changeStep', this.step)
        }
    }
}
</script>
<style lang="scss" scoped>
.onboard-step {
    margin-top: 1em;

    .step-header {
        display: flex;
        cursor: pointer;

        &.active {
            font-weight: 500;
        }

        .icon-box {
            color: #4a4a4a;
            margin-right: 0.5em;

            &.checked {
                color: #9198ff;
            }
        }
    }
}
</style>