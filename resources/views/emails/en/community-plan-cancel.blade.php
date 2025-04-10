@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $userName }}</b>,
    <br /><br />
    Your subscription to the <b>{{ $communityName }}</b> community will not be automatically renewed. It will be canceled but will remain active until {{ $periodEnd }}
    <br /><br />
    If you change your mind, you can renew your subscription by clicking the button below :
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Renew my subscription
        </a>
    </div>
    <br /><br />
    You are receiving this email because you have an active subscription with Packie.
@stop