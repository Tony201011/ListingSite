@php
    /** @var \App\Models\MetaKeyword $record */
    $metaKeywords = $record->meta_keyword;
    $metaKeywordsText = is_array($metaKeywords) ? implode(', ', $metaKeywords) : (string) $metaKeywords;
@endphp
<div>
    <strong>Page Name:</strong> {{ $record->page_name }}<br>
    <strong>Meta Keyword:</strong>
    <div>{{ $metaKeywordsText }}</div>
</div>
