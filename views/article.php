<?php
require_once __DIR__ . '/../config/init_session.php';

$baseUrl = '../';
$userConnected = !empty($_SESSION['idUser']);
$isEcrivain = $userConnected && !empty($_SESSION['ecrivain']);

$idArticle = isset($_GET['idArticle']) ? (int) $_GET['idArticle'] : 0;
$article = null;
$theme = null;
$author = null;

if ($idArticle > 0) {
    try {
        require_once __DIR__ . '/../models/DAO/Database.php';
        require_once __DIR__ . '/../models/DTO/Article.php';
        require_once __DIR__ . '/../models/DTO/Theme.php';
        require_once __DIR__ . '/../models/DTO/User.php';
        require_once __DIR__ . '/../models/DAO/ArticleDAO.php';
        require_once __DIR__ . '/../models/DAO/ThemeDAO.php';
        require_once __DIR__ . '/../models/DAO/UserDAO.php';
        $articleDao = new \Comores\Models\DAO\ArticleDAO();
        $themeDao = new \Comores\Models\DAO\ThemeDAO();
        $userDao = new \Comores\Models\DAO\UserDAO();
        $article = $articleDao->read($idArticle);
        if ($article !== null) {
            if ($article->getIdTheme() !== null) {
                $theme = $themeDao->read($article->getIdTheme());
            }
            if ($article->getIdUser() !== null) {
                $author = $userDao->read($article->getIdUser());
            }
        }
    } catch (Throwable $e) {
        // BDD erreur
    }
}

