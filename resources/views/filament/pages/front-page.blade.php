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

        <x-filament::input.wrapper>
            <x-slot name="prefix">
                <x-filament::icon-button
                    icon="heroicon-m-magnifying-glass"
                    label="Zoek product"
                />
            </x-slot>

            <x-filament::input
                class="!w-96 py-3"
                type="search"
                wire:model="query"
            ></x-filament::input>

            <x-slot name="suffix">
                <x-filament::icon-button
                    icon="heroicon-m-qr-code"
                    wire:click="$dispatch('open-modal', { id: 'barcode-scanner-modal' })"
                    label="Scan product"
                />
            </x-slot>
        </x-filament::input.wrapper>

        <x-filament::modal
            id="barcode-scanner-modal"
            width="md"
        >
            <x-slot name="heading">
                Scan een product
            </x-slot>
            <x-slot name="description">
                Scan de streepjescode van een product.
            </x-slot>

            <div class="overflow-hidden rounded-xl">
                <div class="relative flex flex-col justify-center">
                    <span class="absolute w-full text-center">Bezig met laden...</span>
                    <canvas
                        id="canvas"
                        class="z-10"
                    ></canvas>
                </div>
            </div>

            <x-slot name="footer">
                <x-filament::input.wrapper>
                    <x-filament::input
                        id="result"
                        wire:model="query"
                    />

                    <x-slot name="suffix">
                        <x-filament::icon-button
                            icon="heroicon-m-magnifying-glass"
                            wire:click="search"
                        ></x-filament::icon-button>
                    </x-slot>
                </x-filament::input.wrapper>
            </x-slot>

            @vite('resources/js/scanner.js')
        </x-filament::modal>
    </div>
</x-filament-panels::page>
