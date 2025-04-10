@extends('emails.fr.layouts.default')

@section('content')
    Bonjour <b>{{ $userName }}</b>,,
    <br /><br />
    Votre communauté <b>{{ $communityName }}</b> a été créée avec succès.
    <br /><br />
    Veuillez suivre les 5 autres étapes pour publier votre nouvelle communauté.
    <br />
    <div style="text-align: center; margin-top: 25px;">
        <a style="padding: 8px 15px; background-color: #9198ff; color: #ffffff; font-weight: 700; text-transform: uppercase; border-radius: 50px; text-decoration: none; font-size: 16px;" href="<?php echo strip_tags($communityUrl); ?>" target="_blank">
            Publier la communauté
        </a>
    </div>
@stop