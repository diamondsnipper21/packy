@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $memberName }}</b>,
    <br /><br />
    This is a reminder that your subscription for community <b>{{ $communityName }}</b> in the amount of {{ number_format($subscription['amount'], 2, ',', '') }} {{ $subscription['currency'] }} will automatically renew on {{ $nextBillingDate }}.
    <br /><br />
    Your credit card <b>{{ strtoupper($card['brand']) }} / {{ $card['last4'] }}</b> will be charged at that time.
    <br /><br />
    If your billing information has changed, or you would like to cancel your subscription, please use the link below:
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Manage subscription
        </a>
    </div>
@stop