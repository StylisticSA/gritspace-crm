@props(['url'])
<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            <img src="{{ asset('files_grits/gritspace_logo.png') }}" class="logo" alt="grit space Logo">
            {{-- @if (trim($slot) === 'Laravel')
            @else
                {!! $slot !!}
            @endif --}}
        </a>
    </td>
</tr>
