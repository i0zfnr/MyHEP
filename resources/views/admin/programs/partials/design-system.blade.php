@php
    $canUseAccent = $canUseAccent ?? (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp
