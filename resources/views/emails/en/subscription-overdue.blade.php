@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $memberName }}</b>,
    <br /><br />
    We would like to inform you that the payment for your subscription to community <b>{{ $communityName }}</b> in the amount of {{ number_format($subscription['amount'], 2, ',', '') }} {{ $subscription['currency'] }} was unsuccessful during the attempted transaction.
    <br /><br />
    It appears that your registered payment method (<b>{{ strtoupper($card['brand']) }} / {{ $card['last4'] }}</b>) was declined by your bank or encountered a technical issue.
    <br /><br />
    To avoid any interruption to your subscription, please:<br />
    <ul style="list-style-type: none">
        <li>1. Check your payment details in <a href="{{ $communityUrl }}">your account</a>.</li>
        <li>2. Update your billing information if necessary.</li>
        <li>3. Contact your bank if the issue persists.</li>
    </ul>
    If you have already resolved this issue, please disregard this email.
    <br /><br />
    Thank you for your attention and for being a valued customer.
@stop