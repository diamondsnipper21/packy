<template>
    <Collapse class="box p0">
        <template #trigger="props">
            <div class="onboard-header">
                <div class="flex jcb align-items-center " role="button">
                    <p class="step-title">
                        {{ $t('community.onboarding.header.welcome') }}
                    </p>
                    <span>
                        <font-awesome-icon v-if="props.open" :icon="['fas', 'chevron-up']" />
                        <font-awesome-icon v-else :icon="['fas', 'chevron-down']" />
                    </span>
                </div>
                <p>
                    {{ $t('community.onboarding.header.description') }}
                </p>
                <div class="progress-box mt1" role="button">
                    <div class="step-status">
                        {{ progressText }} 
                    </div>
                    <Progress type="is-link" :value="completedStep" :max="numberOfSteps" class="w100"
                        size="is-small"></Progress>
                </div>
            </div>
        </template>
        <div class="onboard-content">
            <OnboardStep :curStep="currentStep" :step="CommunityOnboardSteps.CREATED" :checked="true">
                <template #title>
                    <p> {{ $t('community.onboarding.step.created.title') }}</p>
                </template>
            </OnboardStep>
            <!-- Cover Image -->
            <OnboardStep :curStep="currentStep" :step="CommunityOnboardSteps.COVER_IMAGE" @changeStep="handleStep"
                :checked="!!community.summary_photo">
                <template #title>
                    <p> {{ $t('community.onboarding.step.cover-image.title') }}</p>
                </template>
                <div class="step-content">
                    <div class="mainbox">
                        <p>{{ $t('community.onboarding.step.cover-image.desc') }}</p>
                        <p>
                            {{ $t('community.onboarding.step.cover-image.desc-1') }}
                            <a href="/demo">{{ $t('community.onboarding.step.cover-image.desc-2') }}</a>
                        </p>
                        <button class="button is-medium community-blue-btn" @click="handleGeneralSetting">
                            {{ $t('community.onboarding.step.cover-image.button') }}
                        </button>
                    </div>
                    <div class="sidebox">
                        <img src="/assets/img/onboarding/upload-cover-image.png" />
                    </div>
                </div>
            </OnboardStep>

            <!-- Description -->
            <OnboardStep :curStep="currentStep" :step="CommunityOnboardSteps.DESCRIPTION" @changeStep="handleStep"
                :checked="!!community.summary_description">
                <template #title>
                    <p> {{ $t('community.onboarding.step.description.title') }}</p>
                </template>
                <div class="step-content">
                    <div class="mainbox">
                        <p>{{ $t('community.onboarding.step.description.desc') }}</p>
                        <p>
                            {{ $t('community.onboarding.step.description.desc-1') }}
                            <a href="/demo">{{ $t('community.onboarding.step.description.desc-2') }}</a>
                        </p>
                        <button class="button is-medium community-blue-btn" @click="handleGeneralSetting">
                            {{ $t('community.onboarding.step.description.button') }}
                        </button>
                    </div>
                    <div class="sidebox">
                        <img src="/assets/img/onboarding/write-description.png" />
                    </div>
                </div>
            </OnboardStep>

            <!-- Invite Friends -->
            <OnboardStep :curStep="currentStep" :step="CommunityOnboardSteps.INVITE_FRIEND" @changeStep="handleStep"
                :checked="community.number_of_members > 3">
                <template #title>
                    <p> {{ $t('community.onboarding.step.invite.title') }}</p>
                </template>
                <div class="step-content">
                    <div class="mainbox">
                        <p>{{ $t('community.onboarding.step.invite.desc') }}</p>
                        <button class="button is-medium community-blue-btn" @click="handleInviteSetting">
                            {{ $t('community.onboarding.step.invite.button') }}
                        </button>
                    </div>
                    <div class="sidebox">
                        <img src="/assets/img/onboarding/invite-friend.png" />
                    </div>
                </div>
            </OnboardStep>

            <!-- Write Post -->
            <OnboardStep :curStep="currentStep" :step="CommunityOnboardSteps.POST" @changeStep="handleStep"
                :checked="!!community.number_of_posts">
                <template #title>
                    <p> {{ $t('community.onboarding.step.post.title') }}</p>
                </template>
                <div class="step-content">
                    <div class="mainbox">
                        <p>{{ $t('community.onboarding.step.post.desc') }}</p>
                        <button class="button is-medium community-blue-btn" @click="handleWritePost">
                            {{ $t('community.onboarding.step.post.button') }}
                        </button>
                    </div>
                    <div class="sidebox">
                        <img src="/assets/img/onboarding/new-post.png" />
                    </div>
                </div>
            </OnboardStep>
        </div>
    </Collapse>
