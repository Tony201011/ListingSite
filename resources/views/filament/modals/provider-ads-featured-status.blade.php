<div class="space-y-4">
    <div>
        <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
            Current ad & featured visibility status for this provider profile.
        </p>
    </div>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300"
                        >
                            Type
                        </th>

                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300"
                        >
                            Status
                        </th>

                        <th
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300"
                        >
                            Expiry
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($rows as $row)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-800/60">
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $row['tier'] }}
                            </td>

                            <td class="px-4 py-3">
                                <span
                                    @class([
                                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
                                        $row['status_class'] ?? 'bg-gray-100 text-gray-700 ring-gray-200',
                                    ])
                                >
                                    {{ $row['status'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $row['expiry'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="3"
                                class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400"
                            >
                                No ad or featured records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
