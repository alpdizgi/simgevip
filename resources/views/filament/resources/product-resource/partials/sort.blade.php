@php
    $sortFields = array_filter($columns, fn ($column) => $column->isSortable());
    $selectedSort = $getLivewire()->tableSortColumn . ':' . $getLivewire()->tableSortDirection;
@endphp
<label class="sv-products-sort">
    <span>Sırala</span>
    <select
        aria-label="Ürün sıralaması"
        x-on:change="$wire.sortTable($event.target.value.split(':')[0] || null, $event.target.value.split(':')[1] || null)"
    >
        <option value="" {{ ! $getLivewire()->tableSortColumn ? 'selected' : '' }}>Varsayılan</option>
        @foreach ($sortFields as $column)
            @foreach (['asc' => 'Artan', 'desc' => 'Azalan'] as $direction => $label)
                <option value="{{ $column->getName() }}:{{ $direction }}" {{ $selectedSort === $column->getName() . ':' . $direction ? 'selected' : '' }}>
                    {{ $column->getLabel() }} · {{ $label }}
                </option>
            @endforeach
        @endforeach
    </select>
</label>
