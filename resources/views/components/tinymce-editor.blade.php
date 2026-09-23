@props(['name', 'value' => '', 'id' => null, 'height' => 450])
@php($id = $id ?? 'tinymce-'.uniqid())

<div class="tinymce-wrapper relative">
    <textarea id="{{ $id }}" name="{{ $name }}" class="tinymce-editor w-full rounded-lg border-slate-300 text-sm">{!! $value !!}</textarea>
</div>

@once('tinymce-editor')
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@7.2.0/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            (() => {
                function initAllTinyMceEditors() {
                    if (! window.tinymce) {
                        return;
                    }

                    window.tinymce.init({
                        selector: 'textarea.tinymce-editor',
                        license_key: 'gpl',
                        branding: false,
                        promotion: false,
                        height: 450,
                        min_height: 350,
                        max_height: 800,
                        autoresize_bottom_margin: 20,
                        plugins: 'preview importcss searchreplace autolink autosave directionality code visualblocks visualchars fullscreen image link media codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help quickbars emoticons',
                        menubar: 'file edit view insert format tools table help',
                        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table codesample | code visualblocks fullscreen | removeformat',
                        block_formats: 'Paragraph=p; Heading 1=h1; Heading 2=h2; Heading 3=h3; Heading 4=h4; Heading 5=h5; Heading 6=h6; Blockquote=blockquote; Code Block=pre; Preformatted=pre',
                        font_size_formats: '11px 12px 14px 15px 16px 18px 20px 24px 28px 32px 36px 48px',
                        link_title: true,
                        link_assume_external_targets: 'http',
                        link_default_target: '_self',
                        link_context_toolbar: true,
                        image_advtab: true,
                        image_caption: true,
                        image_title: true,
                        convert_urls: false,
                        relative_urls: false,
                        remove_script_host: false,
                        entity_encoding: 'raw',
                        verify_html: false,
                        extended_valid_elements: '*[*]',
                        valid_children: '+body[style|script|link],+div[style|script|link|iframe]',
                        content_style: `
                            body {
                                font-family: Figtree, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                                font-size: 15px;
                                color: #334155;
                                line-height: 1.7;
                                padding: 16px 20px;
                            }
                            h1 { font-size: 2.25rem; font-weight: 800; color: #0f172a; margin: 1.5rem 0 1rem; line-height: 1.25; letter-spacing: -0.025em; }
                            h2 { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 1.35rem 0 0.75rem; line-height: 1.3; letter-spacing: -0.02em; }
                            h3 { font-size: 1.35rem; font-weight: 600; color: #1e293b; margin: 1.2rem 0 0.5rem; line-height: 1.4; }
                            h4 { font-size: 1.15rem; font-weight: 600; color: #1e293b; margin: 1rem 0 0.5rem; line-height: 1.4; }
                            h5 { font-size: 1rem; font-weight: 600; color: #334155; margin: 0.75rem 0 0.25rem; }
                            h6 { font-size: 0.875rem; font-weight: 600; color: #475569; text-transform: uppercase; margin: 0.75rem 0 0.25rem; }
                            p { margin: 0.75rem 0; }
                            strong, b { font-weight: 700; color: #0f172a; }
                            em, i { font-style: italic; }
                            u { text-decoration: underline; text-underline-offset: 2px; }
                            s, strike, del { text-decoration: line-through; color: #64748b; }
                            a { color: #ff9200; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; }
                            ul { list-style-type: disc; padding-left: 1.6rem; margin: 0.75rem 0; }
                            ol { list-style-type: decimal; padding-left: 1.6rem; margin: 0.75rem 0; }
                            li { margin: 0.25rem 0; }
                            blockquote { border-left: 4px solid #ff9200; background: #fffaf0; padding: 0.75rem 1rem; margin: 1rem 0; border-radius: 0 6px 6px 0; color: #475569; font-style: italic; }
                            table { width: 100%; border-collapse: collapse; margin: 1rem 0; font-size: 14px; }
                            th, td { border: 1px solid #cbd5e1; padding: 8px 12px; }
                            th { background-color: #f8fafc; font-weight: 600; color: #0f172a; }
                            code { background-color: #f1f5f9; color: #0f172a; padding: 2px 5px; border-radius: 4px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.9em; }
                            pre { background-color: #0f172a; color: #f8fafc; padding: 12px 16px; border-radius: 6px; overflow-x: auto; margin: 1rem 0; }
                            img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
                            hr { border: none; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
                        `,
                        setup: function(editor) {
                            editor.on('change keyup NodeChange SetContent paste blur', function() {
                                editor.save();
                            });
                        }
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initAllTinyMceEditors);
                } else {
                    initAllTinyMceEditors();
                }

                document.addEventListener('livewire:navigated', () => {
                    setTimeout(initAllTinyMceEditors, 100);
                });

                // Auto-save editors on any form submission
                document.addEventListener('submit', () => {
                    if (window.tinymce) {
                        window.tinymce.triggerSave();
                    }
                }, true);

                // Handle details/accordion toggling (e.g. multi-locale page tabs)
                document.querySelectorAll('details').forEach(details => {
                    details.addEventListener('toggle', () => {
                        if (details.open && window.tinymce) {
                            setTimeout(initAllTinyMceEditors, 50);
                        }
                    });
                });
            })();
        </script>
    @endpush
@endonce
