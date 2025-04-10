@extends('emails.en.layouts.default')

@section('content')
    Hello,
    <br /><br />
    Your community <b>{{ $communityName }}</b> is now activated.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="<?php echo strip_tags($communityUrl); ?>" target="_blank">
            View community
        </a>
    </div>
@stop