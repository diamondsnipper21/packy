<template>
    <multiselect v-model="value" :options="members" :multiple="true" :close-on-select="false" :loading="isLoading"
        :clear-on-select="false" :hide-selected="true" label="name" track-by="name"
        :placeholder="$t('community.classroom.member-access')" :select-label="$t('common.press-enter-to-select')"
        :selected-label="$t('common.selected')" :deselect-label="$t('common.press-enter-to-deselect')"
        :searchable="true" :internal-search="false" :options-limit="50" :max-height="600" :show-no-results="false"
        @search-change="searchMembers">
        <template #option="props">
            <div class="flex align-items-center">
                <img v-if="props.option.avatar" class="multi-select-option-avatar mr-05" :src="props.option.avatar" />
                <span class="multi-select-option-name">{{ props.option.name }}</span>
            </div>
        </template>
        <template #clear="props">
            <div class="multiselect__clear" v-if="value.length" @mousedown.prevent.stop="clearAll(props.search)"></div>
        </template>
    </multiselect>
</template>

<script>
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.css'
export default {
    name: 'MemberMultiSelect',
    components: {
        Multiselect
    },
    props: {
        modelValue: {
            type: null,
            default() {
                return []
            }
        },
        initOptions: {
            type: Array,
            default() {
                return []
            }
        },
    },
    emits: ['update:modelValue'],
    data() {
        return {
            members: this.initOptions,
            isLoading: false,
            selectedMembers: this.modelValue
        }
    },
    computed: {
        value: {
            get() {
                return this.selectedMembers
            },
            set(v) {
                this.selectedMembers = v
                this.$emit('update:modelValue', v);
            }
        },
    },
    methods: {
        clearAll() {
            this.selectedMembers = []
        },
        async searchMembers(query) {
            this.isLoading = true;
            if (query.length > 1) {
                this.members = await this.$store.dispatch('GET_ASSIGNABLE_MEMBERS', query);
            }
            this.isLoading = false;
        },
    }
}
</script>
