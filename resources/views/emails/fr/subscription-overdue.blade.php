@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $memberName }}</b>,
    <br /><br />
    Nous vous informons que le paiement de votre abonnement à la communauté <b>{{ $communityName }}</b> d'un montant de {{ number_format($subscription['amount'], 2, ',', '') }} {{ $subscription['currency'] }} n’a pas pu être effectué lors de sa dernière tentative.
    <br /><br />
    Il semblerait que la carte bancaire enregistrée (<b>{{ strtoupper($card['brand']) }} / {{ $card['last4'] }}</b>) ait été refusée par votre banque ou présente un problème technique.
    <br /><br />
    Afin d’éviter toute interruption de service, nous vous invitons à:<br />
    <ul style="list-style-type: none">
        <li>1. Vérifier les informations de votre moyen de paiement dans votre <a href="{{ $communityUrl }}">espace client</a>.</li>
        <li>2. Mettre à jour vos coordonnées bancaires si nécessaire.</li>
        <li>3. Contacter votre établissement bancaire si le problème persiste.</li>
    </ul>
    Si vous avez déjà régularisé votre situation, veuillez ignorer cet e-mail.
    <br /><br />
    Merci de votre attention et de votre fidélité.
@stop