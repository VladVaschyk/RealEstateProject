<?php if(!isset($title)) $title='Агентство нерухомості'; ?>
<!doctype html>
<html lang="uk">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <div class="brand"><a href="index.php">D-22 REALTY</a></div>
    <nav class="main-nav">
      <a href="index.php">Головна</a>
      <a href="properties.php">Об'єкти</a>
      
      <?php if(isset($_SESSION['admin'])): ?>
        <a href="admin.php">Об'єкти (CRUD)</a>
        <a href="clients.php">Клієнти (CRUD)</a>
        <a href="workers.php">Працівники (CRUD)</a>
        <a href="deals.php">Угоди (CRUD)</a>
        <a href="callback_admin.php">Заявки (Адмін)</a>
        <a href="logout.php">Вихід</a>
      <?php else: ?>
        <a href="login.php">Увійти</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">