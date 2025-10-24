<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'Dashboard :: PasumaiKudil' }}</title>

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />

<script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.3.0/alpine.js"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
<meta name="csrf-token" content="{{ csrf_token() }}">
