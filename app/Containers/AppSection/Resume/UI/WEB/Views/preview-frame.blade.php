<iframe
    wire:key="resume-pdf-preview-{{ md5($previewUrl) }}"
    src="{{ $previewUrl }}"
    title="Резюме"
    style="display: block; width: 100%; height: {{ $height }}; min-height: 36rem; border: 0; border-radius: 0.75rem; background: #f8fafc;"
></iframe>
