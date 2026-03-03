<?php
require_once __DIR__ . '/../config/init_session.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailUser = trim($_POST['emailUser'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($emailUser, FILTER_VALIDATE_EMAIL) || $password === '') {
        $errors[] = 'Email et mot de passe requis.';
    } else {
        try {
            require_once __DIR__ . '/../models/DAO/Database.php';
            require_once __DIR__ . '/../models/DTO/User.php';
            require_once __DIR__ . '/../models/DAO/UserDAO.php';
            $userDao = new \Comores\Models\DAO\UserDAO();
            $user = $userDao->readByEmail($emailUser);
            if ($user === null || !password_verify($password, $user->getPassword())) {
                $errors[] = 'Email ou mot de passe incorrect.';
            } else {
                $_SESSION['idUser'] = $user->getIdUser();
                $_SESSION['nomUser'] = $user->getNomUser();
                $_SESSION['prenomUser'] = $user->getPrenomUser();
                $_SESSION['emailUser'] = $user->getEmailUser();
                $_SESSION['ecrivain'] = $user->isEcrivain();
                header('Location: ../index.php');
                exit;
            }
        } catch (Throwable $e) {
            $errors[] = 'Erreur de connexion. Réessayez.';
        }
    }
}

$registered = isset($_GET['registered']);
$googleError = isset($_GET['error']) && $_GET['error'] === 'google';
$googleErrorMessage = $_GET['message'] ?? '';
$googleAuthAvailable = is_readable(__DIR__ . '/../config/google_client_secret.json');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Histoire des Comores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --teal:     #1a6b5a;
            --teal-dk:  #124d41;
            --teal-lt:  #e8f4f1;
            --gold:     #c9a84c;
            --text:     #1c2b27;
            --muted:    #6b8078;
            --border:   #cdddd9;
            --bg:       #f5f9f8;
            --white:    #ffffff;
            --radius:   10px;
        }

        body {
            font-family: 'Source Sans 3', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── HEADER ── */
        .site-header {
            background: var(--teal-dk);
            padding: 1rem 2rem;
        }
        .site-header a {
            color: #fff;
            text-decoration: none;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: .03em;
        }

        /* ── MAIN ── */
        .page-auth {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            position: relative;
            overflow: hidden;
        }

        .page-auth::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                    radial-gradient(ellipse 60% 50% at 10% 20%, rgba(26,107,90,.08) 0%, transparent 70%),
                    radial-gradient(ellipse 50% 60% at 90% 80%, rgba(201,168,76,.07) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 4px 40px rgba(26,107,90,.10), 0 1px 4px rgba(0,0,0,.06);
            padding: 2.8rem 3rem;
            width: 100%;
            max-width: 460px;
            position: relative;
            z-index: 1;
        }

        .auth-card::before {
            content: '';
            position: absolute;
            top: 0; left: 2.5rem; right: 2.5rem;
            height: 3px;
            background: linear-gradient(90deg, var(--gold), rgba(201,168,76,.3));
            border-radius: 0 0 4px 4px;
        }

        .auth-card h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 600;
            color: var(--teal-dk);
            margin-bottom: .4rem;
        }

        .auth-intro {
            color: var(--muted);
            font-size: .95rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        /* ── FORM ── */
        .form-auth { display: flex; flex-direction: column; gap: 1.1rem; }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: .4rem;
        }

        label {
            font-size: .85rem;
            font-weight: 600;
            color: var(--teal-dk);
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        input[type="email"],
        input[type="password"] {
            padding: .75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Source Sans 3', sans-serif;
            font-size: .95rem;
            color: var(--text);
            background: var(--bg);
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }

        input:focus {
            border-color: var(--teal);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26,107,90,.12);
        }

        input::placeholder { color: #a8bbb7; }

        /* Submit */
        .btn-submit {
            margin-top: .4rem;
            padding: .85rem;
            background: var(--teal);
            color: #fff;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: .04em;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            transition: background .2s, transform .15s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(26,107,90,.25);
        }

        .btn-submit:hover {
            background: var(--teal-dk);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,107,90,.32);
        }

        .btn-submit:active { transform: translateY(0); }

        /* Footer link */
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: .9rem;
            color: var(--muted);
        }

        .auth-footer a {
            color: var(--teal);
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover { text-decoration: underline; }

        .auth-errors {
            list-style: none;
            margin: 0 0 1rem;
            padding: 1rem;
            background: #fde8e8;
            border: 1px solid #e8b4b4;
            border-radius: var(--radius);
            color: #a94442;
            font-size: .9rem;
        }
        .auth-errors ul { margin: 0; padding: 0; list-style: none; }
        .auth-errors li { margin: .25rem 0; }

        .auth-success {
            margin-bottom: 1.25rem;
            padding: 1.1rem 1.25rem;
            background: #d4edda;
            border: 1px solid #a3d9a5;
            border-radius: var(--radius);
            color: #155724;
            font-size: .95rem;
        }
        .auth-success strong {
            display: block;
            margin-bottom: .35rem;
            font-size: 1rem;
        }
        .auth-success p {
            margin: 0;
            line-height: 1.45;
        }

        .auth-divider {
            text-align: center;
            margin: 1.25rem 0;
            color: var(--muted);
            font-size: 0.9rem;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: '';
            display: inline-block;
            width: 60px;
            height: 1px;
            background: var(--border);
            vertical-align: middle;
            margin: 0 0.75rem;
        }
        .btn-google {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0.5rem;
            background: #fff;
            color: #3c4043;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-family: 'Source Sans 3', sans-serif;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: background .2s, border-color .2s, box-shadow .2s;
        }
        .btn-google:hover {
            background: #f8f9fa;
            border-color: #dadce0;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
        }
        .btn-google svg {
            width: 20px;
            height: 20px;
        }

        @media (max-width: 768px) {
            .page-auth { padding: 2rem 1rem; }
            .auth-card { padding: 2rem 1.5rem; max-width: 100%; }
            .auth-card h1 { font-size: 1.6rem; }
        }
        @media (max-width: 480px) {
            .site-header { padding: 0.75rem 1rem; }
            .auth-card { padding: 1.5rem 1rem; }
            input[type="email"], input[type="password"] {
                min-height: 48px;
                font-size: 16px;
            }
            .btn-submit { min-height: 48px; padding: 1rem; }
        }
    </style>
</head>
<body>

<header class="site-header">
    <a href="../index.php">← Histoire des Comores</a>
</header>

<main class="page-auth">
    <div class="auth-card">
        <h1>Se connecter</h1>
        <p class="auth-intro">Accédez à votre compte.</p>

        <?php if (!empty($errors)): ?>
            <div class="auth-errors" role="alert">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($registered): ?>
            <div class="auth-success" role="alert">
                <strong>Inscription réussie !</strong>
                <p>Vous pouvez maintenant vous connecter avec votre email et votre mot de passe.</p>
            </div>
        <?php endif; ?>

        <?php if ($googleError): ?>
            <div class="auth-errors" role="alert">
                <strong>Connexion Google impossible.</strong>
                <?php if ($googleErrorMessage === 'access_denied'): ?>
                    <p>Vous avez refusé l'accès. Réessayez ou connectez-vous avec email et mot de passe.</p>
                <?php else: ?>
                    <p>Une erreur s'est produite. Réessayez ou connectez-vous avec email et mot de passe.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($googleAuthAvailable): ?>
            <a href="google_redirect.php" class="btn-google" title="Se connecter avec Google">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Se connecter avec Google
            </a>
            <p class="auth-divider">ou</p>
        <?php endif; ?>

        <form action="" method="post" class="form-auth">
            <div class="form-group">
                <label for="emailUser">Email</label>
                <input type="email" id="emailUser" name="emailUser" maxlength="50" required
                       placeholder="exemple@email.com" autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" maxlength="255" required
                       placeholder="••••••••" autocomplete="current-password">
            </div>

            <button type="submit" class="btn-submit">Connexion</button>
        </form>

        <p class="auth-footer">
            Pas encore de compte ? <a href="register.php">S'inscrire</a>
        </p>
    </div>
</main>

</body>
</html>