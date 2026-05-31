@props([
    'title',
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
])

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $title }}</title>
    @if($description)
        <meta name="description" content="{{ $description }}">
    @endif
    @if($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endif

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=geist:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Open Graph --}}
    <meta property="og:site_name" content="PrComet">
    <meta property="og:type" content="{{ $type }}">
    <meta property="og:title" content="{{ $title }}">
    @if($description)
        <meta property="og:description" content="{{ $description }}">
    @endif
    @if($canonical)
        <meta property="og:url" content="{{ $canonical }}">
    @endif
    @if($image)
        <meta property="og:image" content="{{ $image }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $title }}">
    @if($description)
        <meta name="twitter:description" content="{{ $description }}">
    @endif
    @if($image)
        <meta name="twitter:image" content="{{ $image }}">
    @endif

    {{-- Page-specific head: article meta, JSON-LD, etc. --}}
    {{ $slot }}

    <x-tracking />
</head>
