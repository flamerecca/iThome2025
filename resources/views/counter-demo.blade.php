<x-layouts.app>
    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-semibold mb-6">Livewire Counter Demo</h1>

        <div class="flex items-center gap-6">
            @livewire('counter', ['initial' => 0])
        </div>

        <p class="mt-6 text-sm text-zinc-600 dark:text-zinc-400">
            Try clicking the +1 button. This page is for manual front-end interaction testing of the Volt counter component.
        </p>
    </div>
</x-layouts.app>
