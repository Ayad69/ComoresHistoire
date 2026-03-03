<?php
require_once __DIR__ . '/../config/init_session.php';

$baseUrl = '../';
$userConnected = !empty($_SESSION['idUser']);
$isEcrivain = $userConnected && !empty($_SESSION['ecrivain']);

$idArticle = isset($_GET['idArticle']) ? (int) $_GET['idArticle'] : 0;
$article = null;
$theme = null;
$commentaires = [];
$errors = [];

// Enregistrement d'un nouveau commentaire (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $idArticle > 0 && $userConnected) {
    $contenu = trim($_POST['contenuCommentaire'] ?? '');
    if ($contenu === '') {
        $errors[] = 'Veuillez écrire un commentaire.';
    } else {
        try {
            require_once __DIR__ . '/../models/DAO/Database.php';
            require_once __DIR__ . '/../models/DTO/Commentaire.php';
            require_once __DIR__ . '/../models/DAO/CommentaireDAO.php';
            $commentaireDao = new \Comores\Models\DAO\CommentaireDAO();
            $commentaire = new \Comores\Models\DTO\Commentaire();
            $commentaire->setContenuCommentaire($contenu);
            $commentaire->setDateCommentaire(new \DateTime());
            $commentaire->setIdArticle($idArticle);
            $commentaire->setIdUser((int) $_SESSION['idUser']);
            $commentaire->setIdCommentaireParent(null);
            $commentaireDao->create($commentaire);
            header('Location: commenter.php?idArticle=' . $idArticle);
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Erreur lors de l\'envoi du commentaire. Réessayez.';
        }
    }
}

// Chargement article + commentaires
if ($idArticle > 0) {
    try {
        require_once __DIR__ . '/../models/DAO/Database.php';
        require_once __DIR__ . '/../models/DTO/Article.php';
        require_once __DIR__ . '/../models/DTO/Theme.php';
        require_once __DIR__ . '/../models/DTO/User.php';
        require_once __DIR__ . '/../models/DTO/Commentaire.php';
        require_once __DIR__ . '/../models/DAO/ArticleDAO.php';
        require_once __DIR__ . '/../models/DAO/ThemeDAO.php';
        require_once __DIR__ . '/../models/DAO/UserDAO.php';
        require_once __DIR__ . '/../models/DAO/CommentaireDAO.php';
        $articleDao = new \Comores\Models\DAO\ArticleDAO();
        $themeDao = new \Comores\Models\DAO\ThemeDAO();
        $userDao = new \Comores\Models\DAO\UserDAO();
        $commentaireDao = new \Comores\Models\DAO\CommentaireDAO();
        $article = $articleDao->read($idArticle);
        if ($article !== null) {
            if ($article->getIdTheme() !== null) {
                $theme = $themeDao->read($article->getIdTheme());
            }
            $commentaires = $commentaireDao->readByArticle($idArticle);
        }
    } catch (Throwable $e) {
        // BDD erreur
    }
}

