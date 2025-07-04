@use(Illuminate\Support\Number)

<div
    {{
        $attributes
            ->merge($getExtraAttributes(), escape: false)
            ->class(["flex flex-wrap gap-4 justify-start w-full"])
    }}
>
    @foreach($getState() as $storeProduct)
    <div class="flex text-center gap-2">
        <img src="/images/{{ $storeProduct->store->slug }}.svg" class="h-5" />

        @if ($showPrice)
        <small>{{ Number::currency($storeProduct->original_price, in: "EUR", locale: "NL") }}</small>
        @endif
    </div>
    @endforeach
</div>
