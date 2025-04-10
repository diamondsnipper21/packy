<template>
    <div class="inner-modal-container">
        <div class="relative">
            <div class="top-left">
                <font-awesome-icon icon="fa fa-arrow-left" class="" @click="close" />
            </div>
            <p class="title is-4 font-weight-600">
                {{ $t('community.community.add-post.schedule-post-title') }}
            </p>
        </div>

        <div class="mt1">
            <div class="font-16px text-left">
                {{ $t('community.community.add-post.schedule-post-desc') }}
            </div>
        </div>

        <div class="flex mt2">
            <div class="w100">
                <p class="text-left input-label">
                    {{ $t('community.community.add-post.publish-at') }}
                </p>
                <VueDatePicker v-model="communityPost.publish_at" :teleport-center="true" :format="format"
                    :min-date="new Date()" />
            </div>

            <div class="w100 ml1">
                <p class="text-left input-label">
                    {{ $t('community.calendar.timezone') }}
                </p>

                <select v-model="communityPost.publish_timezone" class="input">
                    <option value="" disabled>{{ $t('community.calendar.timezone') }}</option>
                    <option v-for="timezone in timezones" :value="timezone.key">
                        {{ timezone.val }}
                    </option>
                </select>
            </div>
        </div>

        <div class="flex mt2">
            <input type="checkbox" v-model="communityPost.repeat" class="repeat-checkbox mr1" />
            <label for="repeat-checkbox" class="pointer" @click="checkRecurringBox">{{
                $t('community.calendar.recurring-event') }}</label>
        </div>

        <div v-if="communityPost.repeat" class="w100">
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

            <div class="flex-1 mt2"
                v-if="repeatEveryUnit === 'week' || repeatEveryUnit === 'month' || repeatEveryUnit === 'year'">
                <p class="text-left input-label">
                    {{ $t('community.calendar.repeat-on') }}
                </p>

                <div v-if="repeatEveryUnit === 'week'" class="mt1 flex">
                    <div v-for="(repeatOnDayOpt, index) in repeatOnDayOpts" class="flex ml1">
                        <input type="checkbox" v-model="repeatOnDayOpt.val" class="repeat-checkbox mr-05"
                            :disabled="isChildEvent" />
                        <label for="repeat-checkbox" class="pointer" @click="checkRepeatOnDayOpt(repeatOnDayOpt.key)">
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
                    <VueDatePicker v-model="repeartOnYear" :teleport-center="true" :format="formatWithoutYear"
                        disable-year-select :disabled="isChildEvent" />
                </div>
            </div>

            <div class="flex-1 mt2">
                <p class="text-left input-label">
                    {{ $t('community.calendar.end') }}
                </p>

                <div v-for="(endAtOpt, index) in endAtOpts" :key="endAtOpt.key" class="mt1 ml1">
                    <div class="flex align-items-center">
                        <div class="flex align-items-center pointer" @click="selectEndAtOptOption(endAtOpt.key)">
                            <input type="radio" :id="endAtOpt.key" v-model="endAtOpt.selected" value="1"
                                :disabled="isChildEvent">

                            <div class="ml-05" @click="selectEndAtOptOption(endAtOpt.key)">
                                {{ endAtOpt.label }}
                            </div>
                        </div>

                        <div class="ml1" v-if="endAtOpt.key === 'on'">
                            <VueDatePicker v-model="endAtOn" :teleport-center="true" :format="format"
                                :min-date="new Date()" :disabled="isChildEvent" />
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
        </div>

        <div class="mt2 calendar-setting-error" v-if="invalidRepeatInfo">
            {{ $t('community.community.add-post.invalid-input-desc') }}
        </div>

        <button class="button is-medium w100 community-blue-btn text-uppercase mt2" :class="button"
            :disabled="disabledConfirm" @click="saveScheduledPost">
            {{ $t('community.community.add-post.schedule') }}
        </button>
    </div>
</template>

<script>

import moment from 'moment'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import zones from './../../data/timezones';

