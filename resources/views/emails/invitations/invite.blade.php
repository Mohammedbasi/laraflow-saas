<x-mail::message>
    You have been invited to join **{{ $tenantName }}**.

    <x-mail::button :url="$inviteUrl">
        Accept Invitation
    </x-mail::button>

    This link expires in 7 days.

    Thanks,<br>
    {{ $tenantName }}
</x-mail::message>