</template>

<script>
import Collapse from './General/Elements/Collapse';
import Progress from './General/Elements/Progress';
import OnboardStep from './General/OnboardStep';
import { CommunityOnboardSteps } from '../data/enums';

export default {
    name: 'OnboardingCommunity',
    components: {
        Collapse,
        Progress,
        OnboardStep
    },
    data() {
        return {
            CommunityOnboardSteps,
            numberOfSteps: 5,
            isOpen: false,
        };
    },
    computed: {
        community() {
            return this.$store.state.community.data;
        },
        currentStep() {
            return this.community.onboarding_step;
        },
        completedStep() {
            return this.community.completed_steps;
        },
        progressText() {
            return this.$t('community.onboarding.header.progress')
                .replace('#cur', this.completedStep)
                .replace('#total', this.numberOfSteps)
        }
    },
    methods: {
        reInitSettings() {
            this.$store.commit('cloneCommunity', JSON.parse(JSON.stringify(this.$store.state.community.data)));
            this.$store.commit('resetDropzoneError');
        },
        handleGeneralSetting() {
            this.reInitSettings();
            this.$store.commit('showModal', {
                type: 'CommunitySetting',
                extraData: 'general',
                transparent: true
            });
        },
        handleInviteSetting() {
            this.reInitSettings();
            this.$store.commit('showModal', {
                type: 'CommunitySetting',
                extraData: 'invite',
                extraAction: 'openInviteLink',
                transparent: true
            });
        },
        handleWritePost() {
            this.$store.commit('resetCommunityPost');
            this.$store.commit('setAddPostShow', true);
        },
        handleStep(step) {
            this.$store.commit('setOnboardStep', step);
        }
    }
}
</script>

<style lang="scss" scoped>
.onboard-header {
    border-bottom: 1px solid #dbdbdb;
    padding: 1em;
    font-size: 16px;

    .step-title {
        font-weight: 500;
        font-size: 18px;
    }

    .progress-box {
        display: flex;
        align-items: center;
    }

    .step-status {
        text-wrap: nowrap;
        margin-right: 1em;
    }
}

.onboard-content {
    padding: 1em;
    font-size: 16px;

    .step-content {
        display: flex;
        justify-content: space-between;
        padding: 1em 0.5em 0em 1.5em;

        p {
            margin-bottom: 1em;
        }

        .mainbox {
            max-width: 450px;
        }

        .sidebox {
            padding: 0 0 0 1em;
            position: relative;
            top: -20px;
            width: 250px;

            img {
                border-radius: 6px;
                overflow: hidden;
                width: 100%;
            }
        }
    }
}


@media only screen and (max-width: 600px) {
    .onboard-header {
        padding: 0.75em;
        font-size: 14px;

        .step-title {
            font-size: 14px;
        }
    }

    .onboard-content {
        padding: 0.75em;
        font-size: 14px;

        .step-content {
            display: block;
            margin-left: 0;

            .sidebox {
                padding: 0;
                padding: 1em 0;
                top: 0;
                width: 100%;
            }
        }
    }
}
</style>
