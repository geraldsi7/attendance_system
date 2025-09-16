<x-mail::message>
{{ $session->lecturer->name }} is inviting you to a scheduled attendance session. 
<br />
<br />
Title: {{ $session->title }}
<br />
Time: {{ $starts_at }} - {{ $ends_at }}
<br />
Venue: {{ $session->venue->title }}
</x-mail::message>
