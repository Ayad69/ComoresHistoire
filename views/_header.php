<?php
require_once __DIR__ . '/../config/init_session.php';
if (!isset($baseUrl)) $baseUrl = '../';
if (!isset($pageTitle)) $pageTitle = 'Histoire des Comores';
$userConnected = !empty($_SESSION['idUser']);
$isEcrivain = $userConnected && !empty($_SESSION['ecrivain']);
$navRegister = ($baseUrl === '../') ? 'register.php' : $baseUrl . 'views/register.php';
$navLogin    = ($baseUrl === '../') ? 'login.php'    : $baseUrl . 'views/login.php';
$navLogout   = ($baseUrl === '../') ? 'logout.php'   : $baseUrl . 'views/logout.php';
$navEcrivain = ($baseUrl === '../') ? 'ecrivain.php' : $baseUrl . 'views/ecrivain.php';
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
  <link rel="stylesheet" href="<?php echo htmlspecialchars($baseUrl); ?>css/style.css">
</head>
<body>
  <header class="site-header">
    <nav class="nav-main">
      <?php if ($isEcrivain): ?>
        <a href="<?php echo htmlspecialchars($navEcrivain); ?>" class="nav-ecrivain">Espace écrivain</a>
      <?php endif; ?>
      <a href="<?php echo htmlspecialchars($baseUrl); ?>index.php" class="logo">Histoire des Comores</a>
      <div class="nav-menu">
        <ul class="nav-center">
          <li><a href="<?php echo htmlspecialchars($baseUrl); ?>index.php">Accueil</a></li>
          <?php if ($userConnected): ?>
            <li class="nav-bonjour">Bonjour, <?php echo htmlspecialchars($_SESSION['nomUser'] ?? ''); ?> !</li>
          <?php endif; ?>
        </ul>
        <ul class="nav-right">
          <?php if ($userConnected): ?>
            <li><a href="<?php echo htmlspecialchars($navLogout); ?>">Déconnexion</a></li>
          <?php else: ?>
            <li><a href="<?php echo htmlspecialchars($navRegister); ?>">Inscription</a></li>
            <li><a href="<?php echo htmlspecialchars($navLogin); ?>">Se connecter</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <button class="nav-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </nav>
  </header>
