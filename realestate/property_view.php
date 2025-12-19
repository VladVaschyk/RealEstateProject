<?php require 'includes/config.php'; require 'includes/header.php';
$id = intval($_GET['id'] ?? 0);
$item = $pdo->prepare('SELECT * FROM property WHERE id=:id'); $item->execute([':id'=>$id]); $i = $item->fetch();
if(!$i){ echo '<div class="card">Об\'єкт не знайдено</div>'; require 'includes/footer.php'; exit; }


$priceInfo = calculateDiscountedPrice($i);
$displayPrice = $priceInfo['final_price'];
$isDiscounted = $priceInfo['is_discounted'];

$imgs = $pdo->prepare('SELECT image FROM property_images WHERE property_id=:pid ORDER BY id'); $imgs->execute([':pid'=>$id]); $imgs = $imgs->fetchAll(PDO::FETCH_COLUMN);
?>
<h1><?= htmlspecialchars($i['title']) ?></h1>
<div class="view-wrap">
  <div>
    <div class="gallery-main"><img id="mainImg" src="uploads/<?= htmlspecialchars($imgs[0] ?? 'default.jpg') ?>"></div>
    <div class="gallery-thumbs">
      <?php foreach($imgs as $im): ?>
        <img src="uploads/<?= htmlspecialchars($im) ?>" onclick="document.getElementById('mainImg').src=this.src" >
      <?php endforeach; ?>
    </div>
    <h2>Опис</h2>
    <div class="card">
        <p><?= nl2br(htmlspecialchars($i['description'])) ?></p>
    </div>
  </div>
  <aside class="info-card">
    <h3>Інформація про об'єкт</h3>
    
    <?php if ($isDiscounted): ?>
      <p style="text-decoration: line-through; color: var(--muted); margin-bottom: 2px;">
        Оригінальна ціна: $<?= number_format($priceInfo['original_price']) ?>
      </p>
      <p style="color: red; font-weight: bold;">
        Ціна зі знижкою:
        <span class="price" style="color: red; font-size: 1.2em;">$<?= number_format($displayPrice) ?></span>
      </p>
    <?php else: ?>
      <p><strong>Ціна:</strong> <span class="price">$<?= number_format($displayPrice) ?></span></p>
    <?php endif; ?>
    <p><strong>Площа:</strong> <?= htmlspecialchars($i['area']) ?> м²</p>
    <p><strong>Кількість кімнат:</strong> <?= htmlspecialchars($i['rooms']) ?></p>
    <p><strong>Адреса:</strong> <?= htmlspecialchars($i['address']) ?></p>
    <p><strong>Статус:</strong> <?= htmlspecialchars($i['status'] ?? 'Активний') ?></p>
    
    <p><strong>Дата додавання:</strong> <?= date('Y-m-d', strtotime($i['created_at'])) ?></p>
    <p><a class="btn" href="callback.php?pid=<?= $i['id'] ?>" style="background: #007bff;">Перетелефонуйте мені</a></p>
    
    <?php if(isset($_SESSION['admin'])): ?>
      <p><a class="btn" href="property_edit.php?id=<?= $i['id'] ?>">Редагувати об'єкт</a></p>
    <?php endif; ?>
  </aside>
</div>
<?php require 'includes/footer.php'; ?>