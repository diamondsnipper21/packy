@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $userName }}</b>,
    <br /><br />
    Pour valider votre action, veuillez renseigner le code de vérification ci-dessous :
    <br /><br />
    <div style="text-align: center; font-size: 34px; font-weight: 700; display: flex; justify-content: center; letter-spacing: 4px; background-color: #f5f8fb; padding: 20px 20px 18px 20px; border-radius: 5px;">
        <div>{{ $twoFactorCode }}</div>
    </div><br/>
    Si vous n'êtes pas à l'origine de cette demande, nous vous recommandons de changer votre mot de passe afin d'assurer la sécurité de votre compte.
    <br /><br />
    Votre code de vérification expirera dans 10 minutes.
@stop