$pageTitle = $article !== null ? 'Commentaires — ' . $article->getTitreArticle() : 'Commentaires';
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
        :root { --teal: #1a6b5a; --teal-dk: #124d41; --gold: #c9a84c; --text: #1c2b27; --muted: #6b8078; --border: #cdddd9; --bg: #f5f9f8; --white: #ffffff; }
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
        @media (max-width: 768px) {
            .site-header { padding: 0; }
            .nav-main { padding: 0.6rem 1rem; height: auto; }
            .nav-menu { display: none; position: absolute; top: 100%; left: 0; right: 0; flex-direction: column; background: #124d41; padding: 1rem; }
            .nav-menu.is-open { display: flex; }
            .nav-toggle { display: flex; flex-direction: column; justify-content: center; gap: 5px; background: none; border: none; cursor: pointer; padding: 10px; min-width: 44px; min-height: 44px; }
            .nav-toggle span { width: 24px; height: 2px; background: #fff; }
        }
        .page-hero { background: linear-gradient(135deg, var(--teal-dk) 0%, var(--teal) 100%); padding: 2rem 2rem; text-align: center; }
        .page-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.75rem; font-weight: 600; color: #fff; }
        main { flex: 1; padding: 2rem; }
        .container { max-width: 720px; margin: 0 auto; }
        .commenter-back { margin-bottom: 1.5rem; }
        .btn-back { display: inline-block; padding: .6rem 1.5rem; min-height: 44px; line-height: 1.4; border: 1.5px solid var(--teal); color: var(--teal); border-radius: 8px; text-decoration: none; font-weight: 600; font-size: .9rem; }
        .btn-back:hover { background: var(--teal); color: #fff; }
        .commenter-article-ref { margin-bottom: 1.5rem; padding: 1rem 1.25rem; background: var(--white); border-radius: 8px; border: 1px solid var(--border); }
        .commenter-article-ref a { color: var(--teal); font-weight: 600; text-decoration: none; }
        .commenter-article-ref a:hover { text-decoration: underline; }
        .commenter-form-section { margin-bottom: 2rem; padding: 1.5rem; background: var(--white); border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,.06); }
        .commenter-form-section h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.35rem; color: var(--teal-dk); margin-bottom: 1rem; }
        .commenter-form .form-group { margin-bottom: 1rem; }
        .commenter-form label { display: block; font-weight: 500; margin-bottom: 0.35rem; color: var(--text); }
        .commenter-form textarea { width: 100%; min-height: 120px; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 1rem; resize: vertical; }
        .commenter-form textarea:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 2px rgba(26,107,90,.2); }
        .commenter-form .btn-submit { padding: 0.65rem 1.5rem; background: var(--teal); color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: .95rem; cursor: pointer; min-height: 44px; }
        .commenter-form .btn-submit:hover { background: var(--teal-dk); }
        .commenter-login-msg { padding: 1.25rem; background: #f0f7f5; border-radius: 8px; border: 1px solid var(--border); color: var(--muted); }
        .commenter-login-msg a { color: var(--teal); font-weight: 600; text-decoration: none; }
        .commenter-login-msg a:hover { text-decoration: underline; }
        .commenter-errors { margin-bottom: 1rem; padding: 0.75rem 1rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #b91c1c; font-size: 0.9rem; }
        .commenter-list { list-style: none; padding: 0; margin: 0; }
        .commenter-item { padding: 1rem 1.25rem; margin-bottom: 0.75rem; background: var(--white); border-radius: 8px; border: 1px solid var(--border); }
        .commenter-item-header { font-size: 0.9rem; color: var(--muted); margin-bottom: 0.5rem; }
        .commenter-item-header strong { color: var(--text); }
        .commenter-item-content { line-height: 1.6; white-space: pre-wrap; word-break: break-word; }
        .commenter-empty { padding: 2rem; text-align: center; color: var(--muted); background: var(--white); border-radius: 8px; border: 1px dashed var(--border); }
        .site-footer { background: var(--teal-dk); color: rgba(255,255,255,.6); text-align: center; padding: 1.25rem; font-size: .88rem; }
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
            <div class="commenter-empty">
                <p>Cet article n'existe pas ou l'URL est incorrecte.</p>
                <a href="themes.php" class="btn-back" style="margin-top:1rem;display:inline-block;">← Retour aux thèmes</a>
            </div>
        </div>
    </main>
<?php else: ?>
    <div class="page-hero">
        <h1>Commentaires</h1>
    </div>

    <main>
        <div class="container">
            <p class="commenter-back">
                <a href="article.php?idArticle=<?php echo (int) $idArticle; ?>" class="btn-back">← Retour à l'article</a>
            </p>

            <div class="commenter-article-ref">
                Article : <a href="article.php?idArticle=<?php echo (int) $idArticle; ?>"><?php echo htmlspecialchars($article->getTitreArticle()); ?></a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="commenter-errors">
                    <?php foreach ($errors as $err): ?>
                        <p><?php echo htmlspecialchars($err); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($userConnected): ?>
                <section class="commenter-form-section">
                    <h2>Écrire un commentaire</h2>
                    <form class="commenter-form" method="post" action="commenter.php?idArticle=<?php echo (int) $idArticle; ?>">
                        <div class="form-group">
                            <label for="contenuCommentaire">Votre commentaire</label>
                            <textarea id="contenuCommentaire" name="contenuCommentaire" placeholder="Partagez votre avis..." required><?php echo htmlspecialchars($_POST['contenuCommentaire'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Publier le commentaire</button>
                    </form>
                </section>
            <?php else: ?>
                <div class="commenter-login-msg">
                    <a href="login.php?redirect=<?php echo urlencode('views/commenter.php?idArticle=' . $idArticle); ?>">Connectez-vous</a> pour écrire un commentaire.
                </div>
            <?php endif; ?>

            <?php
            $commentairesRacine = array_filter($commentaires, fn($c) => $c->getIdCommentaireParent() === null);
            $userDaoCommentaires = new \Comores\Models\DAO\UserDAO();
            ?>
            <section class="commenter-list-section">
                <h2 style="font-family:'Cormorant Garamond',serif;font-size:1.35rem;color:var(--teal-dk);margin-bottom:1rem;">Commentaires (<?php echo count($commentairesRacine); ?>)</h2>
                <?php if (empty($commentairesRacine)): ?>
                    <div class="commenter-empty">Aucun commentaire pour le moment. Soyez le premier à réagir !</div>
                <?php else: ?>
                    <ul class="commenter-list">
                        <?php foreach ($commentairesRacine as $c):
                            $auteur = $userDaoCommentaires->read($c->getIdUser());
                            $nomAuteur = $auteur !== null ? trim($auteur->getPrenomUser() . ' ' . $auteur->getNomUser()) : 'Anonyme';
                            $dateStr = $c->getDateCommentaire() ? $c->getDateCommentaire()->format('d/m/Y à H:i') : '';
                        ?>
                        <li class="commenter-item">
                            <div class="commenter-item-header">
                                <strong><?php echo htmlspecialchars($nomAuteur); ?></strong> — <?php echo htmlspecialchars($dateStr); ?>
                            </div>
                            <div class="commenter-item-content"><?php echo nl2br(htmlspecialchars($c->getContenuCommentaire() ?? '')); ?></div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <p class="commenter-back" style="margin-top:1.5rem;">
                <a href="article.php?idArticle=<?php echo (int) $idArticle; ?>" class="btn-back">← Retour à l'article</a>
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
