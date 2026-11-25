@if ([] !== $resume->links)
    <p class="header__links">
        @foreach ($resume->links as $link)
            <a class="header__link" href="{{ $link->url }}">
                @if ($link->iconSvg)
                    {!! $link->iconSvg !!}
                @endif
                <span>{{ $link->label }}</span>
            </a>
        @endforeach
    </p>
@endif
