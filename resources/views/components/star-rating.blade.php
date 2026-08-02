@props(['rating'])

<span class="star-rating" aria-label="{{ $rating }} sur 5">
    @for ($i = 1; $i <= 5; $i++)
        <svg class="star-rating__star {{ $i <= $rating ? 'is-filled' : '' }}" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2.5l3 6.2 6.8.9-4.9 4.7 1.2 6.7-6.1-3.3-6.1 3.3 1.2-6.7-4.9-4.7 6.8-.9z" />
        </svg>
    @endfor
</span>
