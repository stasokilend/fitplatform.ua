<?php
// Настройки Google Fit API
define('GOOGLE_FIT_CLIENT_ID', '1097198779936-5lsqnde1ge5dvohie2eavj6irl461pbd.apps.googleusercontent.com');
define('GOOGLE_FIT_CLIENT_SECRET', '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');
define('GOOGLE_FIT_REDIRECT_URI', 'http://fitplatform.ua/google-fit-callback.php');

// Scopes для доступа к данным
define('GOOGLE_FIT_SCOPES', implode(' ', [
    'https://www.googleapis.com/auth/fitness.activity.read',
    'https://www.googleapis.com/auth/fitness.activity.write',
    'https://www.googleapis.com/auth/fitness.body.read',
    'https://www.googleapis.com/auth/fitness.heart_rate.read',
    'https://www.googleapis.com/auth/fitness.blood_pressure.read',
    'https://www.googleapis.com/auth/fitness.location.read',
    'https://www.googleapis.com/auth/userinfo.email',
    'https://www.googleapis.com/auth/userinfo.profile'
]));

// URL API Google Fit
define('GOOGLE_FIT_API_URL', 'https://www.googleapis.com/fitness/v1/users/me');
define('GOOGLE_OAUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');
?>