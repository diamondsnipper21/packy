<template>
    <div>
        <div v-if="ruleShow === 'list'">
            <div>
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.admin-settings.rules') }}
                </div>

                <div class="mt1">
                    <button class="button is-medium community-blue-btn" @click="addRule">
                        {{ $t('community.members.setting-modal.admin-settings.add-rule') }}
                    </button>
                </div>
            </div>

            <div class="mt1">
                <div
                    v-for="rule in rules"
                    :key="rule.id"
                    class="tab-content-item"
                >
                    <div class="tab-content-item-content">
                        <div class="tab-content-sub-title font-weight-600 max-full-width">
                            {{ rule.order }}. {{ rule.title }}
                        </div>
                        <div class="tab-content-desc">
                            {{ rule.description }}
                        </div>
                    </div>
                    <div class="tab-content-item-action">
                        <button class="button is-medium community-btn mr-05" @click="editRule(rule)">
                            {{ $t('common.edit') }}
                        </button>

                        <div :class="showDropdown(rule)" class="tab-content-action-dropdown" v-click-outside="onClickOutside">
                            <div class="dropdown-trigger flex">
                                <button class="button is-medium community-btn tab-content-action-dropdown-link" @click.stop="toggleDropdown(rule)">
                                    <font-awesome-icon icon="fa fa-bars" class="tab-content-action-dropdown-link-icon" />
                                </button>
                            </div>
                            <div class="dropdown-menu-items">
                                <div
                                    class="tab-content-item-action-item"
                                    :class="rule.order === 1 ? 'disabled' : ''"
                                    @click.stop="moveUp(rule)"
                                >
                                    {{ $t('community.members.setting-modal.admin-settings.move-up') }}
                                </div>
                                <div
                                    class="tab-content-item-action-item"
                                    :class="rule.order === rules.length ? 'disabled' : ''"
                                    @click.stop="moveDown(rule)"
                                >
                                    {{ $t('community.members.setting-modal.admin-settings.move-down') }}
                                </div>
                                <div
                                    class="tab-content-item-action-item"
                                    @click.stop="deleteRule(rule)"
                                >
                                    {{ $t('common.delete') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="ruleShow === 'add'">
            <div class="tab-content-title">
                {{ $t('community.members.setting-modal.admin-settings.add-rule') }}
            </div>
            <div class="flex mt2">
                <!-- Group url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.title') }}
                    </p>
                    <input
                        class="input"
                        :placeholder="$t('community.community.title')"
                        v-model="newRule.title"
                        @input="inputNewRuleTitle"
                    />
                </div>
            </div>
            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.description') }}
                    </p>
                    <textarea
                        class="textarea"
                        :placeholder="$t('community.community.description')"
                        v-model="newRule.description"
                        rows="3"
                    />
                </div>
            </div>
            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="addConfirm" @click="saveAddRule">
                    {{ $t('common.add') }}
                </button>
                <button class="button is-medium community-btn" @click="close">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

        <div v-else-if="ruleShow === 'edit'">
            <div class="tab-content-title">
                {{ $t('community.members.setting-modal.admin-settings.edit-rule') }}
            </div>
            <div class="flex mt2">
                <!-- Group url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.title') }}
                    </p>
                    <input
                        class="input"
                        :placeholder="$t('community.community.title')"
                        v-model="editedRule.title"
                        @input="inputEditRuleTitle"
                    />
                </div>
            </div>
            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.description') }}
                    </p>
                    <textarea
                        class="textarea"
                        :placeholder="$t('community.community.description')"
                        v-model="editedRule.description"
                        rows="3"
                    />
                </div>
            </div>
            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="editConfirm" @click="saveEditRule">
                    {{ $t('common.save') }}
                </button>
                <button class="button is-medium community-btn" @click="close">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

    </div>
</template>

<script>
export default {
	name: 'RulesSetting',
    data () {
        return {
            editedRule: null,
            newRule: null,
            addConfirm: true,
            editConfirm: true,
        };
    },
    computed: {
        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },

        /**
         * Returns community rules
         */
        rules ()
        {
            return this.community.rules;
        },

        /**
         * Returns community community settings
         */
        communitySettings ()
        {
            return this.$store.state.communitycenter.communitySettings;
        },

        /**
         * Returns setting rule show
         */
        ruleShow ()
        {
            return this.communitySettings.ruleShow;
        }
    },
	methods: {
        addRule ()
        {
            this.newRule = {
                id: 0,
                community_id: this.community.id,
                title: '',
                description: ''
            };

            this.addConfirm = true;

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'ruleShow',
                v: 'add'
            });
        },

        editRule (rule)
        {
            this.editedRule = rule;
            this.editConfirm = false;
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'ruleShow',
                v: 'edit'
            });
        },

        close ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'ruleShow',
                v: 'list'
            });
        },

        /**
         * Show or hide the dropdown
         */
        showDropdown (rule)
        {
            return (typeof rule.dropdown !== 'undefined' && rule.dropdown) ? 'dropdown is-active' : 'dropdown';
        },

        /**
         * Show or hide the dropdown
         */
        toggleDropdown (rule)
        {
            this.$store.commit('toggleRuleDropdownItem', rule);
        },

        /**
         * When we click outside of the dropdown, close it
         */
        onClickOutside ()
        {
            this.$store.commit('resetRuleDropdownItem');
        },

        async moveUp (rule)
        {
            await this.$store.dispatch('MOVE_UP_RULE', rule);
        },

        async moveDown (rule)
        {
            await this.$store.dispatch('MOVE_DOWN_RULE', rule);
        },

        async deleteRule (rule)
        {
            await this.$store.dispatch('DELETE_RULE', rule);
        },

        /**
         * When we input new rule title
         */
        inputNewRuleTitle ()
        {
            if (this.newRule.title !== '') {
                this.addConfirm = false;
            } else {
                this.addConfirm = true;
            }
        },

        async saveAddRule ()
        {
            await this.$store.dispatch('SAVE_RULE', this.newRule);
            this.close();
        },

        /**
         * When we input edit rule title
         */
        inputEditRuleTitle ()
        {
            if (this.editedRule.title !== '') {
                this.editConfirm = false;
            } else {
                this.editConfirm = true;
            }
        },

        async saveEditRule ()
        {
            await this.$store.dispatch('SAVE_RULE', this.editedRule);
            this.close();
        },
	},
    directives: {
        'click-outside': {
            bind: function(el, binding, vNode) {
                // Provided expression must evaluate to a function.
                if (typeof binding.value !== 'function') {
                    const compName = vNode.context.name
                    let warn = `[Vue-click-outside:] provided expression '${binding.expression}' is not a function, but has to be`
                    if (compName) { warn += `Found in component '${compName}'` }
                    // console.warn(warn)
                }
                // Define Handler and cache it on the element
                const bubble = binding.modifiers.bubble
                const handler = (e) => {
                    if (bubble || (!el.contains(e.target) && el !== e.target)) {
                        binding.value(e)
                    }
                }
                el.__vueClickOutside__ = handler

                // add Event Listeners
                document.addEventListener('click', handler)
            },

            unbind: function(el, binding) {
                // Remove Event Listeners
                document.removeEventListener('click', el.__vueClickOutside__)
                el.__vueClickOutside__ = null
            }
        }
    }
}
</script>

<style scoped>
</style>
