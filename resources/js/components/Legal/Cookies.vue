<template>
    <div class="box legal-box p-1-5">
        <div class="privacy-title">
            {{ $t('privacy.cookies.title') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.cookies.header-content') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.cookies.sub-title-1') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.cookies.sub-content-1.desc') }}
            <br/>
            
            <table class="w100 mt1">
                <thead>
                    <tr>
                        <th style="width: 20%; text-align: center;">{{ $t('privacy.cookies.sub-content-1.table.header-1') }}<br/><br/></th>
                        <th style="text-align: center;">{{ $t('privacy.cookies.sub-content-1.table.header-2') }}<br/><br/></th>
                        <th style="width: 20%; text-align: center;">{{ $t('privacy.cookies.sub-content-1.table.header-3') }}<br/><br/></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="no-border">
                            {{ $t('privacy.cookies.sub-content-1.table.row-1-1') }}<br/><br/>
                        </td>
                        <td class="no-border">
                            {{ $t('privacy.cookies.sub-content-1.table.row-1-2') }}<br/><br/>
                        </td>
                        <td class="no-border" v-html="$t('privacy.cookies.sub-content-1.table.row-1-3')"></td>
                    </tr>
                    <tr>
                        <td class="no-border">
                            {{ $t('privacy.cookies.sub-content-1.table.row-2-1') }}
                        </td>
                        <td class="no-border">
                            {{ $t('privacy.cookies.sub-content-1.table.row-2-2') }}
                        </td>
                        <td class="no-border" v-html="$t('privacy.cookies.sub-content-1.table.row-2-3')"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.cookies.sub-title-2') }}
        </div>
        <div class="p-0-5">
            {{ $t('privacy.cookies.sub-content-2') }}
        </div>

        <div class="privacy-sub-title mt1">
            {{ $t('privacy.cookies.sub-title-3') }}
        </div>
        <div class="p-0-5" v-html="$t('privacy.cookies.sub-content-3')"></div>
    </div>
</template>

<script>
export default {
    name: 'Cookies',
    data () {
        return {
            contactMail: 'help@packy.com'
        };
    }
}
</script>
