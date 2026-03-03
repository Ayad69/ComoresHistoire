<?php
require_once __DIR__ . '/../config/init_session.php';

$pageTitle = 'Articles par thème';
$baseUrl = '../';
$userConnected = !empty($_SESSION['idUser']);
$isEcrivain = $userConnected && !empty($_SESSION['ecrivain']);

$idTheme = isset($_GET['idTheme']) ? (int) $_GET['idTheme'] : 0;
$theme = null;
$articles = [];

if ($idTheme > 0) {
    try {
        require_once __DIR__ . '/../models/DAO/Database.php';
        require_once __DIR__ . '/../models/DTO/Theme.php';
        require_once __DIR__ . '/../models/DTO/Article.php';
        require_once __DIR__ . '/../models/DTO/User.php';
        require_once __DIR__ . '/../models/DAO/ThemeDAO.php';
        require_once __DIR__ . '/../models/DAO/ArticleDAO.php';
        require_once __DIR__ . '/../models/DAO/UserDAO.php';
        $themeDao = new \Comores\Models\DAO\ThemeDAO();
        $articleDao = new \Comores\Models\DAO\ArticleDAO();
        $userDao = new \Comores\Models\DAO\UserDAO();
        $theme = $themeDao->read($idTheme);
        if ($theme !== null) {
            $articles = $articleDao->readByTheme($idTheme);
        }
    } catch (Throwable $e) {
        // BDD erreur
    }
}

$searchQuery = trim($_GET['search'] ?? '');
if ($searchQuery !== '' && !empty($articles)) {
    $articles = array_filter($articles, function ($article) use ($searchQuery) {
        $titre = $article->getTitreArticle() ?? '';
        return mb_stripos($titre, $searchQuery) !== false;
    });
}

