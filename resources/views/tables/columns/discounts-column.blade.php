@if ($getState())
    <div class="flex flex-wrap gap-2">
    @foreach($getState() as $tiers)
        @foreach($tiers as $tier)
        <span class="bg-primary-600 text-gray-50 py-1 px-3 rounded-full">
        {{ $tier->description }}
        </span>
        @endforeach
    @endforeach
    </div>
@endif
