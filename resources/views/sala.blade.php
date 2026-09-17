<!doctype html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} · Sala</title>
    <script>
        window.RADIO = {
            reverb: {
                key: @json(config('reverb.apps.apps.0.key')),
                host: @json(env('REVERB_HOST', 'localhost')),
                port: @json((int) env('REVERB_PORT', 8102)),
                scheme: @json(env('REVERB_SCHEME', 'http')),
            },
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body class="antialiased">
    <div id="app"></div>
</body>
</html>
