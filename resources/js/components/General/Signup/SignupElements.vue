<template>
  <div class="p-0-5">
    <img src="/assets/logo/packie-logo.png" class="home-logo"/>

    <p class="title is-4 mt1 font-weight-600">
      {{ $t('signup.title') }}
    </p>

    <div class="flex mt1">
      <!-- First name -->
      <div class="flex-1">
        <p class="text-left input-label">
          {{ $t('common.first-name') }}
        </p>
        <input
            class="input"
            :placeholder="$t('common.first-name')"
            v-model="firstname"
            autofocus
        />
      </div>
    </div>

    <div class="flex mt1">
      <!-- Last name -->
      <div class="flex-1">
        <p class="text-left input-label">
          {{ $t('common.last-name') }}
        </p>
        <input
            class="input"
            :placeholder="$t('common.last-name')"
            v-model="lastname"
        />
      </div>
    </div>

    <div class="flex mt1">
      <!-- Country -->
      <div class="flex-1">
        <p class="text-left input-label">
          {{ $t('signup.country') }}
        </p>
        <select v-model="country" class="input" :class="selectClass" @change="selectChange">
          <option disabled v-bind:value="null">{{ $t('signup.country') }}</option>
          <option value="FR">France</option>
          <option value="BE">Belgium</option>
          <option value="CH">Switzerland</option>
          <option value="GB">United Kingdom</option>
          <option value="IE">Ireland</option>
          <option value="US">USA</option>
          <option disabled v-bind:value="null">------</option>
          <option v-for="item in countries" :value="item.value">{{ item.text }}</option>
        </select>
      </div>
    </div>

    <div class="flex mt1">
      <!-- Email -->
      <div class="flex-1">
        <p class="text-left input-label">
          {{ $t('common.email') }}
        </p>
        <input
            class="input"
            :placeholder="$t('common.email')"
            v-model="email"
        />
      </div>
    </div>

    <div class="flex mt1">
      <!-- Password -->
      <div class="flex-1">
        <p class="text-left input-label">
          {{ $t('common.password') }}
        </p>
        <input
            class="input"
            :placeholder="$t('common.password')"
            type="password"
            v-model="password"
        />
      </div>
    </div>
  </div>
</template>

<script>
import countries from "../../../data/countries";

export default {
  name: 'SignupElements',
  data() {
    return {
      countries: [],
      selectChanged: false
    };
  },
  mounted() {
    this.countries = countries;
  },
  computed: {
    /**
     * Returns community
     */
    community() {
      return this.$store.state.community.data;
    },

    /**
     * Returns user
     */
    user() {
      return this.$store.state.auth.user;
    },

    /**
     * Get | Set firstname
     */
    firstname: {
      get() {
        return this.user.firstname;
      },
      set(v) {
        this.$store.commit('setUserProp', {
          key: 'firstname',
          v
        });
      }
    },

    /**
     * Get | Set lastname
     */
    lastname: {
      get() {
        return this.user.lastname;
      },
      set(v) {
        this.$store.commit('setUserProp', {
          key: 'lastname',
          v
        });
      }
    },

    /**
     * Get | Set country
     */
    country: {
      get() {
        return this.user.country ? this.user.country : null;
      },
      set(v) {
        this.$store.commit('setUserProp', {
          key: 'country',
          v
        });
      }
    },

    /**
     * Get | Set email
     */
    email: {
      get() {
        return this.user.email;
      },
      set(v) {
        this.$store.commit('setUserProp', {
          key: 'email',
          v
        });
      }
    },

    /**
     * Get | Set password
     */
    password: {
      get() {
        return this.user.password;
      },
      set(v) {
        this.$store.commit('setUserProp', {
          key: 'password',
          v
        });
      }
    },

    /**
     * Returns select box class
     */
    selectClass ()
    {
      return this.selectChanged ? ' selected' : '';
    },
  },
  methods: {
    /**
     * Select box change handler
     */
    selectChange ()
    {
      this.selectChanged = true;
    },
  }
}
</script>

<style scoped>
.home-logo {
  height: 40px;
}

select.input { 
  opacity: 0.5;
}

select.input.selected { 
  opacity: 1;
}

select.input option:disabled { 
  opacity: 0.6;
}
</style>
