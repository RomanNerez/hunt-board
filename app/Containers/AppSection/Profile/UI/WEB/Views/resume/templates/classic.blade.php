@php
    $t = static fn (string $key): string => __("appSection@profile::resume.{$key}", locale: $resume->locale->value);
    $contacts = array_filter([
        $t('contacts.email') => $resume->email,
        $t('contacts.phone') => $resume->phone,
        $t('contacts.location') => $resume->location,
    ], static fn (?string $value): bool => null !== $value);
@endphp
<!DOCTYPE html>
<html lang="{{ $resume->locale->value }}">
<head>
    <meta charset="utf-8">
    <title>{{ $resume->fullName }}</title>
    <style>
        @page {
            size: A4;
            margin: 12mm 0;
            background: #f8fafc;
        }

        @page :first {
            margin-top: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #f8fafc;
            color: #1e293b;
            font-family: "Liberation Sans", Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .header {
            background: #0f172a;
            color: #e2e8f0;
            padding: 8mm 6mm 6mm;
        }

        .header__name {
            margin: 0;
            color: #ffffff;
            font-size: 23pt;
            font-weight: 700;
            line-height: 1.2;
        }

        .header__headline {
            margin: 2mm 0 0;
            color: #38bdf8;
            font-size: 12.5pt;
        }

        .header__line {
            margin: 3mm 0 0;
            font-size: 9.5pt;
        }

        .header__line + .header__line {
            margin-top: 0.5mm;
        }

        .header__item {
            display: inline-block;
            margin-right: 6mm;
        }

        .header__label {
            color: #cbd5e1;
            font-weight: 700;
        }

        .header__link {
            color: #38bdf8;
        }

        .content {
            padding: 4mm 12mm 0;
        }

        .summary {
            margin: 0 0 6mm;
            padding: 3mm 4mm;
            background: #ffffff;
            border-left: 1mm solid #0284c7;
            white-space: pre-line;
        }

        .section {
            margin-bottom: 6mm;
        }

        .section__title {
            margin: 0 0 3mm;
            padding-bottom: 1mm;
            border-bottom: 0.6mm solid #0284c7;
            color: #0f172a;
            font-size: 12.5pt;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            break-after: avoid;
        }

        .skills {
            width: 100%;
            border-collapse: collapse;
        }

        .skills th,
        .skills td {
            padding: 0 0 2mm;
            vertical-align: top;
            text-align: left;
        }

        .skills th {
            width: 40mm;
            padding-right: 3mm;
            color: #334155;
            font-weight: 700;
        }

        .card {
            margin-bottom: 3mm;
            padding: 3mm 4mm;
            background: #ffffff;
            border: 0.3mm solid #e2e8f0;
            border-radius: 1mm;
            break-inside: avoid;
        }

        .card__head {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 4mm;
        }

        .card__role {
            color: #0369a1;
            font-size: 11.5pt;
            font-weight: 700;
        }

        .card__organization {
            color: #334155;
            font-size: 11.5pt;
            font-weight: 700;
        }

        .card__period {
            flex-shrink: 0;
            color: #64748b;
            font-size: 9.5pt;
        }

        .card__title {
            margin-top: 0.5mm;
            color: #475569;
            font-style: italic;
        }

        .card__description {
            margin-top: 1mm;
        }

        .card__description p {
            margin: 0 0 1mm;
        }

        .card__description ul,
        .card__description ol {
            margin: 0 0 1mm;
            padding-left: 5mm;
        }

        .card__description li {
            margin-bottom: 0.8mm;
        }

        .card__description li p {
            margin: 0;
        }

        .card__stack {
            margin-top: 1mm;
            color: #475569;
            font-size: 9pt;
        }

        .card__stack strong {
            color: #334155;
        }

        .education {
            margin-bottom: 2.5mm;
            break-inside: avoid;
        }

        .education__heading {
            color: #0f172a;
            font-weight: 700;
        }

        .education__meta,
        .education__description {
            color: #475569;
            font-size: 10pt;
        }
    </style>
</head>
<body>
<header class="header">
    <h1 class="header__name">{{ $resume->fullName }}</h1>
    @if ($resume->headline)
        <p class="header__headline">{{ $resume->headline }}</p>
    @endif
    @if ([] !== $contacts)
        <p class="header__line">
            @foreach ($contacts as $label => $value)
                <span class="header__item">
                    <span class="header__label">{{ $label }}:</span>
                    @if ($label === $t('contacts.email'))
                        <a class="header__link" href="mailto:{{ $value }}">{{ $value }}</a>
                    @else
                        {{ $value }}
                    @endif
                </span>
            @endforeach
        </p>
    @endif
    @if ([] !== $resume->links)
        <p class="header__line">
            @foreach ($resume->links as $link)
                <span class="header__item">
                    @if ('' !== $link->label)
                        <span class="header__label">{{ $link->label }}:</span>
                    @endif
                    <a class="header__link" href="{{ $link->url }}">{{ $link->displayUrl }}</a>
                </span>
            @endforeach
        </p>
    @endif
</header>

<main class="content">
    @if ($resume->summary)
        <div class="summary">{{ $resume->summary }}</div>
    @endif

    @if ($resume->hasSkillsSection())
        <section class="section">
            <h2 class="section__title">{{ $t('sections.skills') }}</h2>
            <table class="skills">
                @foreach ($resume->skillRows as $row)
                    <tr>
                        <th>{{ $row->name }}:</th>
                        <td>{{ implode(', ', $row->items) }}</td>
                    </tr>
                @endforeach
                @if ([] !== $resume->languages)
                    <tr>
                        <th>{{ $t('languages') }}:</th>
                        <td>{{ implode('; ', $resume->languages) }}</td>
                    </tr>
                @endif
            </table>
        </section>
    @endif

    @if ([] !== $resume->experience)
        <section class="section">
            <h2 class="section__title">{{ $t('sections.experience') }}</h2>
            @foreach ($resume->experience as $card)
                <article class="card">
                    <div class="card__head">
                        <div>
                            <span class="card__role">{{ $card->role }}</span>
                            <span class="card__organization">| {{ $card->organization }}</span>
                        </div>
                        <div class="card__period">{{ $card->period }}</div>
                    </div>
                    @if ($card->title)
                        <div class="card__title">{{ $card->title }}</div>
                    @endif
                    @if ($card->descriptionHtml)
                        <div class="card__description">{!! $card->descriptionHtml !!}</div>
                    @endif
                    @if ([] !== $card->stack)
                        <div class="card__stack"><strong>{{ $t('tech_stack') }}:</strong> {{ implode(', ', $card->stack) }}</div>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    @if ([] !== $resume->education)
        <section class="section">
            <h2 class="section__title">{{ $t('sections.education') }}</h2>
            @foreach ($resume->education as $education)
                <div class="education">
                    <div class="education__heading">{{ $education->heading }}</div>
                    @if ($education->subtitle || $education->years)
                        <div class="education__meta">{{ implode(' ', array_filter([$education->subtitle, $education->years ? "({$education->years})" : null])) }}</div>
                    @endif
                    @if ($education->description)
                        <div class="education__description">{{ $education->description }}</div>
                    @endif
                </div>
            @endforeach
        </section>
    @endif
</main>
</body>
</html>
