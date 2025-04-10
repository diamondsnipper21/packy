<template>
  <div style="text-align: center; border: 1px solid #eee; padding: 30px; border-radius: 5px;">
    <div>Two-factor Authentication</div>
    <div class="2fa-area mt1" :class="success === false ? 'wrong-code' : ''" style="display:flex; gap:10px; justify-content: center;">
      <input v-model="code1" id="code1" pattern="[0-9]{1}" min="0" max="9" maxlength="1" @input="input($event, 2)" :disabled="processing"/>
      <input v-model="code2" id="code2" pattern="[0-9]{1}" min="0" max="9" maxlength="1" @input="input($event,3)" :disabled="processing"/>
      <input v-model="code3" id="code3" pattern="[0-9]{1}" min="0" max="9" maxlength="1" @input="input($event,4)" :disabled="processing"/>
      <input v-model="code4" id="code4" pattern="[0-9]{1}" min="0" max="9" maxlength="1" @input="input($event,5)" :disabled="processing"/>
      <input v-model="code5" id="code5" pattern="[0-9]{1}" min="0" max="9" maxlength="1" @input="input($event,6)" :disabled="processing"/>
      <input v-model="code6" id="code6" pattern="[0-9]{1}" min="0" max="9" maxlength="1" @input="input($event, 1)" :disabled="processing"/>
      <div v-if="processing" style="position: absolute; padding: 10px; font-size: 24px; background: rgba(255,255,255,0.7); width: 100%; color: #777">
        <font-awesome-icon icon="fa fa-circle-notch" class="link-icon fa-spin" />
      </div>
    </div>

    <div class="mt2 font-14px">
      A message with a verification code has been sent to your email.<br/>
      Enter the code to continue.
    </div>
    <div class="mt1 font-14px">
      <a href="#" @click="sendCodeAgain()">Didn't get a verification code ?</a>
    </div>
  </div>
</template>

<script>
import {notify} from "@kyvg/vue3-notification";

export default {
  name: 'VerificationCode',
  props: {
    code: String,
    action: String
  },
  data() {
    return {
      code1: null,
      code2: null,
      code3: null,
      code4: null,
      code5: null,
      code6: null,
      processing: false,
      success: null
    }
  },
  computed: {
    verificationCode() {
      return this.$store.state.communitycenter.verificationCode;
    },
    fullCode() {
      return this.code1 + '' + this.code2 + '' + this.code3 + '' + this.code4 + '' + this.code5 + '' + this.code6;
    },
  },
  methods: {
    input($event, num) {
      let value = parseInt($event.data);
      if (!Number.isInteger(value)) {
        this[`code${(num-1)}`] = null;
        return;
      }

      this.focus(num)
      this.checkCode();
    },

    focus(num) {
      document.getElementById("code" + num).focus();
    },

    async checkCode() {
      if (this.code1 === null
          || this.code2 === null
          || this.code3 === null
          || this.code4 === null
          || this.code5 === null
          || this.code6 === null) {
        return;
      }

      this.processing = true;
      let self = this;

      await this.$store.dispatch('TWO_FACTOR_AUTHENTICATION_CHECK', {
        code: this.fullCode,
        send_again: false
      });

      self.reset();
      self.success = false;
      self.processing = false;

      if (this.verificationCode === null && this.action) {
        await this.$store.dispatch(this.action);
      }
    },

    reset() {
      this.code1 = null;
      this.code2 = null;
      this.code3 = null;
      this.code4 = null;
      this.code5 = null;
      this.code6 = null;

      let self = this;
      setTimeout(function () {
        self.focus(1);
      }, 1);

      setTimeout(function () {
        self.success = null
      }, 500);
    },

    async sendCodeAgain() {
      await this.$store.dispatch('TWO_FACTOR_AUTHENTICATION_CHECK', {
        code: this.fullCode,
        send_again: true
      });

      notify({
        text: 'A new verification code has been sent to your email.',
        type: 'success'
      });
    }
  }
}
</script>

<style scoped>
  input {
    width: 50px;
    height: 55px;
    font-size: 25px;
    border: 1px solid #eee;
    text-align: center;
    border-radius: 4px;
  }

  @keyframes horizontal-shaking {
    0% { transform: translateX(0) }
    25% { transform: translateX(5px) }
    50% { transform: translateX(-5px) }
    75% { transform: translateX(5px) }
    100% { transform: translateX(0) }
  }

  .wrong-code {
    animation: horizontal-shaking 0.35s ease;
  }
</style>