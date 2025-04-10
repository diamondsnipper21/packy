@extends('emails.en.layouts.default')

@section('content')
    Hello,
    <br /><br />
    Your have been declined from joining to <strong>{{ $communityName }}</strong>.
    <br /><br />

    @if(!empty($feedback))
        <div>
            This is feedback from <strong>{{ $communityName }}</strong>
        </div>
        <div style="background-color: #eee; padding: 3px 7px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px; font-size: 16px;">
            {{ $feedback }}
        </div>
        <br />
    @endif
@stop