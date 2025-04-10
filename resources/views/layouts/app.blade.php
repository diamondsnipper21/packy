<!DOCTYPE html>
<html>

<head>
    @include('includes.header')

    <script>
        // Apply background color to team member login
        function getBackgroundStyle() {
            document.documentElement.style = 'background-color: #f4f5f8; overflow: auto;';
        }

        getBackgroundStyle()
    </script>

    <link href="{{ mix('css/app.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/common.css') }}" type="text/css" rel="stylesheet">
</head>

<body>
    <div
        id="app"
        :style="getContainer"
        auth="{{ $auth ?? false }}"
        action="{{ $action ?? false }}"
        communityUrl="{{ $communityUrl ?? false }}"
        userId="{{ $userId ?? false }}"
        userTag="{{ $userTag ?? false }}"
        inviteToken="{{ $inviteToken ?? false }}">
        @yield('content')
    </div>
</body>

</html>
<script>
    const stripeKey = "{{config('payment.stripe.subscriptions_public_key')}}"
    const stripeKeyMarketplace = "{{config('payment.stripe.marketplace_public_key')}}"
</script>
<script src="{{ mix('/js/app.js') }}"></script>