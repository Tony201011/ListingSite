@php
    /** @var \App\Models\MetaKeyword $record */
    $metaKeywords = $record->meta_keyword;
    $metaKeywordsText = is_array($metaKeywords) ? implode(', ', $metaKeywords) : (string) $metaKeywords;
@endphp
<div class="mk-modal-content">
    <table class="mk-table">
        <tbody>
            <tr>
                <th scope="row">Page Name</th>
                <td>{{ $record->page_name }}</td>
            </tr>
            <tr>
                <th scope="row">Meta Keyword</th>
                <td>{{ $metaKeywordsText }}</td>
            </tr>
        </tbody>
    </table>
</div>

<style>
    .mk-modal-content {
        width: 100%;
    }

    .mk-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }

    .mk-table th,
    .mk-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
        font-size: 13px;
        color: #374151;
        text-align: left;
    }

    .mk-table tr:last-child th,
    .mk-table tr:last-child td {
        border-bottom: 0;
    }

    .mk-table th {
        width: 160px;
        background: #f9fafb;
        font-weight: 700;
        color: #6b7280;
    }

    .mk-table td {
        white-space: pre-wrap;
        word-break: break-word;
    }

    .dark .mk-table {
        border-color: #374151;
        background: #111827;
    }

    .dark .mk-table th {
        background: #1f2937;
        color: #9ca3af;
        border-bottom-color: #374151;
    }

    .dark .mk-table td {
        color: #d1d5db;
        border-bottom-color: #1f2937;
    }
</style>
