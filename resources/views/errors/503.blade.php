@extends('layouts.app')

@section('title', 'Bakım Aşamasında | ' . ($settings->site_name ?? 'SimgeVIP'))

@section('content')
<div class="min-h-[60vh] flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
    <div class="max-w-xl w-full text-center space-y-8">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 mb-4 shadow-sm">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
        </div>
        
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">
            Çok Yakında Buradayız!
        </h1>
        
        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-lg mx-auto leading-relaxed">
            {{ $message ?? 'Sitemiz şu anda bakım aşamasında. Size daha iyi hizmet verebilmek için çalışıyoruz. Lütfen daha sonra tekrar ziyaret edin.' }}
        </p>

        @if(isset($settings) && is_array($settings->social_media) && count($settings->social_media) > 0)
            <div class="pt-8 border-t border-gray-200 dark:border-gray-800">
                <p class="text-sm text-gray-500 mb-4">Bizi sosyal medyada takip edin:</p>
                <div class="flex justify-center gap-4">
                    @foreach($settings->social_media as $social)
                        <a href="{{ $social['url'] }}" target="_blank" class="text-gray-400 hover:text-primary-600 transition-colors">
                            <span class="capitalize font-medium">{{ $social['platform'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
