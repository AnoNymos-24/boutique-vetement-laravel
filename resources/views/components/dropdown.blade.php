@props(['align' => 'right'])

<div class="dropdown" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition.duration.150ms.origin.top
            class="dropdown-panel"
            style="{{ $align === 'left' ? 'right:auto; left:0;' : '' }} display: none;"
            @click="open = false">
        {{ $content }}
    </div>
</div>
