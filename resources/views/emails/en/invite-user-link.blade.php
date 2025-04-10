@extends('emails.en.layouts.default')

@section('content')
    <div style="display: flex; background-color: #fff; padding: 10px;">
        <img style="border-radius: 50%; width: 80px; height: 80px; object-fit: contain;" src="{{ $referUserPhoto }}">
        <div style="font-size: 16px; margin-left: 15px; line-height: 1.5em; margin-top: 15px;">
            <strong style="font-size: 17px;">{{ $referUserName }}</strong> invited you to join the <strong style="font-size: 17px;">{{ $communityName }}</strong> community on Packie. Join now to start collaborating!
        </div>
    </div>

    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="<?php echo strip_tags($inviteLink); ?>" target="_blank">
            Join now!
        </a>
    </div>
@stop