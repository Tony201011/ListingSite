<div class="space-y-4">
    <p class="text-sm leading-6 text-gray-600">
        Current ad/featured visibility status for this provider profile.
    </p>

    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Expiry</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @foreach ($rows as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ e($row['tier']) }}</td>
                        <td class="px-4 py-3">
                            <span
                                @class([
                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium',
                                    $row['status_class'] ?? 'bg-gray-100 text-gray-700',
                                ])
                            >
                                {{ e($row['status']) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ e($row['expiry']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
