<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

@foreach($urls as $url)
    <url>
        <loc>{{ e($url['loc']) }}</loc>

        @if(!empty($url['lastmod']))
        <lastmod>{{ date('c', strtotime($url['lastmod'])) }}</lastmod>
        @endif

        @if(!empty($url['changefreq']))
        <changefreq>{{ $url['changefreq'] }}</changefreq>
        @endif

        @if(!empty($url['priority']))
        <priority>{{ number_format((float)$url['priority'], 1) }}</priority>
        @endif
    </url>
@endforeach

</urlset>