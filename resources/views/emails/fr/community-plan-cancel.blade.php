@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $userName }}</b>,
    <br /><br />
    Votre abonnement à la communauté <b>{{ $communityName }}</b> ne sera pas renouvel&eacute; automatiquement. Il sera annulé, mais restera actif jusqu'au {{ $periodEnd }}.
    <br /><br />
    Si vous changez d'avis, vous pouvez renouveler votre abonnement en cliquant sur le bouton ci-dessous :
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Renouveler mon abonnement
        </a>
    </div>
    <br /><br />
    Vous recevez cet e-mail car vous avez un abonnement actif avec Packie.
@stop