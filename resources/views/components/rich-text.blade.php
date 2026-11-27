{{-- Editor-written HTML, sanitized before output. Urdu content runs right-to-left. --}}
@props(['html', 'language' => null])
@php($urdu = $language?->isRtl())
<div {{ $attributes->class(['rich-text']) }} lang="{{ $urdu ? 'ur' : 'en' }}" @if ($urdu) dir="rtl" @endif>
    {!! str((string) $html)->sanitizeHtml() !!}
</div>
