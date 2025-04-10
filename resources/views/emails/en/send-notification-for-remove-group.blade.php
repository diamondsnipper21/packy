@extends('emails.en.layouts.default')

@section('content')
    Hello {{ $feedback }},
    <br /><br />
    You were removed from community <strong> {{ $communityName }} </strong> because you canceled your subscription.
    <br /><br />
    You need at least 1 active group subscription to be a member.
    <br /><br />

    <strong> Want to keep these benifits? </strong><br />
    * All the knowledge you need to start making money.<br />
    * Community of people just like you.<br />
    * Live workshops and coaching.<br />
    * All-in-one Software.<br />
    <br /><br />
@stop
