@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $userName }}</b>,
    <br /><br />
    Votre communauté <b>{{ $communityName }}</b> a été fermée car votre abonnement a été annulé.
    <br /><br />
    Si vous souhaitez réactiver votre communauté, veuillez souscrire à un nouvel abonnement à partir du menu des paramètres de votre communauté.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Voir la communauté
        </a>
    </div>
@stop