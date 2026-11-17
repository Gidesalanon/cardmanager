<?php

return [
    // Seul ce compte garde l'accès pendant le verrou (php artisan admin:verrouiller)
    'email_exempte' => env('ADMIN_LOCK_EMAIL_EXEMPTE', 'dev@cardmanager.test'),
];
