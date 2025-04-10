<template>
    <div class="mt2">
        <p class="tab-content-title">
            {{ $t('community.members.setting-modal.admin-settings.api_key_title') }}
        </p>
        <!-- Group name -->
        <div class="flex-1">
            <div class="invite-content-action">
                <div class="invite-content-action-input">
                    {{ apiKey }}
                </div>

                <div class="invite-content-action-btn" @click="copy">
                    {{ inviteActionText }}
                </div>
                <div ref="reference"></div>
            </div>
        </div>
        <div class="submit-button mt1">
            <button class="button is-medium community-blue-btn" @click="generateKey">
                {{ $t('community.members.setting-modal.admin-settings.generate') }}
            </button>
        </div>
    </div>
</template>

<script>

export default {
    name: 'ApiKeySetting',
    components: {
    },
    data() {
        return {
            inviteActionText: '',
        };
    },
    created() {
        this.self = this;
    },
    mounted() {
        this.inviteActionText = this.$t('community.members.setting-modal.copy');
    },
    computed: {
        /**
         * Return apiKey
         */
        apiKey() {
            return this.$store.state.member.apiKey.api_key;
        },

        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        currentMember() {
            return this.$store.state.member.data;
        },
    },
    methods: {
        /**
         * copy action handler
         */
        copy() {
            const storage = document.createElement('textarea');
            storage.value = this.apiKey;
            this.$refs.reference.appendChild(storage);
            storage.select();
            storage.setSelectionRange(0, 99999);
            document.execCommand('copy');
            this.$refs.reference.removeChild(storage);

            this.inviteActionText = this.$t('community.members.setting-modal.copied');

            setTimeout(() => {
                this.inviteActionText = this.$t('community.members.setting-modal.copy');
            }, 2000);
        },

        async generateKey() {
            await this.$store.dispatch('GENERATE_API_KEY', {
                url: this.community.url,
                memberId: this.currentMember.id
            });
        },
    }
}
</script>

<style scoped>
.invite-content-action {
    width: 100%;
    height: 52px;
    border-radius: 3px;
    border: 1px solid rgb(228, 228, 228);
    display: flex;
    align-items: center;
    margin-top: 15px;
}

.invite-content-action:focus {
    border: 1px solid rgb(144, 144, 144);
}

.invite-content-action-input {
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding-left: 10px;
}

.invite-content-action-btn {
    width: 100px;
    height: 48px;
    background-color: #9198FF;
    font-weight: bold;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border-radius: 3px;
    margin-right: 5px;
    text-transform: uppercase;
}

.invite-content-action-btn:hover {
    background-color: #7957d5;
    color: #fff;
}

@media only screen and (max-width: 600px) {
    .invite-content-action {
        height: 42px;
        margin-top: 5px;
    }

    .invite-content-action-btn {
        height: 38px;
    }
}
</style>
