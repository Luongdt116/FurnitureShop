@if ($paginator->hasPages())
    <div class="aura-pagination-container">
        {{-- Counter Info --}}
        <div class="aura-pagination-counter">
            Hiển thị <strong>{{ $paginator->firstItem() }}</strong> – <strong>{{ $paginator->lastItem() }}</strong> trong <strong class="text-accent">{{ $paginator->total() }}</strong> sản phẩm
        </div>

        {{-- Navigation Controls --}}
        <nav class="aura-pagination-nav" aria-label="Phân trang sản phẩm">
            <ul class="aura-pagination-list">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="aura-page-item disabled" aria-disabled="true">
                        <span class="aura-page-btn" title="Trang trước">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    </li>
                @else
                    <li class="aura-page-item">
                        <a class="aura-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" title="Trang trước">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="aura-page-item disabled" aria-disabled="true">
                            <span class="aura-page-btn dots">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="aura-page-item active" aria-current="page">
                                    <span class="aura-page-btn">{{ $page }}</span>
                                </li>
                            @else
                                <li class="aura-page-item">
                                    <a class="aura-page-btn" href="{{ $url }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="aura-page-item">
                        <a class="aura-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" title="Trang tiếp">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                @else
                    <li class="aura-page-item disabled" aria-disabled="true">
                        <span class="aura-page-btn" title="Trang tiếp">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif
