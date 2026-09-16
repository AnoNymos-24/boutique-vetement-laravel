@props(['url', 'logo' => null])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logo)
<img src="{{ $logo }}" class="logo" alt="{{ trim($slot) }}" style="max-height:48px; width:auto;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
