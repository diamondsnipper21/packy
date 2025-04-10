@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $userName }}</b>,
    <br /><br />
    Your community <b>{{ $communityName }}</b> has been closed because your subscription plan has been cancelled.
    <br /><br />
    If you want to reactivate your community, please make new plan from your community settings menu.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            View community
        </a>
    </div>
@stop