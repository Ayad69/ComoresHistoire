<?php
require_once __DIR__ . '/../config/init_session.php';

$pageTitle = 'Thèmes';
$baseUrl = '../';
$userConnected = !empty($_SESSION['idUser']);
$isEcrivain = $userConnected && !empty($_SESSION['ecrivain']);

$themes = [];
try {
    require_once __DIR__ . '/../models/DAO/Database.php';
    require_once __DIR__ . '/../models/DTO/Theme.php';
    require_once __DIR__ . '/../models/DAO/ThemeDAO.php';
    $themeDao = new \Comores\Models\DAO\ThemeDAO();
    $themes = $themeDao->readAll();
} catch (Throwable $e) {
    // BDD non configurée ou erreur : on affiche une liste vide
}

require_once __DIR__ . '/_header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les Thèmes — Histoire des Comores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --teal:    #1a6b5a;
            --teal-dk: #124d41;
            --teal-lt: #e8f4f1;
            --gold:    #c9a84c;
            --text:    #1c2b27;
            --muted:   #6b8078;
            --border:  #cdddd9;
            --bg:      #f5f9f8;
            --white:   #ffffff;
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
        .site-header { background: var(--teal-dk); padding: 0 2rem; }
        .nav-main {
            display: flex;
            align-items: center;
            height: 64px;
            position: relative;
        }
        .logo {
            color: #fff !important;
            text-decoration: none;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 600;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .nav-menu { display: flex; align-items: center; flex: 1; position: relative; }
        .nav-center {
            list-style: none;
            position: absolute;
            left: 50%; transform: translateX(-50%);
        }
        .nav-center li a { color: #fff !important; text-decoration: none; font-weight: 500; }
        .nav-right {
            list-style: none;
            display: flex;
            gap: 1.5rem;
            margin-left: auto;
        }
        .nav-right li a { color: #fff !important; text-decoration: none; font-weight: 500; }
        .nav-right li a:hover, .nav-center li a:hover, .logo:hover { opacity: .8; }
        .nav-ecrivain { color: #fff !important; text-decoration: none; font-weight: 500; margin-right: 1.5rem; }
        .nav-ecrivain:hover { opacity: .9; }

        /* Mobile : menu hamburger uniquement */
        @media (max-width: 768px) {
            .nav-main { padding: 0.6rem 1rem; height: auto; }
            .nav-menu {
                display: none !important;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                flex-direction: column;
                background: #124d41;
                padding: 1rem;
                border-bottom: 2px solid rgba(201,168,76,0.3);
                box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            }
            .nav-menu.is-open { display: flex !important; }
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
            .nav-toggle span { width: 24px; height: 2px; background: #fff; }
            .logo { font-size: 1.1rem; max-width: 50%; overflow: hidden; text-overflow: ellipsis; }
            .nav-ecrivain { margin-right: 0.5rem; font-size: 0.9rem; }
        }

        /* ── PAGE HERO ── */
        .page-hero {
            background: linear-gradient(135deg, var(--teal-dk) 0%, var(--teal) 100%);
            padding: 3.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .page-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .page-hero h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.6rem;
            font-weight: 600;
            color: #fff;
            position: relative;
        }
        .page-hero p {
            color: rgba(255,255,255,.75);
            margin-top: .6rem;
            font-size: 1.05rem;
            position: relative;
        }
        /* Gold underline */
        .page-hero h1::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--gold);
            margin: .7rem auto 0;
            border-radius: 2px;
        }

        /* ── MAIN ── */
        main { flex: 1; padding: 3rem 1rem; }
        .container { max-width: 900px; margin: 0 auto; }

        /* ── THEME GRID ── */
        .theme-list {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.25rem;
            margin-top: 2rem;
        }

        .theme-item { display: contents; }

        .theme-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.4rem 1.6rem;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            text-decoration: none;
            color: var(--text);
            box-shadow: 0 2px 12px rgba(26,107,90,.07);
            transition: border-color .2s, box-shadow .2s, transform .2s, background .2s;
            position: relative;
            overflow: hidden;
        }

        .theme-link::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: var(--gold);
            border-radius: 12px 0 0 12px;
            transform: scaleY(0);
            transition: transform .2s;
            transform-origin: bottom;
        }

        .theme-link:hover {
            border-color: var(--teal);
            box-shadow: 0 6px 24px rgba(26,107,90,.14);
            transform: translateY(-2px);
            background: var(--teal-lt);
        }

        .theme-link:hover::before { transform: scaleY(1); }

        .theme-icon {
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            background: var(--teal-lt);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            transition: background .2s;
        }

        .theme-link:hover .theme-icon { background: rgba(26,107,90,.15); }

        .theme-label {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--teal-dk);
            line-height: 1.3;
        }

        .theme-arrow {
            margin-left: auto;
            color: var(--muted);
            font-size: 1.1rem;
            transition: transform .2s, color .2s;
        }
        .theme-link:hover .theme-arrow { transform: translateX(4px); color: var(--teal); }

        /* Empty state */
        .themes-empty {
            text-align: center;
            padding: 3rem;
            color: var(--muted);
            font-size: 1rem;
            background: var(--white);
            border-radius: 12px;
            border: 1.5px dashed var(--border);
            margin-top: 2rem;
        }

        /* Back link */
        .themes-back {
            text-align: center;
            margin-top: 3rem;
        }
        .btn-back {
            display: inline-block;
            padding: .7rem 2rem;
            border: 1.5px solid var(--teal);
            color: var(--teal);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: .95rem;
            transition: background .2s, color .2s;
        }
        .btn-back:hover { background: var(--teal); color: #fff; }

        /* ── FOOTER ── */
        .site-footer {
            background: var(--teal-dk);
            color: rgba(255,255,255,.6);
            text-align: center;
            padding: 1.25rem;
            font-size: .88rem;
        }

        @media (max-width: 480px) {
            .page-hero h1 { font-size: 1.9rem; }
            .theme-list { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<header class="site-header">
    <nav class="nav-main">
        <?php if ($isEcrivain): ?>
            <a href="ecrivain.php" class="nav-ecrivain">Espace écrivain</a>
        <?php endif; ?>
        <a href="../index.php" class="logo">Histoire des Comores</a>
        <div class="nav-menu">
            <ul class="nav-center">
                <li><a href="../index.php#accueil">Accueil</a></li>
            </ul>
            <ul class="nav-right">
                <?php if ($userConnected): ?>
                    <li><a href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="register.php">Inscription</a></li>
                    <li><a href="login.php">Se connecter</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <button class="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>
</header>

<!-- Page hero -->
<div class="page-hero">
    <h1>Les Thèmes</h1>
    <p>Découvrez les histoires des Comores par thème.</p>
</div>

<main>
    <div class="container">

        <?php if (count($themes) === 0): ?>
            <p class="themes-empty">Aucun thème pour le moment.</p>
        <?php else: ?>
            <ul class="theme-list">
                <?php
                /* Icônes associées par ordre (vous pouvez les personnaliser) */
                $icons = ['🏛️','🌿','🌊','🕌','⚔️','🎭','🗺️','📖','🎶','🌺'];
                $i = 0;
                ?>
                <?php foreach ($themes as $theme): ?>
                    <li class="theme-item">
                        <a href="theme.php?idTheme=<?php echo (int) $theme->getIdTheme(); ?>" class="theme-link">
                            <div class="theme-icon"><?php echo $icons[$i % count($icons)]; ?></div>
                            <span class="theme-label"><?php echo htmlspecialchars($theme->getLibTheme()); ?></span>
                            <span class="theme-arrow">→</span>
                        </a>
                    </li>
                    <?php $i++; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="themes-back">
            <a href="<?php echo htmlspecialchars($baseUrl); ?>index.php" class="btn-back">← Retour à l'accueil</a>
        </div>

    </div>
</main>

<footer class="site-footer">
    <p>Histoire des Comores — Rédaction personnelle.</p>
</footer>

<script src="../js/main.js"></script>
</body>
</html>