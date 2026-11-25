<style>
    @page {
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
        text-align: center;
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

    .header__links {
        margin: 3mm 0 0;
        font-size: 9.5pt;
    }

    .header__link {
        display: inline-block;
        margin: 0 3mm;
        color: #38bdf8;
        white-space: nowrap;
    }

    .header__icon {
        display: inline-block;
        width: 3.6mm;
        height: 3.6mm;
        margin-right: 1mm;
        vertical-align: -0.6mm;
    }

    .content {
        padding: 4mm 12mm 0;
    }

    .summary {
        padding: 3mm 4mm;
        background: #ffffff;
        border: 0.3mm solid #e2e8f0;
        border-radius: 1mm;
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
        text-align: center;
        text-transform: uppercase;
        break-after: avoid;
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

    .card__organization {
        color: #0f172a;
        font-size: 11.5pt;
        font-weight: 700;
    }

    .card__role {
        margin-top: 0.5mm;
        color: #0369a1;
        font-size: 10.5pt;
        font-weight: 700;
    }

    .card__aside {
        flex-shrink: 0;
        text-align: right;
    }

    .card__period {
        color: #64748b;
        font-size: 9.5pt;
    }

    .card__meta {
        color: #64748b;
        font-size: 9.5pt;
        font-style: italic;
    }

    .card__separator {
        margin: 0 1mm;
        color: #cbd5e1;
        font-size: 9.5pt;
    }

    .card__description {
        margin-top: 1mm;
    }

    .card__description p,
    .education__description p {
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

    .languages {
        margin: 0;
        padding-left: 5mm;
    }

    .languages li {
        margin-bottom: 0.8mm;
    }
</style>
