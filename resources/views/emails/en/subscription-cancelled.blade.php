@extends('emails.en.layouts.default')

@section('content')
    Hello <b>{{ $memberName }}</b>,
    <br /><br />
    We regret to inform you that your subscription to community <b>{{ $communityName }}</b> has been canceled due to multiple failed payment attempts on your registered payment method (<b>{{ strtoupper($card->card_brand) }} / {{ $card->last4 }}</b>).
    <br /><br />
    Despite our repeated attempts, we were unable to process the payment, leading to the automatic termination of your subscription.
    <br /><br />
    If you wish to reactivate your subscription, you can update your payment details and subscribe again through <a href="{{ $communityUrl }}">your account</a>.
    <br /><br />
    We hope to see you again soon as one of our valued subscribers.
@stop