import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/super-admin/payouts',
            component: require('./components/SuperAdmin/SuperAdminPayouts.vue').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/super-admin/stats',
            component: require('./components/SuperAdmin/SuperAdminStats.vue').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/join',
            component: require('./components/CommunityAbout').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url',
            component: require('./components/CommunityMain').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/:postUrl',
            component: require('./components/CommunityMain').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/ressources',
            component: require('./components/CommunityClassrooms').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/ressources/:cid',
            component: require('./components/CommunityClassroom').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/ressources/:cid/lesson/:lid',
            component: require('./components/CommunityClassroom').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/calendar',
            component: require('./components/CommunityCalendar').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/member',
            component: require('./components/CommunityMember').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/rankings',
            component: require('./components/CommunityLeaderboard').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/about',
            component: require('./components/CommunityAbout').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/users',
            component: require('./components/UserManagement').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/:url/start',
            component: require('./components/IncubateurStart').default,
            meta: {
                // middleware: [customer_auth]
            }
        },
        {
            path: '/account/profile',
            component: require('./components/Account/CustomerProfile').default,
            meta: {
                // middleware: [customer_auth]
            }
        }
    ],
    scrollBehavior (to, from, savedPosition) {
        if (document.getElementById('app')) {
            document.getElementById('app').scrollIntoView({ behavior: 'smooth' });
        }

        // savedPosition is only available for popstate navigations.
        if (savedPosition) return savedPosition

        // if the returned position is falsy or an empty object,
        // will retain current scroll position.
        if (to.params.savePosition) return {}

        // scroll to anchor by returning the selector
        if (to.hash) {
            let position = {selector: to.hash}
            
            return position
        }

        // scroll to top by default
        return {x: 0, y: 0}
    }
});

export default router