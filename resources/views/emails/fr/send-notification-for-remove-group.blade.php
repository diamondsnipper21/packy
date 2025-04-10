@extends('emails.fr.layouts.default')

@section('content')
    Bonjour {{ $feedback }},
    <br /><br />
    Vous avez été supprimé de la commuanuté <strong>{{ $communityName }}</strong> car vous avez annulé votre abonnement.
    <br /><br />
    Vous devez avoir au moins un abonnement actif au groupe pour être membre.
    <br /><br />

    <strong> Vous souhaitez conserver ces avantages? </strong><br />
    * Toutes les connaissances dont vous avez besoin pour commencer à gagner de l'argent.<br />
    * Communauté de personnes comme vous.<br />
    * Ateliers et coaching en direct.<br />
    * Logiciel tout-en-un.<br />
    <br /><br />
@stop