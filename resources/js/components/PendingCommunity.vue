<template>
    <Collapse class="box p0 mt1">
        <template #trigger="props">
            <div class="onboard-header">
                <div class="flex jcb align-items-center " role="button">
                    <p class="step-title">{{ title }}</p>
                    <div class="flex jcb align-items-center ml-05">
                        <p class="step-title mr-05" v-if="!props.open">
                            {{ progressText }}
                        </p>
                        <div>
                            <font-awesome-icon v-if="props.open" :icon="['fas', 'chevron-up']" />
                            <font-awesome-icon v-else :icon="['fas', 'chevron-down']" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div class="onboard-content">
            <p>{{ $t('community.onboarding.pending.desc') }}</p>
            <div class="message-wrapper">
                <div class="avatar-wrapper">
                    <div class="avatar">
                        <img v-if="member" :src="getMemberGravatar(member)" alt="gravatar-profile-image">
                    </div>
                </div>
                <div class="sms-box">
                    {{ $t('community.onboarding.pending.sms') }}
                    <a :href="communityUrl">{{ communityUrl }}</a>
                </div>
                <button class="button" @click="copy">
                    <font-awesome-icon :icon="['fas', 'copy']" />
                </button>
            </div>
            <div ref="reference"></div>
            <div class="progress-wrapper">
                <div class="joined-text">{{ progressText }}</div>
                <div class="unlock-progress">
                    <Progress type="is-link" :value="community.number_of_members - 1" :max="3" class="w100"
                        size="is-medium"></Progress>
                </div>
            </div>
        </div>
    </Collapse>
</template>

<script>
import Collapse from './General/Elements/Collapse';
import Progress from './General/Elements/Progress';
import OnboardStep from './General/OnboardStep';
import { CommunityOnboardSteps } from '../data/enums';
import getMemberGravatar from "../mixins/util";
import { notify } from "@kyvg/vue3-notification";

export default {
    name: 'PendingCommunity',
    components: {
        Collapse,
        Progress,
        OnboardStep
    },
    mixins: [
        getMemberGravatar
    ],
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
        communityUrl() {
            return `${window.location.origin}/${this.community.url}`
        },
        member() {
            return this.$store.state.member.data;
        },
        currentStep() {
            return this.community.onboarding_step;
        },
        title() {
            return this.$t('community.onboarding.pending.title')
                .replace('#name', this.community.name);
        },
        progressText() {
            return this.$t('community.onboarding.pending.joined') + ' ' + (this.community.number_of_members - 1) + '/' + 3
        },
    },
    methods: {
        copy() {
            const storage = document.createElement('textarea');
            storage.value = this.$t('community.onboarding.pending.sms') + this.communityUrl;
            this.$refs.reference.appendChild(storage);
            storage.select();
            storage.setSelectionRange(0, 99999);
            document.execCommand('copy');
            this.$refs.reference.removeChild(storage);
            notify({ text: this.$t('community.onboarding.pending.copied'), type: 'success' });
        },
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
        padding: 1em 0.5em 1em 1.5em;

        p {
            margin-bottom: 1em;
        }

        .sidebox {
            flex: 0 0 250px;
            margin-left: 0.5em;
        }
    }
}

.message-wrapper {
    margin: 1em 0;
    display: flex;

    .avatar-wrapper {
        display: flex;
        align-items: center;
        gap: 1em;
        flex-direction: column;

        .avatar {
            width: 50px;
            height: 50px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            flex-shrink: 0;
            user-select: none;
            border-radius: 50%;

            img {
                color: transparent;
                width: 100%;
                height: 100%;
                object-fit: cover;
                text-align: center;
            }
        }
    }

    .sms-box {
        background: #f4f5f8;
        padding: 0.5em;
        border-radius: 10px;
        border: 1px solid #dbdbdb;
        margin: 0px 0.3em 0px 0.5em;
    }

    .button {
        flex-shrink: 0;
        align-self: center;
        color: #4a4a4a;
        border: none;
        font-size: 18px;
        padding: 0.5em;
    }
}

.progress-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;

    .unlock-progress {
        width: 100%;
        padding: 1em 2.5em;

        .unlock-icon {}
    }
}

.joined-text {
    font-weight: 500;
    font-size: 18px;
}

@media only screen and (max-width: 600px) {
    .onboard-header {
        padding: 0.75em;

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
        }
    }

    .progress-wrapper {
        .unlock-progress {
            padding: 1em;
        }
    }

    .message-wrapper {
        font-size: 14px;

        .avatar-wrapper {
            .avatar {
                width: 38px;
                height: 38px;
            }
        }
    }

    .joined-text {
        font-weight: 500;
        font-size: 14px;
    }

}
</style>
