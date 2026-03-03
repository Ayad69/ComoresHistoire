<?php
/**
 * Callback Google OAuth : reçoit le code, récupère le token et les infos utilisateur,
 * crée ou connecte l'utilisateur puis redirige vers l'accueil.
 */
require_once __DIR__ . '/../config/init_session.php';
require_once __DIR__ . '/../config/google_oauth.php';

$code = $_GET['code'] ?? null;
$error = $_GET['error'] ?? null;

if ($error !== null) {
    header('Location: login.php?error=google&message=' . urlencode($error));
    exit;
}

if ($code === null || $code === '') {
    header('Location: login.php?error=google&message=no_code');
    exit;
}

$redirectUri = get_google_redirect_uri();
$tokenPayload = [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => $redirectUri,
    'grant_type'    => 'authorization_code',
];

$ch = curl_init(GOOGLE_TOKEN_URI);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($tokenPayload),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || $response === false) {
    header('Location: login.php?error=google&message=token_failed');
    exit;
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? null;
if (!$accessToken) {
    header('Location: login.php?error=google&message=no_token');
    exit;
}

$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
]);
$userInfoJson = curl_exec($ch);
curl_close($ch);

if ($userInfoJson === false) {
    header('Location: login.php?error=google&message=userinfo_failed');
    exit;
}

$userInfo = json_decode($userInfoJson, true);
$email = $userInfo['email'] ?? null;
$givenName = $userInfo['given_name'] ?? '';
$familyName = $userInfo['family_name'] ?? '';

if (!$email) {
    header('Location: login.php?error=google&message=no_email');
    exit;
}

require_once __DIR__ . '/../models/DAO/Database.php';
require_once __DIR__ . '/../models/DTO/User.php';
require_once __DIR__ . '/../models/DAO/UserDAO.php';

$userDao = new \Comores\Models\DAO\UserDAO();
$user = $userDao->readByEmail($email);

if ($user === null) {
    $user = new \Comores\Models\DTO\User();
    $user->setNomUser($familyName !== '' ? $familyName : 'Utilisateur');
    $user->setPrenomUser($givenName !== '' ? $givenName : 'Google');
    $user->setEmailUser($email);
    $user->setDateNaissUser(null);
    $user->setPassword(password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT));
    $user->setEcrivain(false);
    $userDao->create($user);
    $user = $userDao->readByEmail($email);
}

if ($user !== null) {
    $_SESSION['idUser'] = $user->getIdUser();
    $_SESSION['nomUser'] = $user->getNomUser();
    $_SESSION['prenomUser'] = $user->getPrenomUser();
    $_SESSION['emailUser'] = $user->getEmailUser();
    $_SESSION['ecrivain'] = $user->isEcrivain();
}

header('Location: ../index.php');
exit;
