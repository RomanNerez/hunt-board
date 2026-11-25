@if ([] !== $resume->experiences)
    <section class="section">
        <h2 class="section__title">{{ $t('sections.experience') }}</h2>
        @foreach ($resume->experiences as $experience)
            <article class="card">
                <div class="card__head">
                    <div>
                        <div class="card__organization">{{ $experience->company }}</div>
                        <div class="card__role">{{ $experience->position }}</div>
                    </div>
                    <div class="card__aside">
                        @if ($experience->location || $experience->employmentType)
                            <span class="card__meta">{{ implode(' · ', array_filter([$experience->location, $experience->employmentType])) }}</span>
                            <span class="card__separator">|</span>
                        @endif
                        <span class="card__period">{{ $experience->period }}</span>
                    </div>
                </div>
                @if ($experience->descriptionHtml)
                    <div class="card__description">{!! $experience->descriptionHtml !!}</div>
                @endif
                @if ([] !== $experience->stack)
                    <div class="card__stack"><strong>{{ $t('tech_stack') }}:</strong> {{ implode(', ', $experience->stack) }}</div>
                @endif
            </article>
        @endforeach
    </section>
@endif
