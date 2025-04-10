<template>
    <div class="container pl-0 pr-0">
        <div class="columns">
            <div class="column is-one-quarter">
                <div 
                    v-for="tab, i in regularTabs" 
                    class="tab-link" 
                    :class="tabClass(tab)"
                    @click="selectTab(tab)"
                >
                    {{ $t(`privacy.tabs.${tab}`) }}
                </div>
            </div>
            <div class="column">
                <Legal v-if="selectedTab === 'legal'" />
                <Rules v-if="selectedTab === 'rules'" />
                <Terms v-if="selectedTab === 'terms'" />
                <Cookies v-if="selectedTab === 'cookies'" />
                <Privacy v-if="selectedTab === 'privacy'" />
            </div>
        </div>
    </div>
</template>

<script>

import Legal from "./Legal/Legal";
import Rules from "./Legal/Rules";
import Terms from "./Legal/Terms";
import Cookies from "./Legal/Cookies";
import Privacy from "./Legal/Privacy";

export default {
    name: 'LegalIndex',
    components: {
      Legal,
      Rules,
      Terms,
      Cookies,
      Privacy
    },
    data () {
        return {
            regularTabs: ['legal', 'rules', 'terms', 'cookies', 'privacy'],
            selectedTab: 'legal',
        };
    },
    created ()
    {
        document.documentElement.style = 'background-image: url(\'https://wolfeo.s3.eu-west-1.amazonaws.com/uploads/6746026629/silver.png\'); background-repeat: no-repeat; background-size: cover; width: 100vw; height: 100vh;';

        let params = this.$route.path.split("/");
        if (typeof params[2] !== 'undefined' && this.regularTabs.includes(params[2])) {
            this.selectedTab = params[2];
        }
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
         * Class to show for clicked tab
         */
        tabClass(tab) {
            let tabClass = '';
            tabClass = (this.selectedTab === tab) ? 'clicked' : '';

            return tabClass;
        },

        /**
         * Select tab click handler
         */
        selectTab(tab) {
            let path = '/legal';
            if (tab !== 'legal') {
                path += '/' + tab;
            }

            this.$router.push(path).catch(() => { });
            this.selectedTab = tab;
        }
    }
}
</script>

<style scoped>

    .tab-link {
        padding: 10px 15px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: background 300ms ease 0s;
        text-align: left;
        white-space: nowrap;
    }

    .tab-link:hover {
        background-color: rgb(228, 228, 228);
    }

    .tab-link.clicked {
        color: #fff;
        background-color: #9198FF;
    }

    @media only screen and (max-width: 600px)
    {
        .tab-link {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 5px;
        }

    }
</style>
