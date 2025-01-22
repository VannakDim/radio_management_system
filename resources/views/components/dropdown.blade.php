@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white', 'dropdownClasses' => '', 'content' => ''])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    'none', 'false' => '',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    '60' => 'w-60',
    default => 'w-48',
};
@endphp

<div class="dropdown show d-inline-block widget-dropdown" x-data="{ open: false }" @click.away="open = false" @close.stop="open = false">
    <a class="dropdown-toggle icon-burger-mini" href="" role="button" id="dropdown-recent-order1"
       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-display="static" @click="open = ! open"></a>
    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-recent-order1" x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }} {{ $dropdownClasses }}"
            style="display: none;"
            @click="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </ul>
</div>
