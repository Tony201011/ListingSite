<div class="space-y-2">
    <p class="text-sm text-gray-600">Current ad/featured visibility status for this provider profile.</p>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="p-2 text-left">Type</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Expiry</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td class="p-2">{{ e($row['tier']) }}</td>
                        <td class="p-2">{{ e($row['status']) }}</td>
                        <td class="p-2">{{ e($row['expiry']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
