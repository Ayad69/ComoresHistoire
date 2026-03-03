<?php
require_once __DIR__ . '/../config/init_session.php';

$baseUrl = '../';
$userConnected = !empty($_SESSION['idUser']);
$isEcrivain = $userConnected && !empty($_SESSION['ecrivain']);

if (!$isEcrivain) {
    header('Location: ' . $baseUrl . 'index.php');
    exit;
}

$themes = [];
$message = '';
$messageType = '';

try {
    require_once __DIR__ . '/../models/DAO/Database.php';
    require_once __DIR__ . '/../models/DTO/Article.php';
    require_once __DIR__ . '/../models/DTO/Theme.php';
    require_once __DIR__ . '/../models/DAO/ArticleDAO.php';
    require_once __DIR__ . '/../models/DAO/ThemeDAO.php';
    $themeDao = new \Comores\Models\DAO\ThemeDAO();
    $themes = $themeDao->readAll();
} catch (Throwable $e) {
    $themes = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titreArticle = trim($_POST['titreArticle'] ?? '');
    $contenuArticle = trim($_POST['contenuArticle'] ?? '');
    $idTheme = isset($_POST['idTheme']) ? (int) $_POST['idTheme'] : 0;
    $idUser = (int) ($_SESSION['idUser'] ?? 0);
    $posterPath = null;

    if (strlen($titreArticle) < 1 || strlen($titreArticle) > 50) {
        $message = 'Le titre est requis (max 50 caractères).';
        $messageType = 'error';
    } elseif ($idTheme < 1) {
        $message = 'Veuillez choisir un thème.';
        $messageType = 'error';
    } else {
        if (!empty($_FILES['posterArticle']['name']) && $_FILES['posterArticle']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['posterArticle']['tmp_name']);
            if (!in_array($mime, $allowed, true)) {
                $message = 'Format d\'image non accepté (JPEG, PNG, GIF, WebP uniquement).';
                $messageType = 'error';
            } else {
                $imagesDir = __DIR__ . '/../images';
                if (!is_dir($imagesDir)) {
                    mkdir($imagesDir, 0755, true);
                }
                $ext = strtolower(pathinfo($_FILES['posterArticle']['name'], PATHINFO_EXTENSION)) ?: 'jpg';
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) $ext = 'jpg';
                $basename = date('YmdHis') . '_' . substr(uniqid(), -6) . '.' . $ext;
                $fullPath = $imagesDir . '/' . $basename;
                if (move_uploaded_file($_FILES['posterArticle']['tmp_name'], $fullPath)) {
                    $posterPath = 'images/' . $basename;
                }
            }
        }
        if ($message === '') {
            try {
                require_once __DIR__ . '/../models/DAO/Database.php';
                require_once __DIR__ . '/../models/DTO/Article.php';
                require_once __DIR__ . '/../models/DAO/ArticleDAO.php';
                $articleDao = new \Comores\Models\DAO\ArticleDAO();
                $article = new \Comores\Models\DTO\Article();
                $article->setTitreArticle($titreArticle);
                $article->setContenuArticle($contenuArticle);
                $article->setPosterArticle($posterPath);
                $article->setDateArticle(new \DateTime());
                $article->setIdUser($idUser);
                $article->setIdTheme($idTheme);
                $articleDao->create($article);
                $message = 'Article enregistré avec succès.';
                $messageType = 'success';
            } catch (Throwable $e) {
                $message = 'Erreur lors de l\'enregistrement. Réessayez.';
                $messageType = 'error';
            }
        }
    }
}

