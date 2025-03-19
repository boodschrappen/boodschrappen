<x-filament-panels::page>
    <div class="flex flex-col mt-[10vh] justify-start items-center">
        <x-filament::avatar
            size="8rem"
            src="/images/logo.svg"
            alt="Logo"
            class="mx-auto"
        />

        <div class="my-6 text-center">
            <h1 class="text-3xl font-bold">Boodschrappen.nl</h1>
            <p class="text-gray-600">Schrap de kosten, niet de boodschappen.</p>
        </div>

        {{ $this->form }}
    </div>
</x-filament-panels::page>
