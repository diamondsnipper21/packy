@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $userName }}</b>,
    <br /><br />
    We remind you that your subscription for the <b>{{ $communityName }}</b> community, amounting to {{ number_format($plan['amount'], 2, ',', '') }} {{ $plan['currency'] }}, will be automatically renewed on {{ $nextBillingDate }}.
    <br /><br />
    Your credit card <b>{{ strtoupper($card['brand']) }} / {{ $card['last4'] }}</b> will be used for this payment.
    <br /><br />
    If your billing information has changed or if you wish to cancel your subscription, please use the button below :
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Manage subscription
        </a>
    </div>
    <br /><br />
    You are receiving this email because you have an active subscription with Packie.
@stop