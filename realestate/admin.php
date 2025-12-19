<?php

require 'includes/config.php';


if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

require 'includes/header.php';

if(isset($_GET['del'])){
    $id = intval($_GET['del']);
    
    $pdo->prepare('DELETE FROM property WHERE id=:id')->execute([':id'=>$id]);
    
    header('Location: admin.php');
    exit;
}

$sort = $_GET['sort'] ?? 'id_desc';

$orderBy = match ($sort) {
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC',
    'area_desc' => 'area DESC',
    default => 'id DESC',
};

$props = $pdo->query("SELECT * FROM property ORDER BY $orderBy")->fetchAll();
?>

<h1>Керування об'єктами (CRUD)</h1>
<p><a class="btn" href="property_add.php">Додати новий об'єкт</a></p>
<table class="admin-table">
  <tr>
    <th>ID</th>
    <th>Назва</th>
    <th>
        Ціна
        <a href="?sort=price_asc">▲</a>
        <a href="?sort=price_desc">▼</a>
    </th>
    <th>
        Площа
        <a href="?sort=area_desc">▼</a>
    </th>
    <th>Дії</th>
  </tr>
  
  <?php foreach($props as $p): ?>
    <tr>
      <td><?= $p['id'] ?></td>
      <td><?= htmlspecialchars($p['title']) ?></td>
      <td>$<?= number_format($p['price']) ?></td>
      <td><?= htmlspecialchars($p['area']) ?> м²</td>
      
      <td>
        <a href="property_view.php?id=<?= $p['id'] ?>">Переглянути</a> |
        <a href="property_edit.php?id=<?= $p['id'] ?>">Редагувати</a> |
        <a href="admin.php?del=<?= $p['id'] ?>" onclick="return confirm('Видалити?')">Видалити</a>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<?php
require 'includes/footer.php';
?>