@extends('emails.fr.layouts.default')

@section('content')
    Bonjour,
    <br /><br />
    Votre adhésion à la communauté <strong> {{ $communityName }} </strong> a été refusée.
    <br /><br />

    @if(!empty($feedback))
        <div>
            Ceci est un retour de la communauté <strong>{{ $communityName }}</strong>
        </div>
        <div style="background-color: #eee; padding: 3px 7px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px; font-size: 16px;">
            {{ $feedback }}
        </div>
        <br />
    @endif
@stop
