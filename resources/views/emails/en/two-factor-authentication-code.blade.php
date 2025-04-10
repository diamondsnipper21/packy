@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $memberName }}</b>,
    <br /><br />
    In order to validate your action, we need to confirm your identity. Please fill the form the verification code below:
    <br /><br />
    <div style="text-align: center; font-size: 34px; font-weight: 700; display: flex; justify-content: center; letter-spacing: 4px; background-color: #f5f8fb; padding: 20px 20px 18px 20px; border-radius: 5px;">
        <div>{{ $twoFactorCode }}</div>
    </div><br />
    If you did not initiate this request, we recommend that you change your password to ensure the security of your account.
    <br /><br />
    Your verification code will expire in 10 minutes.
@stop