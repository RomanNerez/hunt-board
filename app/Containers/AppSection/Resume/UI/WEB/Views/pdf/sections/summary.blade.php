@if ($resume->summary)
    <section class="section">
        <h2 class="section__title">{{ $t('sections.summary') }}</h2>
        <div class="summary">{{ $resume->summary }}</div>
    </section>
@endif
