<div class="overflow-x-auto rounded-lg shadow">
    <table class="w-full divide-y divide-gray-700 bg-gray-800 text-md text-left text-white">
        <thead class="bg-gray-900 text-xs uppercase tracking-wider text-gray-400">
        <tr>
            {{ $thead }}
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-700">
        {{ $slot }}
        </tbody>
    </table>
</div>
