<template>
    <div class="box legal-box p-1-5">
        <div class="privacy-title">
            {{ $t('privacy.privacy.title') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.privacy.header.html')"></div>
        


        <div class="p-0-5">
            <ol class="pl-1-5">
                <li v-html="$t('privacy.privacy.header.opt-1')"></li>
                <li v-html="$t('privacy.privacy.header.opt-2')"></li>
                <li v-html="$t('privacy.privacy.header.opt-3')"></li>
                <li v-html="$t('privacy.privacy.header.opt-4')"></li>
                <li v-html="$t('privacy.privacy.header.opt-5')"></li>
                <li v-html="$t('privacy.privacy.header.opt-6')"></li>
                <li v-html="$t('privacy.privacy.header.opt-7')"></li>
                <li v-html="$t('privacy.privacy.header.opt-8')"></li>
                <li v-html="$t('privacy.privacy.header.opt-9')"></li>
                <li v-html="$t('privacy.privacy.header.opt-10')"></li>
                <li v-html="$t('privacy.privacy.header.opt-11')"></li>
                <li v-html="$t('privacy.privacy.header.opt-12')"></li>
                <li v-html="$t('privacy.privacy.header.opt-13')"></li>
            </ol>
        </div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-1')"></div>

        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-1')"></div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-2')"></div>

        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-2')"></div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-3')"></div>

        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-3')"></div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-4')"></div>
        <div class="p-0-5">
            {{ $t('privacy.privacy.sub-content-4') }}

            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-4-1.title') }}
            </div>
            <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-4-1.content')"></div>

            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-4-2.title') }}
            </div>
            <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-4-2.content')"></div>

            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-4-3.title') }}
            </div>
            <div class="p-0-5">
                {{ $t('privacy.privacy.sub-content-4-3.content') }}
            </div>
        </div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-5')"></div>
        <div class="p-0-5">
            {{ $t('privacy.privacy.sub-content-5.desc-1') }}
            <br/><br/>
            <ul class="pl-1-5" style="list-style-type: disc;">
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-1') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-2') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-3') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-4') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-5') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-6') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-7') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-8') }}
                </li>
                <li>
                    {{ $t('privacy.privacy.sub-content-5.opt-9') }}
                </li>
            </ul>
            <br/>
            {{ $t('privacy.privacy.sub-content-5.desc-2') }}
        </div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-6')"></div>
        <div class="p-0-5">
            {{ $t('privacy.privacy.sub-content-6.desc-1') }}
            <br/><br/>
            <ul class="pl-1-5" style="list-style-type: disc;">
                <li v-html="$t('privacy.privacy.sub-content-6.opt-1')"></li>
                <li v-html="$t('privacy.privacy.sub-content-6.opt-2')"></li>
                <li v-html="$t('privacy.privacy.sub-content-6.opt-3')"></li>
            </ul>
            <br/>
            <div v-html="$t('privacy.privacy.sub-content-6.desc-2')"></div>
        </div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-7')"></div>
        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-7')"></div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-8')"></div>
        <div class="p-0-5">
            {{ $t('privacy.privacy.sub-content-8') }}
        </div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-9')"></div>
        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-9')"></div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-10')"></div>
        <div class="p-0-5">
            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-10-1.title') }}
            </div>
            <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-10-1.content')"></div>

            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-10-2.title') }}
            </div>
            <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-10-2.content')"></div>

            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-10-3.title') }}
            </div>
            <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-10-3.content')"></div>


            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-10-4.title') }}
            </div>
            <div class="p-0-5">
                {{ $t('privacy.privacy.sub-content-10-4.content') }}
            </div>

            <div class="privacy-sub-sub-title mt1">
                {{ $t('privacy.privacy.sub-content-10-5.title') }}
            </div>
            <div class="p-0-5">
                {{ $t('privacy.privacy.sub-content-10-5.content') }}
            </div>
        </div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-11')"></div>
        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-11')"></div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-12')"></div>
        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-12')"></div>

        <div class="privacy-sub-title mt1" v-html="$t('privacy.privacy.sub-title-13')"></div>
        <div class="p-0-5" v-html="$t('privacy.privacy.sub-content-13').replaceAll('#contact_mail#', contactMail)"></div>
    </div>
</template>

<script>


export default {
    name: 'Privacy',
    data () {
        return {
            contactMail: 'help@packy.com'
        };
    }
}
</script>
