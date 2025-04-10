@extends('emails.en.layouts.default')

@section('content')
    Hello,
    <br /><br />
    You have been approved into community <strong>{{ $communityName }}</strong>.
    <br /><br />

    <div style="background-color: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd; margin-top: 5px;">
        <div style="font-size: 18px; line-height: 1.5em; margin-top: 15px; font-weight: 600;">
            {{ $communityName }}
        </div>

        @if(!empty($communitySummaryPhoto))
            <div style="font-size: 16px; line-height: 1.5em; margin-top: 15px;">
                <img style="border-radius: 5px; width: 100%; object-fit: contain;" src="{{ $communitySummaryPhoto }}">
            </div>
        @endif

        @if(!empty($communitySummaryDescription))
            <div style="font-size: 16px; margin-top: 15px;">
                {{ $communitySummaryDescription }}
            </div>
        @endif
    </div>

    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            View community
        </a>
    </div>
@stop