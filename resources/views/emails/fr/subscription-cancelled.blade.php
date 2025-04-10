@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $memberName }}</b>,
    <br /><br />
    Nous vous informons que votre abonnement à la communauté <b>{{ $communityName }}</b> a été annulée en raison de plusieurs échecs de prélèvement sur votre carte bancaire (<b>{{ strtoupper($card->card_brand) }} / {{ $card->last4 }}</b>).
    <br /><br />
    Malgré nos tentatives répétées, nous n’avons pas pu traiter le paiement, ce qui a conduit à la résiliation automatique de votre abonnement.
    <br /><br />
    Si vous souhaitez réactiver votre abonnement, vous pouvez mettre à jour vos informations de paiement et souscrire à nouveau via votre <a href="{{ $communityUrl }}">espace client</a>.
    <br /><br />
    Nous espérons vous revoir bientôt parmi nos abonnés.
@stop