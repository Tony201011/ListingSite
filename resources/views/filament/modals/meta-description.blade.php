@php
    /** @var \App\Models\MetaDescription $record */
@endphp
<div>
    <strong>Page Name:</strong> {{ $record->page_name }}<br>
    <strong>Meta Description:</strong>
    <div>{{ $record->meta_description }}</div>
</div>
