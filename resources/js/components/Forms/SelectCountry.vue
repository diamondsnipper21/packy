<template>
  <select v-model="selectedCountry" @change="selectChange" class="input" :class="selectClass">
    <option disabled :value="null">{{ $t('common.country') }}</option>
    <option value="FR">France</option>
    <option value="BE">Belgium</option>
    <option value="CH">Switzerland</option>
    <option value="GB">United Kingdom</option>
    <option value="IE">Ireland</option>
    <option value="US">USA</option>
    <option disabled>------</option>
    <option v-for="item in countries" :value="item.value">
      {{ item.text }}
    </option>
  </select>
</template>

<script>
import countries from './../../data/countries';

export default {
  name: 'SelectCountry',
  props: {
    country: String,
  },
  data() {
    return {
      selectedCountry: null,
      countries: [],
      selectChanged: false
    };
  },
  async created() {
    this.selectedCountry = this.country ? this.country : null;
    this.countries = countries;
  },
  computed: {
    /**
     * Returns select box class
     */
    selectClass ()
    {
      return this.selectChanged ? ' selected' : '';
    },
  },
  methods: {
    selectChange() {
      this.selectChanged = true;
      this.$emit('selected-country', this.selectedCountry);
    }
  }
}
</script>

<style scoped>
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