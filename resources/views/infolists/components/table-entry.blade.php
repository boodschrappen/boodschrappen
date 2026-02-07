<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <x-filament::grid class="gap-4">
        <table class="filament-table-entry w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5 shadow-sm rounded-xl bg-white">
            <thead>
                <tr>
                    @foreach($getState()['headings'] as $heading)
                    <th class="it-table-entry-header-cell font-semibold text-gray-950 dark:text-white text-start px-1 py-3.5 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                        {{ $heading }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                @foreach ($getState()['rows'] as $row)
                <tr>
                    @foreach($row as $column)
                    <td class="it-table-entry-cell first-of-type:font-bold px-1 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 py-2">
                        {{ $column }}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </x-filament::grid>
</x-dynamic-component>