require_once __DIR__ . '/_header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thème — Histoire des Comores</title>
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
        .nav-right { list-style: none; display: flex; gap: 1.5rem; margin-left: auto; }
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
        .page-hero h1::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--gold);
            margin: .7rem auto 0;
            border-radius: 2px;
        }
        .page-hero p {
            color: rgba(255,255,255,.7);
            margin-top: .6rem;
            font-size: 1rem;
            position: relative;
        }

        /* ── MAIN ── */
        main { flex: 1; padding: 3rem 1rem; }
        .container { max-width: 800px; margin: 0 auto; }

        /* ── NOT FOUND ── */
        .not-found {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: 14px;
            border: 1.5px dashed var(--border);
        }
        .not-found h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            color: var(--teal-dk);
            margin-bottom: .75rem;
        }
        .not-found p { color: var(--muted); margin-bottom: 1.5rem; }

        /* ── ARTICLE LIST ── */
        .article-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1.1rem;
            margin-top: .5rem;
        }

        .article-link {
            display: block;
            padding: 1.5rem 1.8rem;
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            text-decoration: none;
            color: var(--text);
            box-shadow: 0 2px 10px rgba(26,107,90,.06);
            transition: border-color .2s, box-shadow .2s, transform .2s;
            position: relative;
            overflow: hidden;
        }

        .article-link::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            background: var(--gold);
            border-radius: 12px 0 0 12px;
            transform: scaleY(0);
            transition: transform .22s;
            transform-origin: bottom;
        }

        .article-link:hover {
            border-color: var(--teal);
            box-shadow: 0 6px 24px rgba(26,107,90,.13);
            transform: translateY(-2px);
        }
        .article-link:hover::before { transform: scaleY(1); }

        .article-poster {
            margin: -1.5rem -1.8rem 1rem -1.8rem;
            border-radius: 12px 12px 0 0;
            overflow: hidden;
            background: var(--border);
        }
        .article-poster img {
            display: block;
            max-width: 75%;
            width: 75%;
            height: auto;
            vertical-align: middle;
            margin: 0 auto;
        }

        .article-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: .5rem;
        }

        .article-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--teal-dk);
            line-height: 1.3;
            transition: color .2s;
        }
        .article-link:hover .article-title { color: var(--teal); }

        .article-date {
            font-size: .82rem;
            color: var(--muted);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .article-excerpt,
        .article-author {
            font-size: .93rem;
            color: var(--muted);
            line-height: 1.6;
            margin-top: .25rem;
        }

        /* Search */
        .theme-search {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .theme-search-input {
            flex: 1;
            min-width: 200px;
            padding: 0.65rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 1rem;
            color: var(--text);
            background: var(--white);
        }
        .theme-search-input:focus {
            outline: none;
            border-color: var(--teal);
            box-shadow: 0 0 0 2px rgba(26, 107, 90, 0.15);
        }
        .theme-search-input::placeholder {
            color: var(--muted);
        }
        .theme-search-btn {
            padding: 0.65rem 1.25rem;
            background: var(--teal);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-family: 'Source Sans 3', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }
        .theme-search-btn:hover {
            background: var(--teal-dk);
            transform: translateY(-1px);
        }

        /* Empty */
        .articles-empty {
            text-align: center;
            padding: 3rem;
            color: var(--muted);
            background: var(--white);
            border-radius: 12px;
            border: 1.5px dashed var(--border);
            margin-top: .5rem;
        }

        /* Back */
        .theme-back {
            text-align: center;
            margin-top: 2.5rem;
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

        @media (max-width: 520px) {
            .page-hero h1 { font-size: 1.9rem; }
            .article-header { flex-direction: column; gap: .2rem; }
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

<?php if ($theme === null): ?>

    <div class="page-hero">
        <h1>Thème introuvable</h1>
    </div>
    <main>
        <div class="container">
            <div class="not-found">
                <h2>Ce thème n'existe pas</h2>
                <p>Il a peut-être été supprimé ou l'URL est incorrecte.</p>
                <a href="themes.php" class="btn-back">← Retour aux thèmes</a>
            </div>
        </div>
    </main>

<?php else: ?>

    <div class="page-hero">
        <h1><?php echo htmlspecialchars($theme->getLibTheme()); ?></h1>
        <p>Articles de ce thème</p>
    </div>

    <main>
        <div class="container">

            <form class="theme-search" method="get" action="">
                <input type="hidden" name="idTheme" value="<?php echo (int) $idTheme; ?>">
                <input type="search" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Rechercher par titre d'article..." class="theme-search-input" aria-label="Rechercher par titre">
                <button type="submit" class="theme-search-btn">Rechercher</button>
            </form>

            <?php if (count($articles) === 0): ?>
                <p class="articles-empty"><?php echo $searchQuery !== '' ? 'Aucun article ne correspond à votre recherche.' : 'Aucun article dans ce thème pour le moment.'; ?></p>
            <?php else: ?>
                <ul class="article-list">
                    <?php foreach ($articles as $article): ?>
                        <li>
                            <a href="article.php?idArticle=<?php echo (int) $article->getIdArticle(); ?>" class="article-link">
                                <div class="article-header">
                                    <span class="article-title"><?php echo htmlspecialchars($article->getTitreArticle()); ?></span>
                                    <?php if ($article->getDateArticle()): ?>
                                        <time class="article-date" datetime="<?php echo $article->getDateArticle()->format('Y-m-d'); ?>">
                                            <?php echo $article->getDateArticle()->format('d/m/Y'); ?>
                                        </time>
                                    <?php endif; ?>
                                </div>
                                <?php
                                $author = ($article->getIdUser() !== null) ? $userDao->read($article->getIdUser()) : null;
                                if ($author !== null):
                                    $authorName = trim($author->getPrenomUser() . ' ' . $author->getNomUser());
                                    ?>
                                    <p class="article-author">Par <?php echo htmlspecialchars($authorName); ?></p>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class="theme-back">
                <a href="themes.php" class="btn-back">← Retour aux thèmes</a>
            </div>

        </div>
    </main>

<?php endif; ?>

<footer class="site-footer">
    <p>Histoire des Comores — Rédaction personnelle.</p>
</footer>

<script src="../js/main.js"></script>
</body>
</html>