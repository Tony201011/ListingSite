@php
    /** @var \App\Models\MetaKeyword $record */
@endphp
<div>
    <strong>Page Name:</strong> {{ $record->page_name }}<br>
    <strong>Meta Keyword:</strong>
    <div>{{ $record->meta_keyword }}</div>
</div>
