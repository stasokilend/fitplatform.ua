<?php
require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/../includes/functions.php';

Env::load();

$googleFitRedirectUri = (string) Env::get('GOOGLE_FIT_REDIRECT_URI', '');
if ($googleFitRedirectUri === '') {
    $googleFitRedirectUri = url('/google-fit-callback.php');
}

define('GOOGLE_FIT_CLIENT_ID', (string) Env::get('GOOGLE_FIT_CLIENT_ID', ''));
define('GOOGLE_FIT_CLIENT_SECRET', (string) Env::get('GOOGLE_FIT_CLIENT_SECRET', ''));
define('GOOGLE_FIT_REDIRECT_URI', $googleFitRedirectUri);

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

define('GOOGLE_FIT_API_URL', 'https://www.googleapis.com/fitness/v1/users/me');
define('GOOGLE_OAUTH_URL', 'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USERINFO_URL', 'https://www.googleapis.com/oauth2/v2/userinfo');
?>
