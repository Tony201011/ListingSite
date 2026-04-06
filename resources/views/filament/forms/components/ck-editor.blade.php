@php
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        wire:ignore
        x-data="{
            state: $wire.$entangle('{{ $statePath }}'),
            editor: null,
        }"
        x-init="
            ClassicEditor.create($refs.ckEditor, {
                toolbar: {
                    items: ['heading', '|', 'bold', 'italic', 'underline', '|', 'bulletedList', 'numberedList', '|', 'link', 'blockQuote', '|', 'undo', 'redo']
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    ]
                },
            }).then(editor => {
                this.editor = editor;
                editor.setData(this.state ?? '');
                this.$watch('state', (value) => {
                    if (editor.getData() !== value) {
                        editor.setData(value ?? '');
                    }
                });
                editor.model.document.on('change:data', () => {
                    this.state = editor.getData();
                });
            });
        "
    >
        <div x-ref="ckEditor"></div>
    </div>
</x-dynamic-component>
