@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $userName }}</b>,
    <br /><br />
    Votre essai gratuit pour la communauté <b>{{ $communityName }}</b> se termine dans 3 jours le {{ $periodEnd }}.
    <br /><br />
    A partir de cette date, votre carte bancaire <b>{{ strtoupper($cardBrand) }}</b> se terminant par <b>{{ $last4 }}</b> sera utilisée pour procéder au paiement de la prémière échéance d'abonnement d'un montant de {{ number_format($amount, 2, ',', '') }} {{ $currency }}.
    <br /><br />
    Si vous ne souhaitez pas être facturé, assurez-vous d'annuler votre essai à partir du menu des paramètres de votre communauté.
    <br /><br />
    Merci pour votre confiance.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Gérer mon abonnement
        </a>
    </div>
@stop