export default {
    name: 'SchedulePost',
    components: {
        VueDatePicker
    },
    data() {
        return {
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

            processing: false
        };
    },
    created() {
        this.timezones = zones;

        for (let i = 1; i < 31; i++) {
            this.repeatOnMonthOpts.push(i);
        }

        for (let i = 1; i < 11; i++) {
            this.endAtAfterOpts.push(i);
        }

        this.resetRecurringProps()
    },
    watch: {
        '$store.state.post.data': function () {
            this.resetRecurringProps()
        },
    },
    computed: {
        /**
         * Returns community
         */
        community() {
            return this.$store.state.community.data;
        },

        /**
         * Return community broadcast
         */
        broadcast() {
            return this.$store.state.community.broadcast;
        },

        /**
         * Return online coach community post
         */
        communityPost() {
            return this.$store.state.post.data;
        },

        /**
         * Returns event is child or not
         */
        isChildEvent() {
            return typeof this.communityPost.origin_id !== 'undefined' && this.communityPost.origin_id > 0 ? true : false;
        },

        /**
         * Returns invalid repeat info status
         */
        invalidRepeatInfo() {
            let invalidRepeatInfo = false;
            if (this.communityPost.repeat) {
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
         * Returns disabled confirm status class
         */
        disabledConfirm() {
            let disabledConfirm = true;
            if (this.communityPost.publish_at !== '' && this.communityPost.publish_timezone !== '' && !this.invalidRepeatInfo) {
                disabledConfirm = false;
            }

            return disabledConfirm;
        },

        /**
         * Returns button status class
         */
        button() {
            return this.processing ? ' is-loading' : '';
        },
    },
    methods: {
        /**
         * Check recurring checkbox
         */
        checkRecurringBox() {
            this.communityPost.repeat = !this.communityPost.repeat;
        },

        /**
         * Check recurring checkbox
         */
        checkRepeatOnDayOpt(key) {
            this.repeatOnDayOpts = this.repeatOnDayOpts.map(opt => {
                if (opt.key === key) {
                    opt.val = !opt.val;
                }

                return { ...opt };
            });
        },

        resetRecurringProps() {
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

            // Load Schedule post
            if (this.communityPost.id) {
                let repeatEvery = this.communityPost.repeat_every;
                if (repeatEvery) {
                    let repeatEveryArray = repeatEvery.split("_");
                    if (repeatEveryArray.length > 1) {
                        this.communityPost.repeat = true;
                        this.repeatEveryVal = repeatEveryArray[0];
                        this.repeatEveryUnit = repeatEveryArray[1];
                    }

                    let repeatOn = this.communityPost.repeat_on;

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
                } else {
                    this.communityPost.repeat = false;
                }

                let endAt = this.communityPost.repeat_end_at || '';

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
            }
        },

        format(date) {
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

        formatWithoutYear(date) {
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

        /**
         * Check recurring checkbox
         */
        checkRepeatOnDayOpt(key) {
            this.repeatOnDayOpts = this.repeatOnDayOpts.map(opt => {
                if (opt.key === key) {
                    opt.val = !opt.val;
                }

                return { ...opt };
            });
        },

        repeatOnDayOptLabel(key) {
            return this.$t('community.calendar.repeat-on-day-options.' + key);
        },

        repeatEveryUnitOptLabel(val) {
            return this.$t('community.calendar.repeat-every-options.' + val);
        },

        selectEndAtOptOption(key) {
            this.endAtOpts = this.endAtOpts.map(opt => {
                opt.selected = 0;
                if (opt.key === key) {
                    opt.selected = 1;
                }

                return { ...opt };
            });
        },

        /**
         * Close the modal
         */
        close() {
            this.$store.commit('resetCommunityPostScheduleProps');
            this.$store.commit('hideModal');
        },

        /**
         * Save scheduled post
         */
        async saveScheduledPost() {
            this.processing = true;

            let communityPost = JSON.parse(JSON.stringify(this.communityPost));
            communityPost.publish_at = moment(communityPost.publish_at).format("YYYY-MM-DD HH:mm:00");

            if (communityPost.repeat) {
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
                    communityPost.repeat_end_at = selectEndOpt;
                } else {
                    communityPost.repeat_end_at = selectEndOpt + '_' + endAtSuffix;
                }

                communityPost.repeat_every = this.repeatEveryVal + '_' + this.repeatEveryUnit;

                let eventRepeatOn = '';

                if (this.repeatEveryUnit === 'week') {
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

                communityPost.repeat_on = this.repeatEveryUnit + '_' + eventRepeatOn;
            } else {
                communityPost.repeat_every = '';
            }

            communityPost.scheduled = true;
            delete communityPost.member;
            if (communityPost.id == 0) {
                this.$store.commit('setCommunityPost', communityPost);
                await this.$store.dispatch('CREATE_SCHEDULED_POST', {
                    communityId: this.community.id,
                    sendEmail: this.broadcast
                });
            } else {
                communityPost.scheduledInfo = true;
                delete communityPost.polls;
                delete communityPost.medias;
                this.$store.commit('setCommunityPost', communityPost);

                await this.$store.dispatch('UPDATE_SCHEDULED_POST', {
                    communityId: this.community.id,
                    id: communityPost.id,
                    sendEmail: this.broadcast
                });
            }

            this.close();

            this.$store.commit('resetCommunityPostScheduleProps');
            this.$store.commit('setAddPostShow', false);

            this.processing = false;
        },
    }
}
</script>

<style scoped>
.top-left {
    position: absolute;
    top: 0;
    left: 0em;
    width: 30px;
    height: 30px;
    cursor: pointer;
    z-index: 9999;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.top-left:hover {
    background-color: rgb(228, 228, 228);
}
</style>
