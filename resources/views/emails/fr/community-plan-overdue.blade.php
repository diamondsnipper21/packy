@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $userName }}</b>,
    <br /><br />
    Le paiement de votre abonnement pour la communauté <b>{{ $communityName }}</b> a échoué.
    <br /><br />
    Nous vous invitons à vous connecter à votre communauté pour régulariser cette situation.
    <br /><br />
    Si vos informations de facturation ont changé ou si vous souhaitez annuler votre abonnement, veuillez utiliser le bouton ci-dessous:
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="{{ $communityUrl }}" target="_blank">
            Voir ma communauté
        </a>
    </div>
    <br /><br />
    Sans action de votre part, nous seront contraint d'interrompre l'accès à votre communauté : son contenu sera rendu inaccessible et vos membres ne pourront plus s'y connecter.
    <br /><br />
    Vous perdrez également l'accès aux ressources des membres Packie.
    <br /><br />
    Vous recevez cet e-mail car vous avez un abonnement actif avec Packie.
@stop