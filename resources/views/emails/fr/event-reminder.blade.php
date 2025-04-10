@extends('emails.fr.layouts.default')

@section('content')
    <p style="font-size: 20px;"><span>Titre de l'événement: </span> <strong><span>{{ $eventTitle }}</span></strong></p><br />
    <p style="font-size: 18px;"><span>Début de l'événement à: </span> <strong><span>{{ $eventStartAt }}</span></strong></p>
    <p style="font-size: 18px;"><span>Fuseau horaire de l'événement: </span> <strong><span>{{ $eventTimezone }}</span></strong></p>
    <p style="font-size: 18px;"><span>Description de l'évenement: </span></p>
    <p style="font-size: 18px;"><span>{{ $eventDescription }}</span></p>

    @if($eventLink)
        <div style="text-align: left; margin-top: 15px;">
            Vous pouvez y assister ici: <a style="font-size: 17px;" href="{{ $eventLink }}" target="_blank">{{ $eventLink }}</a>
        </div>
    @endif
@stop