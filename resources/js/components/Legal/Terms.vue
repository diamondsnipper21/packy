<template>
    <div class="box legal-box p-1-5">
        <div class="privacy-title">
            {{ $t('privacy.terms.title-1') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.terms.header-html')"></div>
        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-1') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-1') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-2') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-2.desc') }}
            <br/><br/>
            <ul class="pl-1-5" style="list-style-type: lower-alpha;">
                <li>
                    {{ $t('privacy.terms.sub-content-2.opt-1') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-2.opt-2') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-2.opt-3') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-2.opt-4') }}
                </li>
            </ul>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-3') }}
        </div>
        <div class="p-0-5">
            <ul class="pl-1-5" style="list-style-type: lower-alpha;">
                <li v-html="$t('privacy.terms.sub-content-3.opt-1')"></li>
                <li v-html="$t('privacy.terms.sub-content-3.opt-2')"></li>
                <li v-html="$t('privacy.terms.sub-content-3.opt-3')"></li>
                <li v-html="$t('privacy.terms.sub-content-3.opt-4')"></li>
            </ul>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-4') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-4.desc') }}
            <br/><br/>
            <ul class="pl-1-5" style="list-style-type: lower-alpha;">
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-1') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-2') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-3') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-4') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-5') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-6') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-7') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-8') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-9') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-10') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-11') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-12') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-13') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-14') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-15') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-16') }}
                </li>
                <li>
                    {{ $t('privacy.terms.sub-content-4.opt-17') }}
                </li>
            </ul>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-5') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-5') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-6') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.terms.sub-content-6').replaceAll('#contact_mail#', contactMail)"></div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-7') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.terms.sub-content-7')"></div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-8') }}
        </div>
        <div class="p-0-5">
            <ul class="pl-1-5" style="list-style-type: lower-alpha;">
                <li v-html="$t('privacy.terms.sub-content-8.opt-1')"></li>
                <li v-html="$t('privacy.terms.sub-content-8.opt-2').replaceAll('#contact_mail#', contactMail)"></li>
                <li v-html="$t('privacy.terms.sub-content-8.opt-3')"></li>
                <li v-html="$t('privacy.terms.sub-content-8.opt-4')"></li>
                <li v-html="$t('privacy.terms.sub-content-8.opt-5')"></li>
                <li v-html="$t('privacy.terms.sub-content-8.opt-6')"></li>
            </ul>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-9') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-9') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-10') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-10') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-11') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.terms.sub-content-11')"></div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-12') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-12') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-13') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-13') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-14') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.terms.sub-content-14')"></div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-15') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.terms.sub-content-15')"></div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-16') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-16.desc') }}
            <br/><br/>
            <ul class="pl-1-5" style="list-style-type: lower-alpha;">
                <li v-html="$t('privacy.terms.sub-content-16.opt-1')"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-2').replaceAll('#contact_mail#', contactMail)"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-3')"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-4')"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-5')"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-6')"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-7')"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-8').replaceAll('#contact_mail#', contactMail)"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-9')"></li>
                <li v-html="$t('privacy.terms.sub-content-16.opt-10')"></li>
            </ul>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-17') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-17') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-18') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.sub-content-18') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-19') }}
        </div>
        <div class="p-0-5">
            <ul class="pl-1-5" style="list-style-type: lower-alpha;">
                <li v-html="$t('privacy.terms.sub-content-19.opt-1')"></li>
                <li v-html="$t('privacy.terms.sub-content-19.opt-2')"></li>
                <li v-html="$t('privacy.terms.sub-content-19.opt-3')"></li>
                <li v-html="$t('privacy.terms.sub-content-19.opt-4')"></li>
                <li v-html="$t('privacy.terms.sub-content-19.opt-5')"></li>
            </ul>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.terms.sub-title-20') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.terms.sub-content-20').replaceAll('#contact_mail#', contactMail)"></div>


        <div class="privacy-title mt1">
            {{ $t('privacy.terms.title-2') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.content-2') }}
        </div>

        <div class="privacy-title mt1">
            {{ $t('privacy.terms.title-3') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.content-3') }}
        </div>

        <div class="privacy-title mt1">
            {{ $t('privacy.terms.title-4') }}
        </div>
        <div class="p-0-5">
            <ul class="pl-1-5" style="list-style-type: disc;">
                <li>
                    {{ $t('privacy.terms.content-4.opt-1') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-4.opt-2') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-4.opt-3') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-4.opt-4') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-4.opt-5') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-4.opt-6') }}
                </li>
            </ul>
            <div v-html="$t('privacy.terms.content-4.desc').replaceAll('#contact_mail#', contactMail)"></div>
        </div>

        <div class="privacy-title mt1">
            {{ $t('privacy.terms.title-5') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.terms.content-5.desc-1') }}
            <br/><br/>
            <ul class="pl-1-5" style="list-style-type: disc;">
                <li>
                    {{ $t('privacy.terms.content-5.opt-1') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-5.opt-2') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-5.opt-3') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-5.opt-4') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-5.opt-5') }}
                </li>
                <li>
                    {{ $t('privacy.terms.content-5.opt-6') }}
                </li>
            </ul>
            <br/>
            {{ $t('privacy.terms.content-5.desc-2') }}
        </div>
    </div>
</template>

<script>

export default {
    name: 'Terms',
    data () {
        return {
            contactMail: 'help@packy.com'
        };
    }
}
</script>
