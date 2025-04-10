<template>
    <div>
        <div v-if="categoryShow === 'list'">
            <div>
                <div class="tab-content-title">
                    {{ $t('community.members.setting-modal.admin-settings.categories') }}
                </div>

                <div class="mt1">
                    <button class="button is-medium community-blue-btn" @click="addCategory">
                        {{ $t('community.members.setting-modal.admin-settings.add-category') }}
                    </button>
                </div>
            </div>

            <div class="mt1">
                <div v-if="categories.length === 0" class="empty-section">
                    {{ $t('community.members.setting-modal.admin-settings.empty-category') }}
                </div>
                <div
                    v-for="category, index in categories"
                    :key="category.id"
                    class="tab-content-item align-items-center"
                >
                    <div class="tab-content-item-content">
                        <div class="tab-content-sub-title font-weight-600 max-full-width">
                            {{ index + 1 }}. {{ category.title }}
                        </div>
                    </div>
                    <div class="tab-content-item-action">
                        <button class="button is-medium community-btn mr-05" @click="editCategory(category)">
                            {{ $t('common.edit') }}
                        </button>

                        <div :class="showDropdown(category)" class="tab-content-action-dropdown">
                            <div class="dropdown-trigger flex">
                                <button class="button is-medium community-btn tab-content-action-dropdown-link" @click.stop="toggleDropdown(category)">
                                    <font-awesome-icon icon="fa fa-bars" class="tab-content-action-dropdown-link-icon" />
                                </button>
                            </div>
                            <div class="dropdown-menu-items">
                                <div
                                    class="tab-content-item-action-item"
                                    @click.stop="deleteCategory(category)"
                                >
                                    {{ $t('common.delete') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="categoryShow === 'add'">
            <div class="tab-content-title">
                {{ $t('community.members.setting-modal.admin-settings.add-category') }}
            </div>
            <div class="flex mt2">
                <!-- Title -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.title') }}
                    </p>
                    <input
                        class="input"
                        :placeholder="$t('community.community.title')"
                        v-model="newCategory.title"
                        @input="inputNewCategoryTitle"
                    />
                </div>
            </div>

            <div class="flex mt2">
                <input type="checkbox" v-model="new_category_admin_only" class="admin-only-checkbox mr1" />
                <label for="admin-only-checkbox" class="pointer admin-only-checkbox-label" @click="checkNewCategoryAdminOnly" >{{ $t('community.members.setting-modal.admin-settings.admin-category') }}</label>
            </div>

            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="addConfirm" @click="saveAddCategory">
                    {{ $t('common.add') }}
                </button>
                <button class="button is-medium community-btn" @click="close">
                    {{ $t('common.cancel') }}
                </button>
            </div>
        </div>

        <div v-else-if="categoryShow === 'edit'">
            <div class="tab-content-title">
                {{ $t('community.members.setting-modal.admin-settings.edit-category') }}
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
                        v-model="editedCategory.title"
                        @input="inputEditCategoryTitle"
                    />
                </div>
            </div>

            <div class="flex mt2">
                <input type="checkbox" v-model="edit_category_admin_only" class="admin-only-checkbox mr1" />
                <label for="admin-only-checkbox" class="pointer admin-only-checkbox-label" @click="checkEditedCategoryAdminOnly" >{{ $t('community.members.setting-modal.admin-settings.admin-category') }}</label>
            </div>

            <div class="flex mt2">
                <button class="button is-medium community-blue-btn mr-05" :disabled="editConfirm" @click="saveEditCategory">
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
	name: 'CategorySetting',
    data () {
        return {
            editedCategory: null,
            newCategory: null,
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
         * Returns community categories
         */
        categories ()
        {
            return this.community.categories;
        },

        /**
         * Returns community community settings
         */
        communitySettings ()
        {
            return this.$store.state.communitycenter.communitySettings;
        },

        /**
         * Returns setting category show
         */
        categoryShow ()
        {
            return this.communitySettings.categoryShow;
        },

        /**
         * new category admin only
         */
        new_category_admin_only: {
            get () {
                return (this.newCategory.admin_only === 1 || this.newCategory.admin_only === true);
            },
            set (v) {
                this.newCategory.admin_only = !this.newCategory.admin_only;
            }
        },

        /**
         * edit category admin only
         */
        edit_category_admin_only: {
            get () {
                return (this.editedCategory.admin_only === 1 || this.editedCategory.admin_only === true);
            },
            set (v) {
                this.editedCategory.admin_only = !this.editedCategory.admin_only;
            }
        },
    },
	methods: {
        addCategory ()
        {
            this.newCategory = {
                id: 0,
                community_id: this.community.id,
                title: '',
                admin_only: 0
            };

            this.addConfirm = true;

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'categoryShow',
                v: 'add'
            });
        },

        editCategory (category)
        {
            this.editedCategory = category;
            this.editConfirm = false;
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'categoryShow',
                v: 'edit'
            });
        },

        close ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'categoryShow',
                v: 'list'
            });
        },

        /**
         * Show or hide the dropdown
         */
        showDropdown (category)
        {
            return (typeof category.dropdown !== 'undefined' && category.dropdown) ? 'dropdown is-active' : 'dropdown';
        },

        /**
         * Show or hide the dropdown
         */
        toggleDropdown (category)
        {
            this.$store.commit('toggleCategoryDropdownItem', category);
        },

        async deleteCategory (category)
        {
            await this.$store.dispatch('DELETE_CATEGORY', category);
        },

        /**
         * When we input new category title
         */
        inputNewCategoryTitle ()
        {
            if (this.newCategory.title !== '') {
                this.addConfirm = false;
            } else {
                this.addConfirm = true;
            }
        },

        async saveAddCategory ()
        {
            await this.$store.dispatch('SAVE_CATEGORY', this.newCategory);
            this.close();
        },

        /**
         * When we input edit category title
         */
        inputEditCategoryTitle ()
        {
            if (this.editedCategory.title !== '') {
                this.editConfirm = false;
            } else {
                this.editConfirm = true;
            }
        },

        /**
         * Check admin only checkbox
         */
        checkNewCategoryAdminOnly ()
        {
            this.newCategory.admin_only = !this.newCategory.admin_only;
        },

        /**
         * Check admin only checkbox
         */
        checkEditedCategoryAdminOnly ()
        {
            this.editedCategory.admin_only = !this.editedCategory.admin_only;
        },

        async saveEditCategory ()
        {
            await this.$store.dispatch('SAVE_CATEGORY', this.editedCategory);
            this.close();
        },
	},
}
</script>

<style scoped>
    @media only screen and (max-width: 600px)
    {
        .admin-only-checkbox-label {
            font-size: 14px;
        }
    }
</style>
