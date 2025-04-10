import moment from 'moment'
import { CommunityOnboardSteps } from '../../../data/enums';

export const mutations = {
    setCommunityLoading(state, payload) {
        state.loading = payload
    },

    setCommunityPlan(state, payload) {
        state.plan = payload
    },

    setCommunityData(state, payload) {
        let onboarding_step = CommunityOnboardSteps.COVER_IMAGE
        let completed_steps = 1
        if (payload?.summary_photo) {
            onboarding_step = CommunityOnboardSteps.DESCRIPTION;
            completed_steps++;
        }
        if (payload?.summary_description) {
            completed_steps++;
            if (onboarding_step == CommunityOnboardSteps.DESCRIPTION) onboarding_step = CommunityOnboardSteps.INVITE_FRIEND;
        }
        if (payload?.number_of_posts > 0) {
            completed_steps++;
            if (onboarding_step == CommunityOnboardSteps.INVITE_FRIEND) onboarding_step = CommunityOnboardSteps.COMPLETED;
        }
        if (typeof payload?.number_of_members !== 'undefined' && payload?.number_of_members >= 3) {
            completed_steps++;
        }

        let broadcast = 0;
        if (payload.last_sent_notification) {
            let now = moment();
            let notificationTS = moment(payload.last_sent_notification);

            let diffTimeFromLastSent = now.diff(notificationTS, 'seconds');

            if (diffTimeFromLastSent > 0 && diffTimeFromLastSent < 3600 * 24) {
                broadcast = 1;
            }
        }

        // refresh favicon
        var link = document.querySelector("link[rel~='icon']");
        if (link) {
            if (payload.favicon) {
                link.href = payload.favicon;
            } else {
                link.href = 'data:,';
            }
        }

        state.data = {
            ...payload,
            broadcast,
            onboarding_step,
            completed_steps
        }
    },

    setCommunityStatus(state, payload) {
        state.data.status = payload
    },

    setCommunityDataProp(state, payload) {
        state.data[payload.key] = payload.value
        let completed_steps = 1
        let onboarding_step = CommunityOnboardSteps.COVER_IMAGE
        if (state.data?.summary_photo) {
            onboarding_step = CommunityOnboardSteps.DESCRIPTION;
            completed_steps++;
        }
        if (state.data?.summary_description) {
            completed_steps++;
            if (onboarding_step == CommunityOnboardSteps.DESCRIPTION) onboarding_step = CommunityOnboardSteps.INVITE_FRIEND;
        }
        if (state.data?.number_of_posts > 0) {
            completed_steps++;
            if (onboarding_step == CommunityOnboardSteps.INVITE_FRIEND) onboarding_step = CommunityOnboardSteps.COMPLETED;
        }

        if (typeof state.data?.number_of_members !== 'undefined' && state.data?.number_of_members >= 3) {
            completed_steps++;
        }
        state.data.onboarding_step = onboarding_step
        state.data.completed_steps = completed_steps
    },

    /**
     * Set last sent notification
     */
    setLastSentNotification(state, payload) {
        state.lastSentNotification = payload;
    },

    setOnboardStep(state, payload) {
        state.data.onboarding_step = payload
    },

    /**
     * Update community rules
     */
    updateCommunityRules(state, payload) {
        state.data.rules = payload;
    },

    /**
     * Set all rule.dropdown to false
     */
    resetRuleDropdownItem(state) {
        let rules = JSON.parse(JSON.stringify(state.data.rules));
        rules.forEach(rule => {
            rule.dropdown = false;
        });
        state.data.rules = rules;
    },

    /**
     * Toggle item dropdown
     *
     * Set all other rule.dropdown to false
     */
    toggleRuleDropdownItem(state, payload) {
        let rules = JSON.parse(JSON.stringify(state.data.rules));
        rules.forEach(item => {
            item.dropdown = (item.id === payload.id) ? !item.dropdown : false;
        });
        state.data.rules = rules;
    },


    /**
     * Update community links
     */
    updateCommunityLinks(state, payload) {
        state.data.links = payload;
    },

    /**
     * Set all link.dropdown to false
     */
    resetLinkDropdownItem(state) {
        let links = JSON.parse(JSON.stringify(state.data.links));
        links.forEach(link => {
            link.dropdown = false;
        });
        state.data.links = links;
    },

    /**
     * Toggle item dropdown
     *
     * Set all other link.dropdown to false
     */
    toggleLinkDropdownItem(state, payload) {
        let links = JSON.parse(JSON.stringify(state.data.links));
        links.forEach(item => {
            item.dropdown = (item.id === payload.id) ? !item.dropdown : false;
        });
        state.data.links = links;
    },

    /**
     * Update community groups
     */
    updateCommunityGroups(state, payload) {
        state.data.groups = payload;
    },

    /**
     * Set all group.dropdown to false
     */
    resetGroupDropdownItem(state) {
        let groups = JSON.parse(JSON.stringify(state.data.groups));
        groups.forEach(group => {
            group.dropdown = false;
        });
        state.data.groups = groups;
    },

    /**
     * Toggle item dropdown
     *
     * Set all other group.dropdown to false
     */
    toggleGroupDropdownItem(state, payload) {
        let groups = JSON.parse(JSON.stringify(state.data.groups));
        groups.forEach(item => {
            item.dropdown = (item.id === payload.id) ? !item.dropdown : false;
        });
        state.data.groups = groups;
    },

    /**
     * Update community categories
     */
    updateCommunityCategories(state, payload) {
        state.data.categories = payload;
    },

    /**
     * Set all category.dropdown to false
     */
    resetCategoryDropdownItem(state) {
        let categories = JSON.parse(JSON.stringify(state.data.categories));
        categories.forEach(item => {
            item.dropdown = false;
        });
        state.data.categories = categories;
    },

    /**
     * Toggle item dropdown
     *
     * Set all other category.dropdown to false
     */
    toggleCategoryDropdownItem(state, payload) {
        let categories = JSON.parse(JSON.stringify(state.data.categories));
        categories.forEach(item => {
            item.dropdown = (item.id === payload.id) ? !item.dropdown : false;
        });
        state.data.categories = categories;
    },

    /**
     * Update community extensions
     */
    updateExtensions(state, payload) {
        state.data.extensions = payload;
    },

    /**
     * Set extension
     */
    setExtension(state, payload) {
        state.extension = JSON.parse(JSON.stringify(payload));
    },


    /**
     * Set auto dm template
     */
    setAutoDmTemplate(state, payload) {
        state.extension.template = payload;
    },

    /**
     * Set auto dm template props
     */
    setAudoDmTemplateProps(state, payload) {
        if (typeof state.extension.template !== 'undefined') {
            state.extension.template[payload.key] = payload.v;
        }
    },

    /**
     * Clone new community
     */
    cloneCommunity(state, payload) {
        state.clone = payload;
    },

    /**
     * Set community property
     */
    setCommunityProperty(state, payload) {
        state.clone[payload.key] = payload.v;
    },

    setCommunityDataProperty(state, payload) {
        state.data[payload.key] = payload.v;
    },

    /**
     * Reset community
     */
    resetCommunity(state) {
        state.clone = {
            id: 0,
            member_id: 0,
            favicon: null,
            url: null,
            link: null,
            links: [],
            logo: null,
            name: '',
            email: '',
            photo: null,
            video: null,
            privacy: 'public',
            owner_show: 1,
            summary_description: null,
            description: null,
            summary_photo: null,
            tab_classrooms: true,
            tab_calendar: true,
            tab_leaderboard: true
        };
    },

    setCommunityLastSentNotification(state, payload) {
        state.data.last_sent_notification = payload
    },

    setCommunityBroadcast(state, payload) {
        state.broadcast = payload;
    },

    setRewardsLevels(state, payload) {
        state.levels = payload;
    },

    setRewardLevel(state, payload) {
        state.rewardLevel = payload;
    },

    updateRewardsLevels(state, payload) {
        const levels = state.levels || []
        const itemIndex = levels.findIndex(elem => elem.id == payload.id)
        levels[itemIndex] = payload
        state.levels = levels;
    },

    saveCommunityPrice(state, payload) {
        state.data.price = payload;
    },

    setCommunityProducts(state, payload) {
        state.data.products = payload;
    },

    setCommunityPrice(state, payload) {
        state.price.id = payload.id;
        state.price.amount_monthly = payload.amount_monthly;
        state.price.amount_yearly = payload.amount_yearly;
        state.price.type = payload.type;
    },

    resetCommunityPrice(state) {
        state.price.id = null;
        state.price.amount_monthly = null;
        state.price.amount_yearly = null;
        state.price.type = null;
    },

    setCommunityPriceId(state, payload) {
        state.price.id = payload;
    },

    setCommunityPriceAmountMonthly(state, payload) {
        state.price.amount_monthly = payload;
    },

    setCommunityPriceAmountYearly(state, payload) {
        state.price.amount_yearly = payload;
    },

    setDisplayPriceForm(state, payload) {
        state.displayPriceForm = payload;
    },

    /**
     * Set incubateur start progress step
     */
    setIncubateurStartStep(state, payload) {
        state.incubateurStartStep = payload;
    },

    nextOnboardingStripeStep(state) {
        state.onboarding.stripe.step = state.onboarding.stripe.step + 1;
    },

    setTrialDays(state, payload) {
        state.data.trial_days = payload;
    },

    setMemberAllowed(state, payload) {
        state.data.member = payload;
    },

    setCommunityPayoutsBalance(state, payload) {
        state.data.payoutsBalance = payload;
    },

    addCommunityPayout(state, payload) {
        let payouts = JSON.parse(JSON.stringify(state.data.payouts));
        payouts.push(payload);
        state.data.payouts = payouts;
    },

    setCommunityNotifications(state, payload) {
        payload.forEach(notification => {
            if (notification.type === 'new_payment') {
                state.newPaymentNotification = true
            }
        });
    },

    setNewPaymentNotification(state, payload) {
        state.newPaymentNotification = payload
    },

    setCommunityTempMedia(state, payload) {
        state.tempMedia = payload;
    },

    changeAboutMediaOrder(state, payload)
    {
        let medias = [...state.data.medias];
        if (medias.length > 0) {
            let index1 = medias.findIndex(x => x.id === payload.from.id);
            let index2 = medias.findIndex(x => x.id === payload.to.id);

            medias[index1] = payload.to;
            medias[index2] = payload.from;

            state.data.medias = medias;
        }
    },

    setMentionedMembers(state, payload) {
        state.mentionedMembers = payload;
    },

    addMentionedMember(state, payload) {
        let mentionedMembers = JSON.parse(JSON.stringify(state.mentionedMembers));
        let existed = false;
        mentionedMembers = mentionedMembers.map((mentionedMember) => {
            if (mentionedMember.id === payload.id) {
                existed = true;
            }

            return { ...mentionedMember };
        });

        if (!existed) {
            mentionedMembers.push(payload);
        }

        state.mentionedMembers = mentionedMembers;
    },

    setMentionedMemberInfo(state, payload) {
        state.mentionedMemberInfo = payload;
    },

    setAppName(state, payload) {
        state.app_name = payload;
    },

    setCommunityInvoiceData(state, payload) {
        let invoice_data = JSON.parse(state.data.invoice_data ?? '{}');
        invoice_data[payload.key] = payload.value;
        state.data.invoice_data = JSON.stringify(invoice_data);
    },

    setCommunityPayoutFilterYear(state, payload) {
        state.payoutFilterYear = payload;
    },

    setCommunityPayouts(state, payload) {
        state.data.payouts = payload
    },

    setCommunityTransactions(state, payload) {
        state.data.transactions = payload
    },

    selectCommunityMedia(state, payload)
    {
        let medias = [...state.data.medias];
        if (medias.length > 0) {
            medias = medias.map((media) => {
                if (media.id === payload.id) {
                    media.selected = true;
                } else {
                    media.selected = false;
                }

                return { ...media };
            });

            state.data.medias = medias;
        }
    },

    /**
     * set about description view
     */
    setAboutDescriptionView(state, payload) {
        state.aboutDescriptionView = payload;
    },
}