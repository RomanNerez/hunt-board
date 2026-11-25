@if ([] !== $resume->experiences)
    <section class="section">
        <h2 class="section__title">{{ $t('sections.experience') }}</h2>
        @foreach ($resume->experiences as $experience)
            <article class="card">
                <div class="card__head">
                    <div class="card__title">
                        <span class="card__role">{{ $experience->position }}</span>
                        <span class="card__separator">|</span>
                        <span class="card__organization">{{ $experience->company }}</span>
                    </div>
                    <div class="card__aside">
                        @if ($experience->location || $experience->employmentType)
                            <span class="card__meta">{{ implode(' · ', array_filter([$experience->location, $experience->employmentType])) }}</span>
                            <span class="card__separator">|</span>
                        @endif
                        <span class="card__period">{{ $experience->period }}</span>
                    </div>
                </div>
                @foreach ($experience->projects as $project)
                    <div class="card__project">
                        <div class="card__project-name">{{ $project->name }}</div>
                        @if ($project->role)
                            <div class="card__project-role"><strong>{{ $t('role') }}:</strong> {{ $project->role }}</div>
                        @endif
                        @if ($project->descriptionHtml)
                            <div class="card__description">{!! $project->descriptionHtml !!}</div>
                        @endif
                        @if ([] !== $project->stack)
                            <div class="card__stack"><strong>{{ $t('tech_stack') }}:</strong> {{ implode(', ', $project->stack) }}</div>
                        @endif
                    </div>
                @endforeach
            </article>
        @endforeach
    </section>
@endif
