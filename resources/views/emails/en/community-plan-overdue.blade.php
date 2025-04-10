@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $userName }}</b>,
    <br /><br />
    The payment for your subscription to the <b>{{ $communityName }}</b> community has failed.
    <br /><br />
    We invite you to log in to your community to resolve this issue.
    <br /><br />
    If your billing information has changed or if you wish to cancel your subscription, please use the button below:
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            View My Community
        </a>
    </div>
    <br /><br />
    Without action from you, we will be forced to suspend access to your community: its content will become inaccessible, and your members will no longer be able to connect.
    <br /><br />
    You will also lose access to Packie member resources.
    <br /><br />
    You are receiving this email because you have an active subscription with Packie.
@stop