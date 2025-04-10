<!DOCTYPE html>
<html style="background-color: #f4f5f8; overflow: auto;">
    <head>
        @include('includes.header')
        <link href="{{ mix('css/app.css') }}" type="text/css" rel="stylesheet">
        <link href="{{ asset('css/common.css') }}" type="text/css" rel="stylesheet">
    </head>
    <body>
        <div id="app-super-admin"
             auth="{{ $auth ?? false }}">
            @yield('content')
        </div>
    </body>
</html>
<script src="{{ mix('/js/app.js') }}"></script>