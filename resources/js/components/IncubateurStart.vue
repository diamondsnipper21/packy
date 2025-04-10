<template>
    <div class="container pl-0 pr-0">

        <div class="w100 flex align-items-start jc mt1">
            <div v-for="step, i in incubateurStartSteps" :key="step.id" class="flex-1 w25" :class="getStepClass(step)">
                <div class="flex align-items-center" :class="justifyContentClass(step)">
                    <div v-if="step.id > 1" class="step-bar"></div>
                    <div v-if="step.completed" class="step-circle">
                        <font-awesome-icon :icon="['fas', 'check']" class="font-14px font-weight-600" />
                    </div>
                    <div v-else class="step-circle">
                        {{ step.id }}
                    </div>
                    <div v-if="step.id < incubateurStartSteps.length" class="step-bar"></div>
                </div>

                <div class="step-label">
                    {{ $t(`community.incubateur.step-${step.id}.title`) }}
                </div>
            </div>
        </div>

        <div class="step-content-section">
            <div class="incubateur-card" :class="currentStep.id !== IncubateurStartStep.SECOND_STEP ? 'card' : 'pt-0'">
                <div class="step-content">
                    <div v-if="currentStep.id === IncubateurStartStep.FIRST_STEP">
                        <SignupElements />
                        <div class="p-0-5">
                            <button
                                class="button is-medium w100 community-blue-btn text-uppercase mt1"
                                @click="finishFirstStep"
                                :disabled="disabledConfirm"
                            >{{ $t('signup.btn') }}</button>

                            <div class="mt1 font-16px" v-html="$t('signup.terms-desc', { termLink: termLink, privacyLink: privacyLink })"></div>

                            <div class="mt1 font-18px font-weight-600">
                                {{ $t('signup.desc') }}
                                <span class="log-in-link" @click="showLogin">
                                    {{ $t('login.btn') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!--
                    <div v-else-if="currentStep.id === IncubateurStartStep.SECOND_STEP" class="p-0-5">
                        <div v-if="welcomeVideo !== ''" class="welcome-video-container" :class="welcomeVideo.includes('youtube') || welcomeVideo.includes('vimeo') ? 'iframe-container' : ''">
                            <iframe
                                v-if="welcomeVideo.includes('youtube')"
                                :src="'https://www.youtube.com/embed/' + welcomeVideo.replace('youtube-', '')"
                                width="100%" height="100%" frameborder="0"></iframe>
                            <iframe
                                v-else-if="welcomeVideo.includes('vimeo')"
                                :src="'https://player.vimeo.com/video/' + welcomeVideo.replace('vimeo-', '') + '?autoplay=0&loop=false&controls=true'"
                                width="100%" height="100%" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" webkitallowfullscreen mozallowfullscreen allowfullscreen referrerpolicy="strict-origin"></iframe>
                            <video
                                v-else :key="welcomeVideo" controls>
                                <source :src="welcomeVideo" type="video/mp4" />
                            </video>
                        </div>

                        <div class="mt2 text-right">
                            <button
                                class="button is-medium community-blue-btn text-uppercase next-step-btn"
                                @click="finishSecondStep"
                            >{{ $t('general.next-step') }}</button>
                        </div>
                    </div>-->

                    <div v-else-if="currentStep.id === IncubateurStartStep.THIRD_STEP" class="p-0-5">
                        <NewCommunity />
                    </div>

                    <div v-else-if="currentStep.id === IncubateurStartStep.FORTH_STEP" class="p-0-5 flex jc">
                        <div class="start-step-content-4">
                            <div class="start-step-content-4-benifit">
                                <font-awesome-icon :icon="['fas', 'check']" class="start-step-content-4-benifit-icon" />
                                <div class="text-left">
                                    {{ $t('community.incubateur.step-4.benifit-1') }}
                                </div>
                            </div>
                            <div class="start-step-content-4-benifit">
                                <font-awesome-icon :icon="['fas', 'check']" class="start-step-content-4-benifit-icon" />
                                <div class="text-left">
                                    {{ $t('community.incubateur.step-4.benifit-2') }}
                                </div>
                            </div>
                            <div class="start-step-content-4-benifit">
                                <font-awesome-icon :icon="['fas', 'check']" class="start-step-content-4-benifit-icon" />
                                <div class="text-left">
                                    {{ $t('community.incubateur.step-4.benifit-3') }}
                                </div>
                            </div>
                            <div class="start-step-content-4-benifit">
                                <font-awesome-icon :icon="['fas', 'check']" class="start-step-content-4-benifit-icon" />
                                <div class="text-left">
                                    {{ $t('community.incubateur.step-4.benifit-4') }}
                                </div>
                            </div>

                            <div v-if="access !== MemberAccess.BANNED" class="mt2 text-left">
                                <button class="button is-medium community-blue-btn text-uppercase w100" :class="button"
                                    @click="joinMembershipRequest">
                                    {{ $t('community.community.summary.join-group') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="right-desc-section" v-if="currentStep.id !== IncubateurStartStep.SECOND_STEP">
                <div v-for="index in rightDescIndex" :key="index" class="start-step-content-4-benifit">
                    <font-awesome-icon :icon="['fas', 'check']" class="start-step-content-4-benifit-icon" />
                    <div class="text-left">
                        {{ $t(`community.incubateur.right-desc.desc-${index}`) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import SignupElements from "./General/Signup/SignupElements.vue";
import NewCommunity from "./Modal/NewCommunity.vue";
import { IncubateurStartStep, MemberAccess } from '../data/enums';

export default {
    name: 'IncubateurStart',
    components: {
        SignupElements,
        NewCommunity
    },
    data () {
        return {
            steps: [{
                id: IncubateurStartStep.FIRST_STEP,
                completed: 0,
                current: 1
            }, {
                id: IncubateurStartStep.SECOND_STEP,
                completed: 0,
                current: 0
            }, {
                id: IncubateurStartStep.THIRD_STEP,
                completed: 0,
                current: 0
            }, {
                id: IncubateurStartStep.FORTH_STEP,
                completed: 0,
                current: 0
            }],

            rightDescIndex: [1, 2, 3, 4, 5, 6, 7, 8],

            welcomeVideo: 'https://wolfeo.s3.eu-west-1.amazonaws.com/2024/1/9/2023-03-15_19-27-16-1704788143.mp4',

            IncubateurStartStep,
            MemberAccess,

            processing: false,
            termLink: null,
            privacyLink: null
        };
    },
    async mounted ()
    {
        if (this.auth) {
            if (this.user.countActivePlans === 0) {
                // If logged in user, then set current step to IncubateurStartStep.SECOND_STEP
                this.$store.commit('setIncubateurStartStep', IncubateurStartStep.THIRD_STEP);
            } else {
                this.$store.commit('setIncubateurStartStep', IncubateurStartStep.FORTH_STEP);
            }
        }

        this.termLink = '<a href="/legal/terms" target="_blank">' + this.$t('signup.terms-conditions') + '</a>';
        this.privacyLink = '<a href="/legal/privacy" target="_blank">' + this.$t('signup.privacy-policy') + '</a>';

        await this.$store.dispatch('REGISTER_VISITOR');
    },
    computed: {
        /**
         * Return auth status
         */
        auth ()
        {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },

        /**
         * Returns Incubateur Start Step
         */
        incubateurStartStep ()
        {
            return this.$store.state.community.incubateurStartStep;
        },

        /**
         * Returns Incubateur Start Steps
         */
        incubateurStartSteps ()
        {
            this.steps = this.steps.map(step => {
                step.completed = 0;
                step.current = 0;

                if ((step.id === this.incubateurStartStep) || (this.incubateurStartStep === IncubateurStartStep.COMPLETED && step.id === IncubateurStartStep.FORTH_STEP)) {
                    step.current = 1;
                }

                if (step.id < this.incubateurStartStep) {
                    step.completed = 1;
                }

                return { ...step };
            });

            return this.steps;
        },

        /**
         * Returns current member
         */
        currentMember ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns member existence
         */
        memberExist ()
        {
            return (typeof this.currentMember.id !== 'undefined' && parseInt(this.currentMember.id) > 0) ? true : false;
        },

        /**
         * Returns access of member
         */
        access ()
        {
            return this.memberExist ? this.currentMember.access : null;
        },

        /**
         * Returns count of active subscription
         */
        activeSubscriptionCnt ()
        {
            return this.memberExist ? this.currentMember.activeSubscriptionCnt : 0;
        },

        /**
         * Returns count of active subscription
         */
        activeCommunityCnt ()
        {
            return this.memberExist ? this.currentMember.activeCommunityCnt : 0;
        },

        /**
         * Returns button status class
         */
        button ()
        {
            return this.processing ? ' is-loading' : '';
        },

        /**
         * Returns current step
         */
        currentStep ()
        {
            let currentStep = null;
            for (let i = 0; i < this.steps.length; i++) {
                const step = this.steps[i];
                if (step.current === 1) {
                    currentStep = step;
                    break;
                }
            }

            return currentStep;
        },

        /**
         * Returns user
         */
        user ()
        {
            return this.$store.state.auth.user;
        },

        /**
         * Prevent special characters from inputing
         */
        validEmail ()
        {
            const reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
            if (reg.test(this.user.email) == false) 
            {
                return false;
            }

            return true;
        },

        /**
         * Returns save button status
         */
        disabledConfirm ()
        {
            let disabledConfirm = true;
            if (this.user.firstname !== '' && this.user.lastname !== '' && this.user.email !== '' && this.user.country !== '' && this.validEmail && this.user.password !== '') {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },
    },
    methods: {
        /**
         * Get step class
         */
        getStepClass (step)
        {
            let stepClass = '';

            if (step.completed === 1) {
                stepClass += ' completed';
            }

            if (step.current === 1) {
                stepClass += ' current';
            }

            return stepClass;
        },

        /**
         * Get justify content class
         */
        justifyContentClass (step)
        {
            let stepClass = '';
            if (step.id === 1) {
                stepClass = 'je';
            } else if (step.id > 1 && step.id < this.steps.length) {
                stepClass = 'jc';
            }

            return stepClass;
        },

        async joinMembershipRequest()
        {
            if (this.auth) {
                this.$store.commit('setIncubateurStartStep', IncubateurStartStep.COMPLETED);

                this.processing = true;

                await new Promise((resolve)=>setTimeout(() => { resolve(); }, 2000));
                await this.$store.dispatch('JOIN_TO_COMMUNITY', { incubateurStart: true });

                this.processing = false;
            } else {
                this.$store.commit('setIncubateurStartStep', IncubateurStartStep.FIRST_STEP);
            }
        },

        /**
         * Finish first step
         */
        async finishFirstStep()
        {
            await this.$store.dispatch('SIGN_UP', {
                firstname: this.user.firstname,
                lastname: this.user.lastname,
                country: this.user.country,
                email: this.user.email,
                password: this.user.password,
                communityId: this.community ? this.community.id : null,
                incubateurStart: true
            });

            if (this.auth) {
                // this.$store.commit('setIncubateurStartStep', IncubateurStartStep.SECOND_STEP);
                this.$store.commit('setIncubateurStartStep', IncubateurStartStep.THIRD_STEP);
            }
        },

        /**
         * Finish second step
         */
        finishSecondStep (stepId)
        {
            if (this.activeSubscriptionCnt > 0 || this.activeCommunityCnt > 0) {
                this.$store.commit('setIncubateurStartStep', IncubateurStartStep.FORTH_STEP);
            } else {
                this.$store.commit('setCommunityLoading', false);
                this.$store.commit('setIncubateurStartStep', IncubateurStartStep.THIRD_STEP);
            }
        },

        /**
         * Show login modal
         */
        showLogin ()
        {
            this.$store.commit('hideModal');

            setTimeout(() => {
                this.$store.commit('resetUser');
                this.$store.commit('showModal', {
                    type: 'Login',
                    transparent: true
                });
            }, 500);
        },
    }
}
</script>

<style scoped>
    .step-content-section {
        display: flex;
        align-items: center;
        width: 100%;
        margin-top: 25px;
    }
    .incubateur-card {
        padding: 20px;
        width: 100%;
    }
    .right-desc-section {
        padding: 35px;
        width: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 400px;
    }

    .step-circle {
        height: 40px;
        width: 40px;
        border-radius: 50%;
        background-color: #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 5px;
        margin-right: 5px;
        font-weight: 500;
    }

    .current .step-circle {
        border: 2px solid #7957d5;
    }

    .completed .step-circle {
        background-color: #7957d5;
        color: #fff;
    }

    .step-bar {
        height: 5px;
        width: calc(50% - 25px);
        background-color: #ddd;
    }

    .current .step-bar {
        background-color: #7957d5;
    }

    .completed .step-bar {
        background-color: #7957d5;
    }

    .step-label {
        text-align: center;
        margin-top: 8px;
        font-size: 16px;
        font-weight: 500;
    }

    .step-content {
        text-align: center;
        padding: 15px;
    }

    .log-in-link {
        margin-left: 10px;
        cursor: pointer;
        color: #7957d5;
    }
    .log-in-link:hover {
        text-decoration: underline;
    }

    .welcome-video-container {
        margin-top: 10px;
        border: 1px solid rgb(228, 228, 228);
        display: block;
        cursor: pointer;
        border-radius: 15px;

        iframe {
            width: 100%;
            height: 100%;
            border-radius: 15px;
            display: block;
        }

        video {
            width: 100%;
            height: 100%;
            border-radius: 15px;
            display: block;
        }
    }

    .start-step-content-4 {
        padding: 15px;
        width: 550px;
    }

    .start-step-content-4-benifit {
        display: flex;
        align-items: center;
        padding: 5px;
    }

    .start-step-content-4-benifit-icon {
        color: #7957d5;
        font-size: 15px;
        font-weight: 600;
        margin-right: 15px;
    }

    .next-step-btn {
        min-width: 200px;
    }

    @media only screen and (max-width: 600px)
    {
        .step-content-section {
            margin-top: 15px;
            display: block;
        }

        .incubateur-card {
            padding: 10px;
            margin-top: 10px;
        }

        .right-desc-section {
            padding: 10px;
        }

        .step-circle {
            height: 30px;
            width: 30px;
        }

        .step-bar {
            height: 5px;
            width: calc(50% - 20px);
        }

        .step-content {
            padding: 10px;
        }

        .start-step-content-4 {
            padding: 10px;
            width: 500px;
        }

        .welcome-video-container {
            border-radius: 10px;

            iframe {
                border-radius: 10px;
            }

            video {
                border-radius: 10px;
            }
        }

        .start-step-content-4-benifit-icon {
            font-size: 14px;
            margin-right: 10px;
        }

        .next-step-btn {
            width: 100%;
        }
    }
</style>
