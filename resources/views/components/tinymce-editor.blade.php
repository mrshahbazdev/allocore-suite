@props(['name', 'value' => '', 'id' => null])
@php($id = $id ?? 'tinymce-'.uniqid())

<textarea id="{{ $id }}" name="{{ $name }}" class="tinymce-editor w-full rounded-lg border-slate-300 text-sm">{{ $value }}</textarea>

@once('tinymce-editor')
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@7.2.0/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (! window.tinymce) {
                    return;
                }

                window.tinymce.init({
                    selector: 'textarea.tinymce-editor',
                    plugins: 'link lists code',
                    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link code',
                    menubar: false,
                    height: 320,
                    branding: false,
                    promotion: false,
                    license_key: 'gpl',
                    content_style: 'body { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; color: #334155; line-height: 1.6; }',
                });
            });
        </script>
    @endpush
@endonce
