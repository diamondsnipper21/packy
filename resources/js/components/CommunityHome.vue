<template>
    <div class="container pl-0 pr-0">
        <div class="text-center pt3 pb3">
            <div class="font-weight-600 home-title">
                {{ $t('home.title') }}
            </div>
            <div class="mt2">
                <div class="home-desc font-16px">
                    {{ $t('home.desc') }}
                    <span class="font-18px font-weight-500">
                        {{ $t('home.desc-1') }}
                    </span>
                </div>
            </div>
            <button
                class="button is-large community-blue-btn mt2 create-account-btn"
                @click="showSignup"
            >
                {{ $t('home.start-free') }}
            </button>
        </div>

        <div class="columns mt2" style="flex-wrap: wrap;">
            <div
                v-for="item in contents"
                :key="item.key"
                class="column is-half p2"
                :class="item.key + '-card'"
            >
                <div class="cc-item-card">
                    <img :src="item.photo" class="card-img" />
                    <div class="font-24px font-weight-600 mt-05">
                        {{ translateLabel(item.key) }}
                    </div>
                    <div class="cc-home-item-card-desc mt1">
                        {{ translateDesc(item.key) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="copy-right">
            {{ $t('home.copyright') }}
        </div>
        <div class="langauge-switch">
            <div class="dropdown flex align-items-center">
                <div 
                    class="dropdown-trigger"
                    @click.stop="showLangOptions"
                    v-click-outside="hideLangOptions"
                >
                <div class="lang-select-info">
                    <font-awesome-icon icon="fa fa-globe" class="mr-05" />
                    {{ $t('general.language.label') }}
                </div>
            </div>
                <div id="lang_select_options"
                     class="dropdown-menu"
                     :class="displayLangSelectDropdown ? 'show' : ''"
                >
                    <div class="dropdown-content">
                        <a v-for="lang in langs" class="lang-select-link" @click="changeFrontendLanguage(lang.value)">
                            <img :src="lang.icon" class="lang-flag-icon"/>
                            <span>&nbsp;{{ lang.label }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import languages from '../data/languages';

export default {
    name: 'CommunityHome',
    data () {
        return {
            contents: [{
                key: "community",
                photo: '/assets/img/group-chat.png'
            }, {
                key: "calendar",
                photo: '/assets/img/calendar.png'
            }, {
                key: "classroom",
                photo: '/assets/img/video-player.png'
            }, {
                key: "rewarding",
                photo: '/assets/img/trophy.png'
            }, {
                key: "without-adterruption",
                photo: '/assets/img/stop-sign.png'
            }, {
                key: "simple-to-use",
                photo: '/assets/img/light-bulb.png'
            }],
            langs: [],
            displayLangSelectDropdown: false,
        };
    },
    mounted() {
        this.langs = languages;

        document.documentElement.style = 'background-image: url(\'https://wolfeo.s3.eu-west-1.amazonaws.com/uploads/6746026629/silver.png\'); background-repeat: no-repeat; background-size: cover; width: 100vw; height: 100vh;';
    },
    computed: {
        /**
         * Return auth status
         */
        auth ()
        {
            return this.$store.state.auth.loggedIn;
        },
    },
    methods: {
        /**
         * Return translated label
         */
        translateLabel (key)
        {
            return this.$t(`home.${key}.label`);
        },

        /**
         * Return translated desc
         */
        translateDesc (key)
        {
            return this.$t(`home.${key}.desc`);
        },

        /**
         * Show login modal
         */
        showLogin ()
        {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Login',
                transparent: true
            });
        },

        /**
         * Show signup modal
         */
        showSignup ()
        {
            this.$store.commit('resetUser');
            this.$store.commit('showModal', {
                type: 'Signup',
                transparent: true
            });
        },

        showLangOptions() {
            this.displayLangSelectDropdown = !this.displayLangSelectDropdown;
        },

        hideLangOptions() {
            this.displayLangSelectDropdown = false;
        },

        changeFrontendLanguage(lang)
        {
            localStorage.setItem('lang', lang);
            this.$i18n.locale = lang;
        },
    }
}
</script>

<style scoped>
    .cc-item-card {
        height: 280px;
        cursor: pointer;
        border-radius: 6px;
        background-color: #fff;
        padding: 25px;
    }

    .cc-item-card:hover {
        box-shadow: rgb(60 64 67 / 32%) 0px 1px 2px, rgb(60 64 67 / 15%) 0px 2px 6px, rgb(0 0 0 / 10%) 0px 1px 8px;
    }

    .cc-home-item-card-desc {
        font-size: 17px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical;
    }

    .home-title {
        font-size: 40px;
    }

    .home-desc {
        max-width: 700px;
        margin: 0 auto;
    }

    .card-img {
        width: 60px;
        height: 60px;
        object-fit: contain;
    }

    .community-card {
        width: 45% !important;
    }

    .calendar-card {
        width: 55% !important;
    }

    .classroom-card {
        width: 55% !important;
    }

    .rewarding-card {
        width: 45% !important;
    }

    .without-adterruption-card {
        width: 40% !important;
    }

    .simple-to-use-card {
        width: 60% !important;
    }

    .create-account-btn {
        border-radius: 50px;
        font-size: 20px;
    }

    .copy-right {
        text-align: center;
        font-size: 20px;
        padding: 20px 0px;
    }

    .langauge-switch {
        position: fixed;
        right: 0px;
        bottom: 35px;
    }

    .lang-select-info {
        padding: 7px 15px;
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 3px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);
        background-color: #fff;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.5s ease;
        margin-right: -90px;
        width: 132px;
    }

    .lang-select-info:hover {
        margin-right: 0px;
    }

    #lang_select_options {
        top: -59px !important;
    }

    #lang_select_options .dropdown-content {
        border-top-right-radius: 0px !important;
        border-bottom-right-radius: 0px !important;
    }

    .lang-flag-icon {
        height: 1.5em;
        margin-right: 0.5em;
    }

    .lang-select-link {
        display: flex;
        align-content: center;
        cursor: pointer;
        height: 100%;
        color: #4a4a4a;
        line-height: 1.5;
        padding: 0.5rem 0.75rem;
        position: relative;
    }

    .lang-select-link:hover {
        color: #7957d5;
    }

    @media only screen and (max-width: 600px)
    {
        .cc-item-card {
            padding: 15px;
        }

        .cc-home-item-card-desc {
            font-size: 16px;
        }

        .home-title {
            font-size: 30px;
        }

        .home-desc {
            max-width: 350px;
        }

        .community-card {
            width: 100% !important;
            padding: 1em !important;
        }

        .calendar-card {
            width: 100% !important;
            padding: 1em !important;
        }

        .classroom-card {
            width: 100% !important;
            padding: 1em !important;
        }

        .rewarding-card {
            width: 100% !important;
            padding: 1em !important;
        }

        .without-adterruption-card {
            width: 100% !important;
            padding: 1em !important;
        }

        .simple-to-use-card {
            width: 100% !important;
            padding: 1em !important;
        }

        .create-account-btn {
            font-size: 16px;
        }

        .copy-right {
            font-size: 16px;
        }
    }
</style>
