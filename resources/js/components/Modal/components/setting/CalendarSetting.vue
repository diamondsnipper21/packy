<template>
    <div>
        <div v-if="calendarShow === 'list'">
            <div v-if="communityEvents.length === 0" class="w100">
                <div class="tab-content-title">
                    {{ $t('community.tabs.calendar') }}
                </div>
                <p class="tab-content-desc mt1" v-html="$t('community.calendar.add-desc')"/>
                <div class="submit-button mt1">
                    <button class="button is-medium community-blue-btn" @click="addEvent">
                        {{ $t('community.calendar.create-event-btn') }}
                    </button>
                </div>
            </div>

            <div v-else class="w100">
                <div class="flex align-items-center jcb">
                    <div class="tab-content-title">
                        {{ $t('community.tabs.calendar') }}
                    </div>

                    <button class="button is-medium community-blue-btn text-uppercase" @click="addEvent">
                        {{ $t('community.calendar.add-event') }}
                    </button>
                </div>

                <div class="flex align-items-center jc mt2">
                    <div class="calendar-link pointer" @click="decreaseMonth">
                        <font-awesome-icon icon="fa fa-chevron-left"/>
                    </div>
                    <div class="ml1 mr1 calendar-month-info">
                        {{ monthInfo }}
                    </div>
                    <div class="calendar-link pointer" @click="increaseMonth">
                        <font-awesome-icon icon="fa fa-chevron-right"/>
                    </div>
                </div>

                <div class="mt2">

                    <!-- No events Exist -->
                    <div v-if="this.events.length === 0" class="empty-section">
                        {{ $t('community.community.empty-event-placeholder') }}
                    </div>

                    <div v-else>
                        <div
                            v-for="(event, i) in events"
                            :key="event.id"
                            class="tab-content-item"
                        >
                            <div class="tab-content-item-content">
                                <div class="tab-content-sub-title font-weight-600 max-full-width">
                                    {{ event.title }}
                                </div>
                                <div class="tab-content-desc">
                                    {{ getEventDate(event.start_at) }} - {{ getEventEndDate(event) }}
                                </div>
                            </div>
                            <div class="tab-content-item-action">
                                <button class="button is-medium community-btn mr-05" @click="goToEditEvent(event)">
                                    {{ $t('common.edit') }}
                                </button>
                                <button class="button is-medium community-btn" @click="goToDeleteEvent(event)">
                                    {{ $t('common.delete') }}
                                </button>
                            </div>
                        </div>
                        <Pagination :total="total" :perPage="perPage" :current="currentPage"
                            pageAction="GET_MONTHLY_EVENTS" class="mb1"/>
                    </div>
                </div>
            </div>
        </div>

        <div v-else-if="calendarShow === 'add' || calendarShow === 'edit'">
            <div v-if="calendarShow === 'add'" class="tab-content-title">
                {{ $t('community.calendar.add-event') }}
            </div>
            <div v-else-if="calendarShow === 'edit'" class="tab-content-title">
                {{ $t('community.calendar.edit-event') }}
            </div>

            <div class="flex mt2">
                <!-- Event Title -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.title') }}
                    </p>
                    <input
                        class="input"
                        :placeholder="$t('community.community.title')"
                        v-model="calendarEvent.title"
                        @input="inputCalendarEventTitle"
                    />
                </div>
            </div>

            <div class="flex mt2">
                <!-- Photo url -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.add-image.title') }}
                    </p>
                    <div class="flex align-items-center">
                        <input
                            class="input mr-05"
                            :placeholder="$t('community.community.add-image.description')"
                            v-model="event_media"
                            @keypress="validateUrl($event)"
                        />
                        <UploadFile filetype="image" :owner="owner" />
                    </div>
                </div>
            </div>

            <div class="flex mt2">
                <!-- Link -->
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.add-link.title') }}
                    </p>
                    <input
                        class="input"
                        :placeholder="$t('community.community.add-link.description')"
                        v-model="calendarEvent.link"
                    />
                </div>
            </div>

            <div class="flex mt2">
                <div class="w40">
                    <p class="text-left input-label">
                        {{ $t('community.calendar.start_at') }}
                    </p>
                    <VueDatePicker v-model="calendarEvent.start_at" :teleport-center="true" :format="format" />
                </div>

                <div class="w60 flex ml1">
                    <div class="w40">
                        <p class="text-left input-label">
                            {{ $t('community.calendar.duration') }}
                        </p>

                        <select v-model="calendarEvent.duration" class="input">
                            <option value="" disabled>{{ $t('community.calendar.duration') }}</option>
                            <option v-for="durationOpt in durationOpts" :value="durationOpt">
                                {{ durationLabel(durationOpt) }}
                            </option>
                        </select>
                    </div>

                    <div class="ml1">
                        <p class="text-left input-label">
                            {{ $t('community.calendar.timezone') }}
                        </p>

                        <select v-model="calendarEvent.timezone" class="input">
                            <option value="" disabled>{{ $t('community.calendar.timezone') }}</option>
                            <option v-for="timezone in timezones" :value="timezone.key">
                                {{ timezone.val }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div v-if="isManager(role)" class="flex mt2">
                <input type="checkbox" v-model="calendarEvent.repeat" class="repeat-checkbox mr1" />
                <label for="repeat-checkbox" class="pointer" @click="checkRecurringBox" >{{ $t('community.calendar.recurring-event') }}</label>
            </div>

            <div v-if="calendarEvent.repeat" class="w100">
                <template v-if="calendarShow === 'edit'">
                    <div class="flex-1 mt2">
                        <p class="text-left input-label">
                            {{ $t('community.calendar.repeat-on') }}
                        </p>
                        <span
                            v-for="(repeatDate, i) in repeatDates"
                            :key="repeatDate.id"
                            class="repeat-date-text"
                        >
                            {{ repeatDate.text }}
                        </span>
                        {{ $t('common.at') }}
                        <span class="repeat-date-text">
                            {{ repeatTime }}
                        </span>
                    </div>
                </template>
                <template v-else>
                    <div class="flex mt2 align-items-center">
                        <div class="w20">
                            <p class="text-left input-label">
                                {{ $t('community.calendar.repeat-every') }}
                            </p>
                        </div>
                        <div class="w20 ml1">
                            <select v-model="repeatEveryVal" class="input" :disabled="isChildEvent">
                                <option v-for="repeatEveryValOpt in repeatEveryValOpts" :value="repeatEveryValOpt">
                                    {{ repeatEveryValOpt }}
                                </option>
                            </select>
                        </div>
                        <div class="w20 ml1">
                            <select v-model="repeatEveryUnit" class="input" :disabled="isChildEvent">
                                <option v-for="repeatEveryUnitOpt in repeatEveryUnitOpts" :value="repeatEveryUnitOpt">
                                    {{ repeatEveryUnitOptLabel(repeatEveryUnitOpt) }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex-1 mt2" v-if="repeatEveryUnit === 'week' || repeatEveryUnit === 'month' || repeatEveryUnit === 'year'">
                        <p class="text-left input-label">
                            {{ $t('community.calendar.repeat-on') }}
                        </p>

                        <div v-if="repeatEveryUnit === 'week'" class="mt1 flex">
                            <div v-for="(repeatOnDayOpt, index) in repeatOnDayOpts" class="flex ml1">
                                <input type="checkbox" v-model="repeatOnDayOpt.val" class="repeat-checkbox mr-05" :disabled="isChildEvent" />
                                <label for="repeat-checkbox" class="pointer" @click="checkRepeatOnDayOpt(repeatOnDayOpt.key)" >
                                    {{ repeatOnDayOptLabel(repeatOnDayOpt.key) }}
                                </label>
                            </div>
                        </div>

                        <div v-else-if="repeatEveryUnit === 'month'" class="mt1 w20">
                            <select v-model="repeatOnMonth" class="input" :disabled="isChildEvent">
                                <option disabled value="">{{ $t('community.calendar.repeat-on-month-placeholder') }}</option>
                                <option v-for="repeatOnMonthOpt in repeatOnMonthOpts" :value="repeatOnMonthOpt">
                                    {{ repeatOnMonthOpt }}
                                </option>
                            </select>
                        </div>

                        <div v-else-if="repeatEveryUnit === 'year'" class="mt1 w50">
                            <VueDatePicker v-model="repeartOnYear" :teleport-center="true" :format="formatWithoutYear" disable-year-select :disabled="isChildEvent" />
                        </div>
                    </div>

                    <div class="flex-1 mt2">
                        <p class="text-left input-label">
                            {{ $t('community.calendar.end') }}
                        </p>

                        <div
                            v-for="(endAtOpt, index) in endAtOpts"
                            :key="endAtOpt.key"
                            class="mt1 ml1"
                        >
                            <div class="flex align-items-center">
                                <div class="flex align-items-center pointer" @click="selectEndAtOptOption(endAtOpt.key)">
                                    <input
                                        type="radio"
                                        :id="endAtOpt.key"
                                        v-model="endAtOpt.selected"
                                        value="1"
                                        :disabled="isChildEvent"
                                    >

                                    <div class="ml-05" @click="selectEndAtOptOption(endAtOpt.key)">
                                        {{ endAtOpt.label }}
                                    </div>
                                </div>

                                <div class="ml1" v-if="endAtOpt.key === 'on'">
                                    <VueDatePicker v-model="endAtOn" :teleport-center="true" :format="format" :min-date="new Date()" :disabled="isChildEvent" />
                                </div>

                                <div class="ml1" v-if="endAtOpt.key === 'after'">
                                    <select v-model="endAtAfter" class="input" :disabled="isChildEvent">
                                        <option v-for="endAtAfterOpt in endAtAfterOpts" :value="endAtAfterOpt">
                                            {{ endAtAfterOpt }}
                                        </option>
                                    </select>
                                </div>

                                <div class="ml-05" v-if="endAtOpt.key === 'after'">
                                    {{ $t('community.calendar.occurences') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex mt2">
                <div class="flex-1">
                    <p class="text-left input-label">
                        {{ $t('community.community.description') }}
                    </p>
                    <textarea
                        class="textarea"
                        :placeholder="$t('community.community.description')"
                        v-model="calendarEvent.description"
                        rows="3"
                    />
                </div>
            </div>

            <div class="mt2 calendar-setting-error" v-if="invalidRepeatInfo">
                {{ $t('community.calendar.invalid-input-desc') }}
            </div>

            <div class="mt2">
                <div class="flex">
                    <div class="flex-1">
                        <p class="text-left input-label">
                            {{ $t('community.calendar.access-event') }}
                        </p>

                        <select class="input tab-content-select" v-model="calendarEvent.access_type">
                            <option v-for="accessTypeOpt in accessTypeOpts" :value="accessTypeOpt"
                                :selected="calendarEvent.access_type === accessTypeOpt">
                                {{ $t(`community.classroom.access-type-options.${accessTypeOpt}`) }}
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="calendarEvent.access_type === 'only_member'" class="flex mt2">
                    <div class="flex-1">
                        <p class="text-left input-label">
                            {{ $t('community.classroom.member-access') }}
                        </p>
                        <MemberMultiSelect v-model="eventAccessValue" :init-options="memberOptions" />
                    </div>
                </div>

                <div class="flex mt2">
                    <div class="flex-1">
                        <p class="text-left input-label">
                            {{ $t('community.classroom.level-access') }}
                        </p>

                        <select class="input tab-content-select" v-model="calendarEvent.level">
                            <option value="" disabled>{{ $t('community.classroom.level-access') }}</option>
                            <option v-for="accessLevelValueOpt in accessLevelValueOpts" :value="accessLevelValueOpt"
                                :selected="calendarEvent.level === accessLevelValueOpt">
                                {{ $t(`community.classroom.access-level-value-options.${accessLevelValueOpt}`) }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex mt2">
                <button
                    v-if="calendarShow === 'add'"
                    class="is-medium community-blue-btn mr-05"
                    :class="button"
                    :disabled="confirmDisabled"
                    @click="saveEvent"
                >
                    {{ $t('common.add') }}
                </button>
                <button
                    v-else-if="calendarShow === 'edit'"
                    class="is-medium community-blue-btn mr-05"
                    :class="button"
                    :disabled="confirmDisabled"
                    @click="saveEvent"
                >
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

import moment from 'moment'
import Pagination from "../../../../components/General/Elements/Pagination.vue";
import MemberMultiSelect from "../../../../components/General/MemberMultiSelect";

import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import zones from './../../../../data/timezones';

import UploadFile from "../../../General/UploadFile.vue";
import validateUrl from "../../../../mixins/util";
import isManager from "../../../../mixins/util";

export default {
	name: 'RulesSetting',
    mixins: [validateUrl, isManager],
    components: {
        Pagination,
        MemberMultiSelect,
        VueDatePicker,
        UploadFile
    },
    data () {
        return {
            durationOpts: [
                '0.5',
                '1',
                '1.5',
                '2',
                '2.5',
                '3',
                '3.5',
                '4',
                '4.5',
                '5'
            ],

            timezones: [],

            repeatEveryValOpts: [
                '1',
                '2',
                '3',
                '4',
                '5'
            ],

            repeatEveryUnitOpts: [
                'day',
                'week',
                'month',
                'year'
            ],

            repeatEveryVal: '1',
            repeatEveryUnit: 'week',

            repeatOnDayOpts: [{
                key: 'mon',
                val: false
            }, {
                key: 'tue',
                val: false
            }, {
                key: 'wed',
                val: false
            }, {
                key: 'thurs',
                val: false
            }, {
                key: 'fri',
                val: false
            }, {
                key: 'sat',
                val: false
            }, {
                key: 'sun',
                val: false
            }],

            repeatOnMonthOpts: [],

            repeatOnMonth: '',

            repeartOnYear: '',

            endAtOn: new Date(),
            endAtAfter: 10,

            endAtAfterOpts: [],

            endAtOpts: [{
                key: "on",
                label: this.$t('community.calendar.end-options.on'),
                selected: 1
            }, {
                key: "after",
                label: this.$t('community.calendar.end-options.after'),
                selected: 0
            }],

            owner: 'calendar',
            uploadingText: this.$t('common.uploading'),

            processing: false,

            changeToAll: 0,
            repeatDates: [],
            repeatTime: '',

            accessTypeOpts: ['all', 'only_member'],
            accessLevelValueOpts: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10']
        };
    },
    created () {
        this.timezones = zones;

        for (let i = 1; i < 31; i++) {
            this.repeatOnMonthOpts.push(i);
        }

        for (let i = 1; i < 11; i++) {
            this.endAtAfterOpts.push(i);
        }
    },
    mounted() {
        this.emitter.on("editEventTrigger", ev => {
            this.editEvent(ev);
        });
    },
    computed: {
        /**
         * Return auth status
         */
        auth ()
        {
            return this.$store.state.auth.loggedIn;
        },

        /**
         * Returns member
         */
        member ()
        {
            return this.$store.state.member.data;
        },

        /**
         * Returns access of member
         */
        access ()
        {
            return this.member ? this.member.access : null;
        },

        /**
         * Returns role of member
         */
        role ()
        {
            return this.member ? this.member.role : null;
        },

        /**
         * Returns user
         */
        user ()
        {
            return this.$store.state.auth.user;
        },

        /**
         * Returns community
         */
        community ()
        {
            return this.$store.state.community.data;
        },

        /**
         * Returns community events
         */
        communityEvents ()
        {
            return this.$store.state.communitycenter.communityEvents;
        },

        /**
         * Returns community events
         */
        monthlyEvents ()
        {
            return this.$store.state.communitycenter.monthlyEvents;
        },

        /**
         * Returns total of community events
         */
        total() {
            return this.$store.state.communitycenter.monthlyEvents?.total || 0;
        },

        /**
         * Returns current page of community events
         */
        currentPage() {
            return this.$store.state.communitycenter.monthlyEvents?.current_page || 0;
        },

        perPage() {
            return this.$store.state.communitycenter.monthlyEvents?.per_page || 1;
        },

        /**
         * Returns community posts
         */
        events ()
        {
            return (this.monthlyEvents.hasOwnProperty('data')) ? this.monthlyEvents.data : [];
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
        calendarShow ()
        {
            return this.communitySettings.calendarShow;
        },

        /**
         * Returns calendar event
         */
        calendarEvent ()
        {
            return this.communitySettings.calendarEvent;
        },

        /**
         * Returns event is child or not
         */
        isChildEvent ()
        {
            return typeof this.calendarEvent.origin_id !== 'undefined' && this.calendarEvent.origin_id > 0 ? true : false;
        },

        /**
         * Get | Set calendar event media
         */
        event_media: {
            get () {
                return this.calendarEvent.media;
            },
            set (v) {
                this.$store.commit('setCommunitySettingsCalendarEventProperty', {
                    key: 'media',
                    v
                });
            }
        },

        /**
         * Returns eventMonth
         */
        eventMonth ()
        {
            return this.$store.state.communitycenter.eventMonth;
        },

        /**
         * Returns month info
         */
        monthInfo ()
        {
            let monthInfo = '';
            if (this.eventMonth > 0) {
                monthInfo = moment().add(this.eventMonth, 'months').locale(this.$i18n.locale).format('MMMM YYYY');
            } else if (this.eventMonth < 0) {
                monthInfo = moment().subtract(Math.abs(this.eventMonth), 'months').locale(this.$i18n.locale).format('MMMM YYYY');
            } else {
                monthInfo = moment().locale(this.$i18n.locale).format('MMMM YYYY');
            }

            return monthInfo;
        },

        /**
         * Edit confirm disabled status
         */
        confirmDisabled ()
        {
            let confirmDisabled = true;
            if (this.calendarEvent !== null && this.calendarEvent.title !== '' && this.calendarEvent.duration !== '' && this.calendarEvent.timezone !== '' && !this.invalidRepeatInfo) {
                confirmDisabled = false;
            }

            return confirmDisabled;
        },

        /**
         * Returns invalid repeat info status
         */
        invalidRepeatInfo ()
        {
            let invalidRepeatInfo = false;
            if (this.calendarEvent.repeat) {
                if (this.repeatEveryUnit === 'week') {
                    let repeatDayExist = false;

                    this.repeatOnDayOpts.map(opt => {
                        if (opt.val) {
                            repeatDayExist = true;
                        }
                    });

                    if (!repeatDayExist) {
                        invalidRepeatInfo = true;
                    }
                } else if (this.repeatEveryUnit === 'month' && this.repeatOnMonth === '') {
                    invalidRepeatInfo = true;
                } else if (this.repeatEveryUnit === 'year' && this.repeartOnYear === '') {
                    invalidRepeatInfo = true;
                }
            }

            return invalidRepeatInfo;
        },

        /**
         * Add ' is-loading' when processing
         */
        button ()
        {
            let button = 'button ';

            return (this.processing)
                ? button + ' is-loading'
                : button;
        },

        /**
         * extra data of modal
         */
        childModalExtraData ()
        {
            return this.$store.state.modal.childModalExtraData;
        },

        /**
         * Returns community groups
         */
        groups() {
            return this.community.groups;
        },

        /**
         * Returns allowedMembers
         */
        allowedMembers ()
        {
            return this.$store.state.communitycenter.allowedMembers;
        },

        /**
         * Returns init options
         */
        memberOptions ()
        {
            let members = JSON.parse(JSON.stringify(this.allowedMembers));
            let memberOptions = [];
            if (members.length > 0) {
                members.map((member, index) => {
                    memberOptions.push({
                        id: member.id,
                        name: this.getMemberName(member),
                        avatar: this.getMemberGravatar(member)
                    });
                });
            }

            let groups = JSON.parse(JSON.stringify(this.groups));
            if (groups.length > 0) {
                groups.map((group, index) => {
                    memberOptions.push({
                        id: 'group_' + group.id,
                        name: group.name,
                        avatar: ''
                    });
                });
            }

            return memberOptions;
        },

        eventAccessValue: {
            get() {
                let accessValueItems = [];
                let accessType = this.calendarEvent.access_type;
                let accessValue = this.calendarEvent.access_value;

                if (accessType === 'only_member') {
                    let values = [];
                    if (accessValue) {
                        values = accessValue.split(",");
                    }
                    let groupIds = [];
                    let memberIds = [];

                    for (const elem of values) {
                        const value = elem.toString();
                        if (value.startsWith('group_')) {
                            groupIds.push(parseInt(value.replace('group_', '')));
                        } else {
                            memberIds.push(parseInt(value));
                        }
                    }

                    let members = JSON.parse(JSON.stringify(this.allowedMembers));
                    if (members.length > 0) {
                        members.map((member, index) => {
                            if (memberIds.includes(member.id)) {
                                accessValueItems.push({
                                    id: member.id,
                                    name: this.getMemberName(member),
                                    avatar: this.getMemberGravatar(member)
                                });
                            }
                        });
                    }

                    let groups = JSON.parse(JSON.stringify(this.groups));
                    if (groups.length > 0) {
                        groups.map((group, index) => {
                            if (groupIds.includes(group.id)) {
                                accessValueItems.push({
                                    id: 'group_' + group.id,
                                    name: group.name,
                                    avatar: ''
                                });
                            }
                        });
                    }
                }

                return accessValueItems;
            },
            set(v) {
                let memberIds = [];
                if (Array.isArray(v)) {
                    v.map(member => {
                        memberIds.push(member.id);
                    });
                } else if (typeof v === 'object') {
                    memberIds.push(v.id);
                }

                this.calendarEvent.access_value = memberIds.join(",");
            }
        },
    },
	methods: {
        resetCalendarEvent ()
        {
            this.repeatEveryVal = '1';
            this.repeatEveryUnit = 'week';
            this.repeatOnMonth = '';
            this.repeartOnYear = '';
            this.endAtOn = new Date();
            this.endAtAfter = 10;

            this.endAtOpts = this.endAtOpts.map(opt => {
                opt.selected = 0;
                if (opt.key === 'on') {
                    opt.selected = 1;
                }

                return { ...opt };
            });

            this.repeatOnDayOpts = this.repeatOnDayOpts.map(opt => {
                opt.val = false;
                
                return { ...opt };
            });

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'calendarEvent',
                v: {
                    id: 0,
                    community_id: this.community.id,
                    title: '',
                    description: '',
                    start_at: new Date(),
                    end_at: '',
                    duration: '',
                    repeat: false,
                    repeat_every: '',
                    repeat_on: '',
                    timezone: '',
                    media: '',
                    link: '',
                    type: '',
                    location: '',
                    access_type: 'all',
                    access_value: '',
                    level: 1,
                    origin_id: 0,
                    change_to_all: 0
                }
            });
        },

        durationLabel (duration)
        {
            let suffix = this.$t('community.calendar.hour');
            if (parseFloat(duration) > 1) {
                suffix = this.$t('community.calendar.hours');
            }

            return duration + ' ' + suffix;
        },

        repeatEveryUnitOptLabel (val)
        {
            return this.$t('community.calendar.repeat-every-options.' + val);
        },

        selectEndAtOptOption (key)
        {
            this.endAtOpts = this.endAtOpts.map(opt => {
                opt.selected = 0;
                if (opt.key === key) {
                    opt.selected = 1;
                }

                return { ...opt };
            });
        },

        addEvent ()
        {
            this.resetCalendarEvent();

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'calendarShow',
                v: 'add'
            });
        },

        /**
         * Check recurring checkbox
         */
        checkRecurringBox ()
        {
            this.calendarEvent.repeat = !this.calendarEvent.repeat;
        },

        /**
         * Check recurring checkbox
         */
        checkRepeatOnDayOpt (key)
        {
            this.repeatOnDayOpts = this.repeatOnDayOpts.map(opt => {
                if (opt.key === key) {
                    opt.val = !opt.val;
                }

                return { ...opt };
            });
        },

        repeatOnDayOptLabel (key)
        {
            return this.$t('community.calendar.repeat-on-day-options.' + key);
        },

        goToEditEvent (event)
        {
            if (event.repeat_every && event.repeat_on) {
                this.$store.commit('showChildModal', {
                    modalType: 'EditRecurringEvent',
                    extraData: event
                });
            } else {
                this.editEvent(event);
            }
        },

        editEvent (event)
        {
            this.changeToAll = event.change_to_all;
            
            let eventDateArray = event.start_at.split(' ');

            this.repeatDates = [];
            this.repeatTime = '';

            if (this.changeToAll) {
                if (this.communityEvents.length > 0) {

                    let originId = 0;
                    if (event.origin_id > 0) {
                        originId = event.origin_id;
                    } else {
                        originId = event.id;
                    }

                    this.communityEvents.map((item, index) => {
                        if (item.id === originId || item.origin_id === originId) {
                            let repeatEventDateArray = item.start_at.split(' ');
                            this.repeatDates.push({
                                id: item.id,
                                text: repeatEventDateArray[0]
                            });
                        }
                    });
                }
            } else {
                let repeatEventDateArray = event.start_at.split(' ');
                this.repeatDates.push({
                    id: event.id,
                    text: repeatEventDateArray[0]
                });
            }

            this.repeatTime = this.formatTime(eventDateArray[1]);

            let repeatEvery = event.repeat_every;
            if (repeatEvery) {
                let repeatEveryArray = repeatEvery.split("_");
                if (repeatEveryArray.length > 1) {
                    event.repeat = true;
                    this.repeatEveryVal = repeatEveryArray[0];
                    this.repeatEveryUnit = repeatEveryArray[1];
                }

                let repeatOn = event.repeat_on;

                if (this.repeatEveryUnit === 'week' && repeatOn.startsWith('week')) {
                    let repeatOnVal = repeatOn.replace('week_', '');
                    let repeatOnValArray = repeatOnVal.split("-");
                    if (repeatOnValArray.length > 0) {
                        this.repeatOnDayOpts = this.repeatOnDayOpts.map(opt => {
                            opt.val = false;
                            if (repeatOnValArray.includes(opt.key)) {
                                opt.val = true;
                            }

                            return { ...opt };
                        });
                    }
                } else if (this.repeatEveryUnit === 'month' && repeatOn.startsWith('month')) {
                    this.repeatOnMonth = repeatOn.replace('month_', '');
                } else if (this.repeatEveryUnit === 'year' && repeatOn.startsWith('year')) {
                    this.repeartOnYear = repeatOn.replace('year_', '');
                }

                let endAt = event.end_at;

                this.endAtOpts = this.endAtOpts.map(opt => {
                    opt.selected = 0;
                    if (endAt.startsWith(opt.key)) {
                        opt.selected = 1;
                    }

                    return { ...opt };
                });

                if (endAt.startsWith('on')) {
                    this.endAtOn = endAt.replace('on_', '');
                } else if (endAt.startsWith('after')) {
                    this.endAtAfter = endAt.replace('after_', '');
                }
            } else {
                event.repeat = false;
            }

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'calendarEvent',
                v: event
            });
            
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'calendarShow',
                v: 'edit'
            });
        },

        close ()
        {
            this.$store.commit('setCommunitySettingsProperty', {
                key: 'calendarShow',
                v: 'list'
            });
        },

        goToDeleteEvent (event)
        {
            if (event.repeat_every && event.repeat_on) {
                this.$store.commit('showChildModal', {
                    modalType: 'DeleteRecurringEvent',
                    extraData: event
                });
            } else {
                this.deleteEvent(event);
            }
        },

        async deleteEvent (event)
        {
            await this.$store.dispatch('DELETE_EVENT', event);
        },

        async saveEvent ()
        {
            this.processing = true;

            let calendarEvent = JSON.parse(JSON.stringify(this.calendarEvent));
            calendarEvent.start_at = moment(calendarEvent.start_at).format("YYYY-MM-DD HH:mm:00");

            if (calendarEvent.repeat) {
                let selectEndOpt = 'on';
                this.endAtOpts.map(opt => {
                    if (parseInt(opt.selected) === 1) {
                        selectEndOpt = opt.key;
                    }
                });

                let endAtSuffix = '';
                if (selectEndOpt === 'on') {
                    endAtSuffix = moment(this.endAtOn).format("YYYY-MM-DD HH:mm:00");
                } else if (selectEndOpt === 'after') {
                    endAtSuffix = this.endAtAfter;
                }

                if (endAtSuffix === '') {
                    calendarEvent.end_at = selectEndOpt;
                } else {
                    calendarEvent.end_at = selectEndOpt + '_' + endAtSuffix;
                }

                calendarEvent.repeat_every = this.repeatEveryVal + '_' + this.repeatEveryUnit;

                let eventRepeatOn = '';

                if (this.repeatEveryUnit === 'week') {
                    let repeatDayExist = false;

                    this.repeatOnDayOpts.map(opt => {
                        if (opt.val) {
                            if (eventRepeatOn !== '') {
                                eventRepeatOn += '-';
                            }

                            eventRepeatOn += opt.key;
                        }
                    });
                } else if (this.repeatEveryUnit === 'month') {
                    eventRepeatOn = this.repeatOnMonth;
                } else if (this.repeatEveryUnit === 'year') {
                    eventRepeatOn = moment(this.repeartOnYear).format("YYYY-MM-DD HH:mm:00");
                }

                calendarEvent.repeat_on = this.repeatEveryUnit + '_' + eventRepeatOn;
            } else {
                calendarEvent.repeat_every = '';
            }

            this.$store.commit('setCommunitySettingsProperty', {
                key: 'calendarEvent',
                v: calendarEvent
            });

            await this.$store.dispatch('SAVE_EVENT');

            this.processing = false;

            this.close();
        },

        getEventDate (date) {
            let eventDate = '';
            if (date) {
                eventDate = moment(date).locale(this.$i18n.locale).format('ddd, MMM DD @ h:mm a');
            }

            return eventDate;
        },

        getEventEndDate (event) {
            let eventEndDate = '';
            let endAt = event.end_at;
            if (endAt && endAt.startsWith('on_')) {
                eventEndDate = moment(endAt.replace('on_', '')).locale(this.$i18n.locale).format('ddd, MMM DD @ h:mm a');
            } else if (event.duration !== '') {
                let durationMin = parseFloat(event.duration) * 60;
                eventEndDate = moment(event.start_at).add(durationMin, 'minutes').locale(this.$i18n.locale).format('ddd, MMM DD @ h:mm a');
            }

            return eventEndDate;
        },

        /**
         * Decrease month
         */
        async decreaseMonth()
        {
            this.$store.commit('decreaseMonth');
            await this.$store.dispatch('GET_MONTHLY_EVENTS');
        },

        /**
         * Increase month
         */
        async increaseMonth()
        {
            this.$store.commit('increaseMonth');
            await this.$store.dispatch('GET_MONTHLY_EVENTS');
        },

        format (date) {
            let hour = date.getHours();
            if (parseInt(hour) < 10) {
                hour = '0' + date.getHours();
            }

            let minute = date.getMinutes();
            if (parseInt(minute) < 10) {
                minute = '0' + date.getMinutes();
            }

            return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + ' ' + hour + ':' + minute + ':00';
        },

        formatWithoutYear (date) {
            let hour = date.getHours();
            if (parseInt(hour) < 10) {
                hour = '0' + date.getHours();
            }

            let minute = date.getMinutes();
            if (parseInt(minute) < 10) {
                minute = '0' + date.getMinutes();
            }

            return (date.getMonth() + 1) + '-' + date.getDate() + ' ' + hour + ':' + minute + ':00';
        },

        formatTime (timeString) {
            const momentTime = moment(timeString, 'H:i:s');
            return momentTime.format('h:mm A');
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
    .calendar-setting-error {
        color: #e74c3c;
        font-size: 12px;
    }

    .repeat-date-text {
        font-size: 13px;
        padding: 2px 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        background-color: #eee;
        margin-right: 3px;
    }
</style>
