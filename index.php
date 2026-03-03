<?php
require_once __DIR__ . '/config/init_session.php';
$userConnected = !empty($_SESSION['idUser']);
$isEcrivain = $userConnected && !empty($_SESSION['ecrivain']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histoire des Comores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        html, body { overflow-x: hidden; }
        .nav-main {
            display: flex;
            align-items: center;
            position: relative;
            padding: 0 2rem;
        }
        .logo {
            color: #fff !important;
            text-decoration: none;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .nav-menu {
            display: flex;
            align-items: center;
            flex: 1;
            position: relative;
        }
        .nav-center {
            list-style: none;
            margin: 0;
            padding: 0;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.15rem;
        }
        .nav-center li a {
            color: #fff !important;
            text-decoration: none;
            font-weight: 500;
        }
        .nav-right {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            gap: 1.5rem;
            margin-left: auto;
        }
        .nav-right li a {
            color: #fff !important;
            text-decoration: none;
            font-weight: 500;
        }
        .nav-bonjour {
            color: #fff !important;
            font-weight: 500;
            list-style: none;
        }
        .nav-right li a:hover,
        .nav-center li a:hover,
        .logo:hover { opacity: 0.8; }

        .btn-stories {
            display: inline-block;
            padding: .85rem 2.4rem;
            background: #1a6b5a;
            color: #fff;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-decoration: none;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(26,107,90,.28);
            transition: background .2s, transform .15s, box-shadow .2s;
        }
        .btn-stories:hover {
            background: #124d41;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(26,107,90,.35);
        }
        .btn-stories:active { transform: translateY(0); }

        .intro-actions {
            text-align: center;
            margin-top: 2rem;
        }
        .hero-bonjour {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: clamp(1.75rem, 4vw, 2.5rem);
            font-weight: 600;
            color: #f8f6f3;
            margin: 0 0 0.5rem;
            letter-spacing: 0.02em;
        }
        .nav-ecrivain {
            color: #fff !important;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            margin-right: 1.5rem;
            padding: 0.35em 0;
        }
        .nav-ecrivain:hover { opacity: 0.9; }

        /* Mobile : masquer le menu horizontal, afficher uniquement le hamburger */
        @media (max-width: 768px) {
            .nav-main { padding: 0.6rem 1rem; }
            .nav-menu {
                display: none !important;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                flex-direction: column;
                background: #1e5f74;
                padding: 1rem;
                border-bottom: 2px solid rgba(252, 209, 22, 0.3);
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            }
            .nav-menu.is-open {
                display: flex !important;
            }
            .nav-menu.is-open .nav-center,
            .nav-menu.is-open .nav-right {
                position: static;
                transform: none;
                margin: 0;
                flex-direction: column;
                width: 100%;
                gap: 0.25rem;
            }
            .nav-menu.is-open .nav-center {
                border-bottom: 1px solid rgba(255,255,255,0.15);
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }
            .nav-menu.is-open .nav-center a,
            .nav-menu.is-open .nav-right a {
                display: block;
                padding: 0.75rem 0.5rem;
                min-height: 44px;
            }
            .nav-toggle {
                display: flex !important;
                flex-direction: column;
                justify-content: center;
                gap: 5px;
                background: none;
                border: none;
                cursor: pointer;
                padding: 10px;
                min-width: 44px;
                min-height: 44px;
            }
            .nav-toggle span {
                width: 24px;
                height: 2px;
                background: #fff;
            }
            .hero { padding-left: 1.25rem; padding-right: 1.25rem; }
            .hero-content { width: 100%; max-width: 100%; box-sizing: border-box; padding: 0 0.25rem; }
            .hero h1 { font-size: 1.65rem; word-wrap: break-word; overflow-wrap: break-word; }
            .logo { font-size: 1rem; max-width: none; white-space: normal; line-height: 1.25; }
            .nav-ecrivain { margin-right: 0.5rem; font-size: 0.85rem; max-width: 38%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        }
        @media (max-width: 480px) {
            .nav-main { padding: 0.5rem 0.75rem; }
            .hero { padding: 2rem 1rem 1.5rem; min-height: 65vh; }
            .hero-content { padding: 0; }
            .hero h1 { font-size: 1.45rem; }
            .hero-bonjour { font-size: 1.35rem; }
            .hero-subtitle { font-size: 0.9rem; }
            .logo { font-size: 0.95rem; }
            .nav-ecrivain { font-size: 0.8rem; max-width: 35%; }
        }
        @media (max-width: 360px) {
            .hero h1 { font-size: 1.25rem; }
            .logo { font-size: 0.9rem; }
        }
    </style>
</head>
<body>

<header class="site-header">
    <nav class="nav-main">
        <?php if ($isEcrivain): ?>
            <a href="views/ecrivain.php" class="nav-ecrivain">Espace écrivain</a>
        <?php endif; ?>
        <a href="#accueil" class="logo">Histoire des Comores</a>
        <div class="nav-menu">
            <ul class="nav-center">
                <li><a href="#accueil">Accueil</a></li>
            </ul>
            <ul class="nav-right">
                <?php if ($userConnected): ?>
                    <li><a href="views/logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="views/register.php">Inscription</a></li>
                    <li><a href="views/login.php">Se connecter</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <button class="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>
</header>

<main>
    <section id="accueil" class="hero">
        <div class="hero-content">
            <?php if ($userConnected): ?>
                <p class="hero-bonjour">Bonjour <?php echo htmlspecialchars($_SESSION['nomUser']); echo ' ' ,htmlspecialchars($_SESSION['prenomUser']);  ?> !</p>
            <?php endif; ?>
            <h1>Histoire des Comores</h1>
            <p class="hero-subtitle">L'archipel aux quatre îles : Grande Comore, Anjouan, Mohéli et Mayotte</p>
            <a href="#introduction" class="btn-hero">Découvrir</a>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <section id="introduction" class="section">
        <div class="container">
            <h2>Introduction des Comores</h2>
            <div class="content-block">
                <p>Les Comores forment un archipel de l'océan Indien, au nord du canal du Mozambique, entre Madagascar et les côtes africaines. Quatre îles principales le composent : la Grande Comore (Ngazidja), Anjouan (Ndzuwani), Mohéli (Mwali) et Mayotte (Maore). Volcaniques, verdoyantes et entourées de lagons, ces terres ont vu se croiser, au fil des siècles, peuplements bantous, influences arabes et perses, sultanats et commerce de l'océan Indien.</p>
                <p>Les Comores possèdent de merveilleuses histoires : récits de sultans, de résistances, d'indépendance et d'une culture riche où se mêlent arabe, swahili et français. Ce site vous invite à les découvrir.</p>
            </div>
            <div class="intro-actions">
                <a href="views/themes.php" class="btn-stories">Lire les histoires</a>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container">
        <p>Histoire des Comores — Rédaction personnelle.</p>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>