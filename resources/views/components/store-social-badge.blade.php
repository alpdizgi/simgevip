@props(['platform', 'url', 'size' => 26])

@php
    $platform = strtolower($platform);
    $iconSize = $size * 0.5; // İkon boyutu rozetin yarısı kadar olsun
    
    // Marka Renkleri
    $bgStyle = match($platform) {
        'instagram' => 'background: radial-gradient(circle at 30% 107%, #fdf497 0%, #fdf497 5%, #fd5949 45%, #d6249f 60%, #285AEB 90%);',
        'facebook' => 'background-color: #1877F2;',
        'twitter', 'x' => 'background-color: #000000;',
        'pinterest' => 'background-color: #E60023;',
        'youtube' => 'background-color: #FF0000;',
        'whatsapp' => 'background-color: #25D366;',
        'tiktok' => 'background-color: #000000;',
        'linkedin' => 'background-color: #0A66C2;',
        'telegram' => 'background-color: #2481cc;',
        default => 'background-color: #333333;'
    };

    $linkStyle = "display: inline-flex; align-items: center; justify-content: center; width: {$size}px; height: {$size}px; border-radius: 50%; color: #ffffff; text-decoration: none; transition: transform 0.2s ease, opacity 0.2s ease; {$bgStyle}";
    $spanStyle = "font-size: " . ($size * 0.4) . "px; font-weight: bold;";
    
    $linkStyleAttr = 'style="' . $linkStyle . '"';
    $spanStyleAttr = 'style="' . $spanStyle . '"';
@endphp

<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" title="{{ ucfirst($platform) }}" 
   {!! $linkStyleAttr !!}
   onmouseover="this.style.transform='translateY(-2px)'; this.style.opacity='0.9';"
   onmouseout="this.style.transform='translateY(0)'; this.style.opacity='1';">
    
    @if($platform === 'instagram')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
    @elseif($platform === 'facebook')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
    @elseif($platform === 'twitter' || $platform === 'x')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"></path></svg>
    @elseif($platform === 'pinterest')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.276 1.15-.325 1.346-.468 1.439-1.204.565-1.956-2.339-1.956-3.766 0-3.066 2.229-5.881 6.425-5.881 3.376 0 6.002 2.404 6.002 5.611 0 3.355-2.113 6.059-5.048 6.059-1.02 0-1.98-.53-2.311-1.159l-.634 2.413c-.229.873-.852 1.961-1.269 2.627 1.124.316 2.315.485 3.535.485 6.627 0 12-5.373 12-12 0-6.62-5.373-11.987-12-11.987z"/></svg>
    @elseif($platform === 'youtube')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
    @elseif($platform === 'whatsapp')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.95 1.17-.17.2-.35.22-.65.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.48-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.87 1.22 3.07c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z"/><path d="M12.04 2C6.55 2 2.1 6.45 2.1 11.94c0 1.76.46 3.48 1.34 5L2 22l5.2-1.36a9.9 9.9 0 004.84 1.23h.01c5.49 0 9.94-4.45 9.94-9.94S17.53 2 12.04 2zm0 18.15h-.01a8.2 8.2 0 01-4.18-1.15l-.3-.18-3.09.81.82-3.01-.2-.31a8.2 8.2 0 01-1.26-4.37c0-4.53 3.69-8.21 8.22-8.21 2.2 0 4.26.86 5.81 2.41a8.16 8.16 0 012.41 5.8c0 4.53-3.69 8.21-8.22 8.21z"/></svg>
    @elseif($platform === 'tiktok')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.12-3.44-3.13-3.65-5.46-.22-2.39.81-4.78 2.65-6.23 1.35-1.07 3.1-1.62 4.82-1.47v4.06c-1.04-.15-2.12.07-2.96.69-.87.6-1.38 1.62-1.33 2.69.04 1.16.7 2.25 1.74 2.78 1.05.53 2.33.47 3.32-.12.83-.49 1.41-1.36 1.48-2.31.13-1.66.08-3.32.08-4.98V.02z"/></svg>
    @elseif($platform === 'linkedin')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M22.225 0H1.77C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.77 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0zM7.069 20.452H3.554V9.034h3.515v11.418zM5.312 7.542c-1.127 0-2.041-.912-2.041-2.04 0-1.127.913-2.04 2.041-2.04s2.04.913 2.04 2.04c0 1.128-.913 2.04-2.04 2.04zm15.14 12.91h-3.515v-5.559c0-1.326-.026-3.033-1.847-3.033-1.85 0-2.133 1.445-2.133 2.937v5.655h-3.515V9.034h3.375v1.556h.048c.47-.887 1.618-1.82 3.33-1.82 3.564 0 4.22 2.345 4.22 5.396v6.286z"/></svg>
    @elseif($platform === 'telegram')
        <svg width="{{ $iconSize }}" height="{{ $iconSize }}" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 24c6.627 0 12-5.373 12-12S18.627 0 12 0 0 5.373 0 12s5.373 12 12 12z" fill="#2481cc"/><path d="M17.47 7.03l-1.5 10.65c-.11.81-.66 1.04-1.34.66l-3.7-2.73-1.78 1.72c-.2.2-.37.37-.76.37l.26-3.78 6.89-6.22c.3-.27-.06-.42-.46-.15l-8.52 5.36-3.66-1.14c-.8-.25-.81-.8.17-1.18l14.3-5.5c.66-.25 1.24.15 1.1.94z" fill="#fff"/></svg>
    @else
        <span {!! $spanStyleAttr !!}>{{ substr(ucfirst($platform), 0, 2) }}</span>
    @endif
</a>