$pageTitle = $article !== null ? $article->getTitreArticle() : 'Article';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> — Histoire des Comores</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --teal: #1a6b5a; --teal-dk: #124d41; --gold: #c9a84c;
            --text: #1c2b27; --muted: #6b8078; --border: #cdddd9;
            --bg: #f5f9f8; --white: #ffffff;
        }
        html { overflow-x: hidden; }
        body { font-family: 'Source Sans 3', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }
        .site-header { background: var(--teal-dk); padding: 0 2rem; }
        .nav-main { display: flex; align-items: center; height: 64px; position: relative; }
        .logo { color: #fff !important; text-decoration: none; font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 600; white-space: nowrap; flex-shrink: 0; }
        .nav-menu { display: flex; align-items: center; flex: 1; position: relative; }
        .nav-center { list-style: none; position: absolute; left: 50%; transform: translateX(-50%); }
        .nav-center li a { color: #fff !important; text-decoration: none; font-weight: 500; }
        .nav-right { list-style: none; display: flex; gap: 1.5rem; margin-left: auto; }
        .nav-right li a { color: #fff !important; text-decoration: none; font-weight: 500; }
        .nav-right li a:hover, .nav-center li a:hover, .logo:hover { opacity: .8; }
        .nav-ecrivain { color: #fff !important; text-decoration: none; font-weight: 500; margin-right: 1.5rem; }
        .nav-ecrivain:hover { opacity: .9; }
        @media (max-width: 768px) {
            .site-header { padding: 0; }
            .nav-main { padding: 0.6rem 1rem; height: auto; }
            .logo { font-size: 1.1rem; max-width: 55%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .nav-ecrivain { margin-right: 0.5rem; font-size: 0.9rem; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; right: 0; flex-direction: column; background: #124d41; padding: 1rem; }
            .nav-menu.is-open { display: flex; }
            .nav-menu.is-open .nav-center, .nav-menu.is-open .nav-right { flex-direction: column; width: 100%; }
            .nav-menu.is-open .nav-center a, .nav-menu.is-open .nav-right a { display: block; padding: 0.75rem 0.5rem; min-height: 44px; }
            .nav-toggle { display: flex; flex-direction: column; justify-content: center; gap: 5px; background: none; border: none; cursor: pointer; padding: 10px; min-width: 44px; min-height: 44px; }
            .nav-toggle span { width: 24px; height: 2px; background: #fff; }
        }
        @media (max-width: 480px) {
            .logo { font-size: 1rem; max-width: 50%; }
        }

        .page-hero {
            position: relative;
            background: linear-gradient(135deg, var(--teal-dk) 0%, var(--teal) 100%);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            z-index: 0;
        }
        .page-hero h1 {
            position: relative;
            z-index: 1;
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            font-weight: 600;
            color: #fff;
            margin: 0;
            padding: 0 0.5rem;
            line-height: 1.35;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }
        .page-hero .article-meta { color: rgba(255,255,255,.75); font-size: .9rem; margin-top: .5rem; }
        .article-author { font-size: .9rem; color: var(--muted); margin-bottom: 1rem; }

        main { flex: 1; padding: 2rem 1rem; }
        .container { max-width: 720px; margin: 0 auto; padding: 0 0.5rem; }
        .article-single { background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.06); border: 1px solid var(--border); }
        .article-single .poster { display: block; max-width: 70%; width: 70%; height: auto; margin: 0 auto; }
        .article-single .body { padding: 1.8rem; isolation: isolate; }
        .article-single .title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--teal-dk);
            margin: 0 0 .5rem;
            line-height: 1.3;
            transform: none;
            backface-visibility: visible;
        }
        .article-single .date { font-size: .85rem; color: var(--muted); margin-bottom: 1rem; }
        .article-single .content { color: var(--text); line-height: 1.7; }
        .article-single .content p { margin: 0 0 1rem; }
        .article-single .content p:last-child { margin-bottom: 0; }
        .article-meta-footer {
            margin-top: 2rem;
            padding: 0.85rem 1.25rem;
            background: var(--teal-dk);
            color: rgba(255,255,255,.9);
            font-size: 0.9rem;
            border-radius: 0 0 12px 12px;
            margin-left: -1.8rem;
            margin-right: -1.8rem;
            margin-bottom: -1.8rem;
            padding-left: 1.8rem;
            padding-right: 1.8rem;
            padding-bottom: 1.25rem;
        }

        .not-found { text-align: center; padding: 3rem 2rem; background: var(--white); border-radius: 12px; border: 1.5px dashed var(--border); }
        .not-found h2 { font-family: 'Cormorant Garamond', serif; color: var(--teal-dk); margin-bottom: .75rem; }
        .not-found p { color: var(--muted); margin-bottom: 1rem; }
        .btn-back { display: inline-block; padding: .6rem 1.5rem; min-height: 44px; line-height: 1.4; border: 1.5px solid var(--teal); color: var(--teal); border-radius: 8px; text-decoration: none; font-weight: 600; font-size: .9rem; -webkit-tap-highlight-color: transparent; }
        .btn-back:hover { background: var(--teal); color: #fff; }
        .article-actions { margin-top: 1.5rem; }
        .btn-commenter { display: inline-block; padding: .6rem 1.5rem; min-height: 44px; line-height: 1.4; background: var(--teal); color: #fff; border: none; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: .9rem; -webkit-tap-highlight-color: transparent; }
        .btn-commenter:hover { background: var(--teal-dk); color: #fff; }
        .article-back { margin-top: 1rem; }
        .site-footer { background: var(--teal-dk); color: rgba(255,255,255,.6); text-align: center; padding: 1.25rem; font-size: .88rem; }

        @media (max-width: 768px) {
            main { padding: 1.5rem 0.75rem; }
            .container { padding: 0 0.75rem; }
            .article-single .poster { max-width: 100%; width: 100%; }
            .article-single .body { padding: 1.25rem; }
            .article-meta-footer { margin-left: -1.25rem; margin-right: -1.25rem; margin-bottom: -1.25rem; padding-left: 1.25rem; padding-right: 1.25rem; }
            .article-single .title { font-size: 1.5rem; }
            .page-hero { padding: 1.75rem 1rem; }
            .page-hero h1 { font-size: 1.5rem; }
            .page-hero .article-meta { font-size: 0.85rem; }
            .not-found { padding: 2rem 1.25rem; }
            .not-found h2 { font-size: 1.4rem; }
            .site-footer { padding: 1rem 0.75rem; font-size: 0.85rem; }
        }
        @media (max-width: 480px) {
            main { padding: 1rem 0.5rem; }
            .container { padding: 0 0.5rem; }
            .article-single .body { padding: 1rem; }
            .article-meta-footer { margin-left: -1rem; margin-right: -1rem; margin-bottom: -1rem; padding-left: 1rem; padding-right: 1rem; font-size: 0.85rem; }
            .article-single .title { font-size: 1.3rem; }
            .article-single .date, .article-single .article-author { font-size: 0.8rem; }
            .article-single .content { font-size: 1rem; }
            .page-hero { padding: 1.25rem 0.75rem; }
            .page-hero h1 { font-size: 1.25rem; }
            .page-hero .article-meta { font-size: 0.8rem; line-height: 1.4; }
            .not-found { padding: 1.5rem 1rem; }
            .not-found h2 { font-size: 1.25rem; }
            .btn-back { padding: 0.65rem 1.25rem; font-size: 0.9rem; }
            .article-back { margin-top: 1.25rem; }
            .site-footer { padding: 1rem 0.5rem; font-size: 0.8rem; }
        }
        @media (max-width: 360px) {
            .page-hero h1 { font-size: 1.1rem; }
            .article-single .title { font-size: 1.2rem; }
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

<?php if ($article === null): ?>

    <div class="page-hero"><h1>Article introuvable</h1></div>
    <main>
        <div class="container">
            <div class="not-found">
                <h2>Cet article n'existe pas</h2>
                <p>Il a peut-être été supprimé ou l'URL est incorrecte.</p>
                <a href="themes.php" class="btn-back">← Retour aux thèmes</a>
            </div>
        </div>
    </main>

<?php else: ?>

    <div class="page-hero">
        <h1><?php echo htmlspecialchars($article->getTitreArticle()); ?></h1>
    </div>

    <main>
        <div class="container">
            <article class="article-single">
                <?php
                $poster = $article->getPosterArticle();
                if ($poster !== null && $poster !== '') {
                    $posterSrc = (strpos($poster, 'http') === 0 || strpos($poster, '/') === 0) ? $poster : $baseUrl . $poster;
                    echo '<img class="poster" src="' . htmlspecialchars($posterSrc) . '" alt="' . htmlspecialchars($article->getTitreArticle()) . '">';
                }
                ?>
                <div class="body">
                    <h2 class="title"><?php echo htmlspecialchars($article->getTitreArticle()); ?></h2>
                    <?php
                    $contenu = $article->getContenuArticle() ?? '';
                    $isHtml = $contenu !== '' && strpos($contenu, '<') !== false;
                    $allowedTags = '<p><br><strong><em><b><i><u><ul><ol><li><span><h1><h2><h3>';
                    ?>
                    <div class="content">
                        <?php
                        if ($contenu !== '') {
                            if ($isHtml) {
                                echo strip_tags($contenu, $allowedTags);
                            } else {
                                echo nl2br(htmlspecialchars($contenu));
                            }
                        } else {
                            echo '<p>Aucun contenu.</p>';
                        }
                        ?>
                    </div>
                    <p class="article-meta-footer">
                        <?php if ($author !== null): ?>
                            Publié par <?php echo htmlspecialchars(trim($author->getPrenomUser() . ' ' . $author->getNomUser())); ?>
                        <?php endif; ?>
                        <?php if ($article->getDateArticle()): ?>
                            <?php if ($author !== null) echo ' le '; ?><?php echo $article->getDateArticle()->format('d/m/Y'); ?>
                        <?php endif; ?>
                        <?php if ($theme !== null): ?>
                            <?php if ($author !== null || $article->getDateArticle()) echo ' — '; ?><?php echo htmlspecialchars($theme->getLibTheme()); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </article>

            <p class="article-actions">
                <a href="commenter.php?idArticle=<?php echo (int) $idArticle; ?>" class="btn-commenter">Commenter</a>
            </p>

            <p class="article-back">
                <?php if ($theme !== null): ?>
                    <a href="theme.php?idTheme=<?php echo (int) $theme->getIdTheme(); ?>" class="btn-back">← Retour au thème</a>
                <?php else: ?>
                    <a href="themes.php" class="btn-back">← Retour aux thèmes</a>
                <?php endif; ?>
            </p>
        </div>
    </main>

<?php endif; ?>

<footer class="site-footer">
    <p>Histoire des Comores — Rédaction personnelle.</p>
</footer>

<script src="../js/main.js"></script>
</body>
</html>
