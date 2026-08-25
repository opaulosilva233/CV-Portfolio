@php
    $siteName = rescue(fn() => \App\Models\SiteSetting::getValue('name'), 'Paulo Silva') ?: 'Paulo Silva';
    $jobTitle = rescue(fn() => \App\Models\SiteSetting::getValue('job_title'), 'Full Stack Developer') ?: 'Full Stack Developer';
    $customSeoTitle = rescue(fn() => \App\Models\SiteSetting::getValue('seo_title'), null);
    $seoTitle = $customSeoTitle ?: "{$siteName} | {$jobTitle}";
    
    $customDescription = rescue(fn() => \App\Models\SiteSetting::getValue('seo_description') ?: \App\Models\SiteSetting::getValue('bio'), null);
    $seoDescription = $customDescription ?: 'Professional portfolio with projects, skills and experience.';

    $customKeywords = rescue(fn() => \App\Models\SiteSetting::getValue('seo_keywords'), null);

    $heroImage = rescue(fn() => \App\Models\SiteSetting::getValue('hero_image'), null);
    $ogImage = $heroImage ? (str_starts_with($heroImage, 'http') ? $heroImage : url($heroImage)) : asset('images/Logotipo.png');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $seoDescription }}">
    @if(!empty($customKeywords))
    <meta name="keywords" content="{{ $customKeywords }}">
    @endif
    <meta name="robots" content="index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1">

    <!-- Open Graph / Facebook / LinkedIn / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <title inertia>{{ $seoTitle }}</title>

    <!-- Favicon -->
    <link rel="icon" href="/images/Logotipo.png" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme');
            const isDark = savedTheme === 'dark' || (!savedTheme && true); // Default to dark
            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>