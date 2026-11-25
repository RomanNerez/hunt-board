<header class="header">
    <h1 class="header__name">{{ $resume->fullName }}</h1>
    @if ($resume->headline)
        <p class="header__headline">{{ $resume->headline }}</p>
    @endif
    @include('appSection@resume::pdf.sections.links')
</header>
