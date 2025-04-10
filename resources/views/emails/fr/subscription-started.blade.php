@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $adminName }}</b>,
    <br /><br />
    @if ($trial === true)
        {{ $memberName }} vient de démarrer un essai gratuit ({{ $price }}) pour rejoindre votre communauté <strong>{{ $communityName }}</strong>.
    @else
        {{ $memberName }} vient de prendre un abonnement ({{ $price }}) à votre communauté <strong>{{ $communityName }}</strong>.
    @endif
    <br /><br />
    Bien joué!
    <br /><br />

    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="<?php echo strip_tags($communityUrl); ?>" target="_blank">
            Voir la communauté
        </a>
    </div>
@stop