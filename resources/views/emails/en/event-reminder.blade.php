@extends('emails.en.layouts.default')

@section('content')
    <p style="font-size: 20px;"><span>Event Title: </span> <strong><span>{{ $eventTitle }}</span></strong></p><br />
    <p style="font-size: 18px;"><span>Event Start At: </span> <strong><span>{{ $eventStartAt }}</span></strong></p>
    <p style="font-size: 18px;"><span>Event Timezone: </span> <strong><span>{{ $eventTimezone }}</span></strong></p>
    <p style="font-size: 18px;"><span>Event Description: </span></p>
    <p style="font-size: 18px;"><span>{{ $eventDescription }}</span></p>

    @if($eventLink)
        <div style="text-align: left; margin-top: 15px;">
            You can join here: <a style="font-size: 17px;" href="{{ $eventLink }}" target="_blank">{{ $eventLink }}</a>
        </div>
    @endif
@stop