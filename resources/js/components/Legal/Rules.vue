<template>
    <div class="box legal-box p-1-5">
        <div class="privacy-title">
            {{ $t('privacy.rules.title') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.rules.header-html')"></div>
        <div class="privacy-sub-title mt1">
            {{ $t('privacy.rules.sub-title-1') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.rules.sub-content-1.desc') }}
            <ul class="pl-1-5" style="list-style-type: disc;">
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-1') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-2') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-3') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-4') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-5') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-6') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-7') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-8') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-9') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-10') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-11') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-12') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-13') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-14') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-15') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-16') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-17') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-18') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-19') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-20') }}
                </li>
                <li>
                    {{ $t('privacy.rules.sub-content-1.opt-21') }}
                </li>
            </ul>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.rules.sub-title-2') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.rules.sub-content-2').replaceAll('#contact_mail#', contactMail)">
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.rules.sub-title-3') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.rules.sub-content-3')"></div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.rules.sub-title-4') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.rules.sub-content-4') }}
        </div>
    </div>
</template>

<script>


export default {
    name: 'Rules',
    data () {
        return {
            contactMail: 'help@packy.com'
        };
    }
}
</script>

<style scoped>
    
</style>
