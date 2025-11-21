@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<div class="logo-container">
    <img src="https://vziwtdyxiozjkkppxlcl.supabase.co/storage/v1/object/public/logo_bucket/Logo.png" class="logo" alt="VisusIA Logo">
</div>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
