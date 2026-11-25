@if ([] !== $resume->languages)
    <section class="section">
        <h2 class="section__title">{{ $t('sections.languages') }}</h2>
        <ul class="languages">
            @foreach ($resume->languages as $language)
                <li>{{ $language->line }}</li>
            @endforeach
        </ul>
    </section>
@endif
