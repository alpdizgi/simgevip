@if ($paginator->hasPages())
@php
$lastPage = $paginator->lastPage();
$currentPage = $paginator->currentPage();
$leadingPages = range(1, min(5, $lastPage));
$trailingPages = $lastPage > 5 ? range(max(6, $lastPage - 1), $lastPage) : [];
$displayPages = array_values(array_unique(array_merge($leadingPages, $trailingPages)));
$pageUrls = $paginator->getUrlRange(1, $lastPage);
@endphp

<nav class="store-pagination" aria-label="Sayfalama">
    @if ($paginator->onFirstPage())
    <span aria-disabled="true">‹</span>
    @else
    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">‹</a>
    @endif

    @php($previousPage = 0)
    @foreach ($displayPages as $page)
    @if ($page > $previousPage + 1)
    <span class="store-pagination__ellipsis" aria-hidden="true">…</span>
    @endif

    @if ($page == $currentPage)
    <span aria-current="page" style="background:#121212;color:#fff;border-color:#121212">{{ $page }}</span>
    @else
    <a href="{{ $pageUrls[$page] }}">{{ $page }}</a>
    @endif

    @php($previousPage = $page)
    @endforeach

    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" rel="next">›</a>
    @else
    <span aria-disabled="true">›</span>
    @endif
</nav>
@endif