@php
    $t = static fn (string $key): string => __("appSection@resume::pdf.{$key}", locale: $resume->locale->value);
@endphp
<!DOCTYPE html>
<html lang="{{ $resume->locale->value }}">
<head>
    <meta charset="utf-8">
    <title>{{ $resume->fullName }}</title>
    @include('appSection@resume::pdf.sections.styles')
</head>
<body>
@include('appSection@resume::pdf.sections.header')

<main class="content">
    @include('appSection@resume::pdf.sections.summary')
    @include('appSection@resume::pdf.sections.experience')
    @include('appSection@resume::pdf.sections.education')
    @include('appSection@resume::pdf.sections.languages')
</main>
</body>
</html>
