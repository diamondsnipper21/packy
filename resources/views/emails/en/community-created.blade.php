@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $userName }}</b>,
    <br /><br />
    Your community <b>{{ $communityName }}</b> has been successfully created.
    <br /><br />
    Please complete another 5 steps to publish your new community.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="<?php echo strip_tags($communityUrl); ?>" target="_blank">
            Publish community
        </a>
    </div>
@stop