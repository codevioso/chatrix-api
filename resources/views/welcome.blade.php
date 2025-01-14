<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ env('APP_NAME') }}</title>
    </head>
    <body>
    <h1 style="text-align: center;margin-top: 100px;">
        <img src="{{ asset('assets/img/logo.png') }}" alt="">
    </h1>
    </body>
</html>
