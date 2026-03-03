<?php
/**
 * Redirige l'utilisateur vers la page de connexion Google (OAuth).
 */
require_once __DIR__ . '/../config/init_session.php';
require_once __DIR__ . '/../config/google_oauth.php';

$redirectUri = get_google_redirect_uri();
$params = [
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => $redirectUri,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'    => 'online',
];
$url = GOOGLE_AUTH_URI . '?' . http_build_query($params);
header('Location: ' . $url);
exit;
