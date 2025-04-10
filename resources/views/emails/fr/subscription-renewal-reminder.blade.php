@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $memberName }}</b>,
    <br /><br />
    Nous vous rappelons que votre abonnement pour la communauté <b>{{ $communityName }}</b> d'un montant de {{ number_format($subscription['amount'], 2, ',', '') }} {{ $subscription['currency'] }} va automatiquement être renouvelé le {{ $nextBillingDate }}.
    <br /><br />
    Votre carte bancaire <b>{{ strtoupper($card['brand']) }} / {{ $card['last4'] }}</b> sera utilisée pour ce paiement.
    <br /><br />
    Si vos informations de facturation ont changé ou si vous souhaitez annuler votre abonnement, veuillez utiliser le bouton ci-dessous:
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Gérer mon abonnement
        </a>
    </div>
@stop