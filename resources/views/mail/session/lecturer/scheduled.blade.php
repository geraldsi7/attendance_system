<x-mail::message>
Your attendance session "{{ $session->title }}" has been scheduled. 
<br />
<br />
Title: {{ $session->title }}
<br />
Attendees: {{ $classeTitle }}
<br />
Time: {{ $starts_at }} - {{ $ends_at }}
<br />
Venue: {{ $session->venue->title }}
</x-mail::message>
