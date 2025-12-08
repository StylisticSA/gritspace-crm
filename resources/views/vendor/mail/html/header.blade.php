@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
                <img src="{{ asset('files_grits/gritspace.svg') }}" class="logo" alt="Laravel Logo">
            @else
                {!! $slot !!}
            @endif
        </a>
    </td>
</tr>
