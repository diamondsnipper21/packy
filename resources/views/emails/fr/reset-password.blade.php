@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $userName }}</b>,
    <br /><br />
    Une demande de réinitialisation de votre mot de passe a été demandée.
    <br /><br />
    Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email. Dans le cas contraire, veuillez réinitialiser votre mot de passe en cliquant sur le bouton ci-dessous.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $link }}" target="_blank">
            Réinitialiser mon mot de passe
        </a>
    </div>
@stop