$pageTitle = 'Espace écrivain';
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
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
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
        .nav-ecrivain:hover { opacity: .9; }
        /* Mobile : menu hamburger uniquement */
        @media (max-width: 768px) {
            .site-header { padding: 0; }
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
            .logo { font-size: 1.1rem; max-width: 50%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .nav-ecrivain { margin-right: 0.5rem; font-size: 0.9rem; }
            .page-hero { padding: 1.75rem 1rem; padding-top: 4.5rem; }
            .page-hero h1 { font-size: 1.5rem; }
            .page-hero p { font-size: 0.95rem; }
            main { padding: 1.5rem 0.75rem; }
            .container { padding: 0 0.25rem; max-width: 100%; }
            .ecrivain-card { padding: 1.5rem; }
            .ecrivain-card h2 { font-size: 1.25rem; }
            .form-article { gap: 1rem; margin-top: 1.25rem; }
        }
        @media (max-width: 480px) {
            .nav-ecrivain { font-size: 0.85rem; max-width: 45%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .logo { font-size: 1rem; max-width: 45%; }
            .page-hero { padding: 1.25rem 0.75rem; padding-top: 4rem; }
            .page-hero h1 { font-size: 1.3rem; }
            .page-hero p { font-size: 0.9rem; }
            main { padding: 1rem 0.5rem; }
            .container { padding: 0; }
            .ecrivain-card { padding: 1.25rem; border-radius: 10px; }
            .ecrivain-card h2 { font-size: 1.15rem; margin-bottom: 0.75rem; }
            .ecrivain-card p { font-size: 0.9rem; }
            .form-article input[type="text"],
            .form-article select {
                min-height: 48px;
                font-size: 16px;
                padding: 0.75rem 1rem;
            }
            .form-article .editor-wrap .ql-container {
                min-height: 220px;
                font-size: 16px;
            }
            .form-article input[type="file"] { padding: 0.5rem 0; font-size: 16px; }
            .form-article .btn-submit {
                min-height: 48px;
                padding: 0.85rem 1.25rem;
                width: 100%;
                align-self: stretch;
            }
            .msg { padding: 0.85rem; font-size: 0.9rem; }
            .site-footer { padding: 1rem 0.5rem; font-size: 0.82rem; }
        }
        @media (max-width: 360px) {
            .page-hero h1 { font-size: 1.15rem; }
            .nav-ecrivain, .logo { max-width: 40%; }
        }
        /* Décalage sous le header fixe pour éviter que la bordure coupe le titre */
        .page-hero { background: linear-gradient(135deg, var(--teal-dk) 0%, var(--teal) 100%); padding: 2.5rem 2rem; padding-top: 5rem; text-align: center; }
        .page-hero h1 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 600; color: #fff; position: relative; z-index: 1; }
        .page-hero p { color: rgba(255,255,255,.8); margin-top: .5rem; }
        main { flex: 1; padding: 2rem 1rem; }
        .container { max-width: 960px; margin: 0 auto; }
        .ecrivain-card { background: var(--white); border-radius: 12px; padding: 2rem; border: 1px solid var(--border); box-shadow: 0 2px 12px rgba(0,0,0,.05); }
        .ecrivain-card h2 { font-family: 'Cormorant Garamond', serif; font-size: 1.35rem; color: var(--teal-dk); margin-bottom: 1rem; }
        .ecrivain-card p { color: var(--muted); line-height: 1.6; }
        .form-article { display: flex; flex-direction: column; gap: 1.25rem; margin-top: 1.5rem; }
        .form-article label { font-weight: 600; color: var(--teal-dk); font-size: .9rem; }
        .form-article input[type="text"], .form-article select { padding: .65rem 1rem; border: 1.5px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 1rem; }
        .form-article input[type="file"] { padding: .5rem 0; font-family: inherit; font-size: .95rem; }
        .form-hint { display: block; font-size: .85rem; color: var(--muted); margin-top: .25rem; }
        .form-article .editor-wrap { width: 100%; min-height: 320px; resize: none; }
        .form-article .editor-wrap .ql-container { min-height: 280px; font-size: 1rem; font-family: inherit; border-radius: 0 0 8px 8px; }
        .form-article .editor-wrap .ql-toolbar { border-radius: 8px 8px 0 0; border-color: var(--border); background: #fafafa; }
        .form-article .editor-wrap .ql-container { border-color: var(--border); }
        .form-article input:focus, .form-article select:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 2px rgba(26,107,90,.12); }
        .form-article .btn-submit { padding: .75rem 1.5rem; background: var(--teal); color: #fff; border: none; border-radius: 8px; font-weight: 600; font-size: 1rem; cursor: pointer; align-self: flex-start; -webkit-tap-highlight-color: transparent; }
        .form-article .btn-submit:hover { background: var(--teal-dk); }
        .msg { padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .msg.success { background: #d4edda; border: 1px solid #a3d9a5; color: #155724; }
        .msg.error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .site-footer { background: var(--teal-dk); color: rgba(255,255,255,.6); text-align: center; padding: 1.25rem; font-size: .88rem; }
    </style>
</head>
<body>

<header class="site-header">
    <nav class="nav-main">
        <a href="ecrivain.php" class="nav-ecrivain">Espace écrivain</a>
        <a href="../index.php" class="logo">Histoire des Comores</a>
        <div class="nav-menu">
            <ul class="nav-center">
                <li><a href="../index.php#accueil">Accueil</a></li>
            </ul>
            <ul class="nav-right">
                <li><a href="logout.php">Déconnexion</a></li>
            </ul>
        </div>
        <button class="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>
</header>

<div class="page-hero">
    <h1>Espace écrivain</h1>
    <p>Bienvenue, <?php echo htmlspecialchars($_SESSION['prenomUser'] ?? $_SESSION['nomUser'] ?? ''); ?>.</p>
</div>

<main>
    <div class="container">
        <?php if ($message !== ''): ?>
            <p class="msg <?php echo htmlspecialchars($messageType); ?>"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <div class="ecrivain-card">
            <h2>Rédiger un article</h2>
            <p>Remplissez le formulaire ci-dessous. La date sera enregistrée automatiquement.</p>
            <form method="post" action="" class="form-article" enctype="multipart/form-data">
                <div>
                    <label for="titreArticle">Titre</label>
                    <input type="text" id="titreArticle" name="titreArticle" maxlength="50" required placeholder="Titre de l'article" value="<?php echo htmlspecialchars($_POST['titreArticle'] ?? ''); ?>">
                </div>
                <div>
                    <label for="posterArticle">Image (poster)</label>
                    <input type="file" id="posterArticle" name="posterArticle" accept="image/jpeg,image/png,image/gif,image/webp">
                    <span class="form-hint">Optionnel. JPEG, PNG, GIF ou WebP.</span>
                </div>
                <div>
                    <label for="contenuArticle">Contenu</label>
                    <?php if (isset($_POST['contenuArticle']) && $_POST['contenuArticle'] !== ''): ?>
                    <div id="contenuArticleInitial" style="display:none"><?php echo preg_replace('/<\/?script\b[^>]*>/i', '', $_POST['contenuArticle']); ?></div>
                    <?php endif; ?>
                    <div class="editor-wrap" id="editorWrap">
                        <div id="editor" style="min-height: 280px;"></div>
                    </div>
                    <input type="hidden" name="contenuArticle" id="contenuArticleInput">
                </div>
                <div>
                    <label for="idTheme">Thème</label>
                    <select id="idTheme" name="idTheme" required>
                        <option value="">— Choisir un thème —</option>
                        <?php foreach ($themes as $t): ?>
                            <option value="<?php echo (int) $t->getIdTheme(); ?>" <?php echo (isset($_POST['idTheme']) && (int)$_POST['idTheme'] === $t->getIdTheme()) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t->getLibTheme()); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Publier l'article</button>
            </form>
        </div>
    </div>
</main>

<footer class="site-footer">
    <p>Histoire des Comores — Rédaction personnelle.</p>
</footer>

<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="../js/main.js"></script>
<script>
(function() {
    var editorEl = document.getElementById('editor');
    var hiddenInput = document.getElementById('contenuArticleInput');
    if (!editorEl || !hiddenInput) return;
    var quill = new Quill('#editor', {
        theme: 'snow',
        placeholder: 'Écrivez votre article ici...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'header': [1, 2, false] }],
                [{ 'color': [] }, { 'background': [] }],
                ['clean']
            ]
        }
    });
    var initEl = document.getElementById('contenuArticleInitial');
    if (initEl && initEl.innerHTML.trim()) quill.root.innerHTML = initEl.innerHTML;
    document.querySelector('.form-article').addEventListener('submit', function() {
        var html = quill.root.innerHTML;
        if (html === '<p><br></p>') html = '';
        hiddenInput.value = html;
    });
})();
</script>
</body>
</html>
