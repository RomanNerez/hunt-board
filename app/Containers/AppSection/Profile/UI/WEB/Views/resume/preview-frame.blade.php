@if ($previewUrl)
    <iframe
        wire:key="resume-preview-{{ $locale }}"
        src="{{ $previewUrl }}"
        title="Резюме"
        style="display: block; width: 100%; height: calc(100vh - 13rem); min-height: 36rem; border: 0; border-radius: 0.75rem; background: #f8fafc;"
    ></iframe>
@else
    <x-filament::section>
        <p>
            Щоб сформувати резюме, заповніть сторінку
            <x-filament::link :href="$generalUrl">«Загальне»</x-filament::link>.
        </p>
    </x-filament::section>
@endif
