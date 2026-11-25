@if ([] !== $resume->educations)
    <section class="section">
        <h2 class="section__title">{{ $t('sections.education') }}</h2>
        @foreach ($resume->educations as $education)
            <div class="education">
                <div class="education__heading">{{ $education->institution }}</div>
                @if ($education->qualification || $education->years)
                    <div class="education__meta">{{ implode(' ', array_filter([$education->qualification, $education->years ? "({$education->years})" : null])) }}</div>
                @endif
                @if ($education->descriptionHtml)
                    <div class="education__description">{!! $education->descriptionHtml !!}</div>
                @endif
            </div>
        @endforeach
    </section>
@endif
