<template>
  <Collapse class="box p0">
    <template #trigger="props">
      <div class="onboard-header">
        <div class="flex jcb align-items-center " role="button">
          <p class="step-title">
            {{ $t('community.onboarding.header.welcome') }}
          </p>
        </div>
        <p>
          {{ $t('community.onboarding.header.description') }}
        </p>
        <div class="progress-box mt1" role="button">
          <div class="step-status">
            {{ progressText }}
          </div>
          <Progress type="is-link" :value="completedStep" :max="numberOfSteps" class="w100" size="is-small"/>
        </div>
      </div>
    </template>

    <div class="onboard-content">
      <OnboardStep :curStep="currentStep" :step="CommunityOnboardSteps.CREATED" :checked="true">
        <template #title>
          <p> {{ $t('community.onboarding.step.created.title') }}</p>
        </template>
      </OnboardStep>

    </div>
  </Collapse>
</template>

<script>
import OnboardStep from "./General/OnboardStep.vue";
import {CommunityOnboardSteps} from "../data/enums";

export default {
  name: 'OnboardingStripe',
  components: {OnboardStep},
  data() {
    return {
      CommunityOnboardSteps,
      numberOfSteps: 5,
      currentStep: 1,
      completedStep: 0,
      isOpen: false,
      progressText: 'progressText'
    };
  },
}
</script>

<style scoped>

</style>
