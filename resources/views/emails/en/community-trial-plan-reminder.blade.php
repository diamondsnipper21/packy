@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $userName }}</b>,
    <br /><br />
    Your <b>{{ $communityName }}</b> free trial ends in 3 days on {{ $periodEnd }}.
    <br /><br />
    Your credit card <b>{{ strtoupper($cardBrand) }}</b> ending in <b>{{ $last4 }}</b> will be charged {{ number_format($amount, 2, ',', '') }} {{ $currency }}.
    <br /><br />
    If you don't want to be charged, make sure you cancel your trial from your community settings menu.
    <br /><br />
    Thanks for being a customer.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Manage my subscription
        </a>
    </div>
@stop