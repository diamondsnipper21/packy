@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $userName }}</b>,
    <br /><br />
    A request to reset your Packie password has been made.
    <br /><br />
    If you did not make this request, please ignore this email. If you did make this request, please reset your password by clicking this button.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $link }}" target="_blank">
            Reset My Password
        </a>
    </div>
@stop