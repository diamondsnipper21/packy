<template>
    <div class="box legal-box p-1-5">
        <div class="privacy-title">
            {{ $t('privacy.legal.title') }}
        </div>
        <div v-html="$t('privacy.legal.header-html')" class="p-0-5"></div>
        <div class="privacy-sub-title mt1">
            {{ $t('privacy.legal.sub-title') }}
        </div>
        <div v-html="$t('privacy.legal.content-html').replaceAll('#contact_mail#', contactMail)" class="p-0-5"></div>
    </div>
</template>

<script>


export default {
    name: 'Legal',
    data () {
        return {
            contactMail: 'help@packy.com'
        };
    }
}
</script>
