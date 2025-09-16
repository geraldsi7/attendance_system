@props(['url'])
<tr>
<td class="header">
<h1>
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
{{ $slot }}
@endif
</h1>
</td>
</tr>
