<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

{{-- Sitemap generated {{ now()->toIso8601String() }} --}}
<urlset
    xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
    xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
>

@foreach($urls as $url)
    <url>
        <loc>{{ e($url['loc']) }}</loc>

        @if(!empty($url['lastmod']))
            <lastmod>{{ \Illuminate\Support\Carbon::parse($url['lastmod'])->toAtomString() }}</lastmod>
        @endif

        @if(!empty($url['changefreq']))
            <changefreq>{{ $url['changefreq'] }}</changefreq>
        @endif

        @if(!empty($url['priority']))
            <priority>{{ number_format((float) $url['priority'], 1) }}</priority>
        @endif

        {{-- ── IMAGES (anime/manga posters & banners) ── --}}
        @if(!empty($url['images']))
            @foreach($url['images'] as $image)
                <image:image>
                    <image:loc>{{ e($image['loc']) }}</image:loc>

                    @if(!empty($image['title']))
                        <image:title>{{ e($image['title']) }}</image:title>
                    @endif

                    @if(!empty($image['caption']))
                        <image:caption>{{ e($image['caption']) }}</image:caption>
                    @endif
                </image:image>
            @endforeach
        @endif

        {{-- ── VIDEOS (anime episodes) ── --}}
        @if(!empty($url['video']))
            <video:video>
                <video:thumbnail_loc>{{ e($url['video']['thumbnail']) }}</video:thumbnail_loc>
                <video:title>{{ e($url['video']['title']) }}</video:title>
                <video:description>{{ e($url['video']['description']) }}</video:description>

                @if(!empty($url['video']['content_loc']))
                    <video:content_loc>{{ e($url['video']['content_loc']) }}</video:content_loc>
                @endif

                @if(!empty($url['video']['player_loc']))
                    <video:player_loc>{{ e($url['video']['player_loc']) }}</video:player_loc>
                @endif

                @if(!empty($url['video']['duration']))
                    <video:duration>{{ (int) $url['video']['duration'] }}</video:duration>
                @endif

                @if(!empty($url['video']['publication_date']))
                    <video:publication_date>{{ \Illuminate\Support\Carbon::parse($url['video']['publication_date'])->toAtomString() }}</video:publication_date>
                @endif

                @if(!empty($url['video']['family_friendly']))
                    <video:family_friendly>{{ $url['video']['family_friendly'] }}</video:family_friendly>
                @endif
            </video:video>
        @endif

        {{-- ── ALTERNATE LANGUAGES (i18n future) ── --}}
        @if(!empty($url['alternates']))
            @foreach($url['alternates'] as $lang => $altUrl)
                <xhtml:link rel="alternate" hreflang="{{ $lang }}" href="{{ e($altUrl) }}" />
            @endforeach
        @endif
    </url>
@endforeach

</urlset>