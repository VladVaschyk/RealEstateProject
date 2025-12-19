<?php
$title='Головна';
require 'includes/config.php';
require 'includes/header.php';
$objects = $pdo->query("SELECT p.*, (SELECT image FROM property_images WHERE property_id=p.id LIMIT 1) AS img FROM property p ORDER BY p.id DESC LIMIT 8")->fetchAll();
?>
<section class="hero">
  <h1>Елітна нерухомість. Надійні інвестиції.</h1>
  <p>Знайдіть свій ідеальний об'єкт з D-22 Realty.</p>
  <a class="btn" href="properties.php">Переглянути об'єкти</a>
  <a class="btn secondary" href="callback.php" style="margin-left: 10px;">Замовити дзвінок</a>
</section>

<h2 class="section-title">Останні об'єкти</h2>
<div class="grid cards">
<?php foreach($objects as $o):
  $img = $o['img'] ? 'uploads/'.htmlspecialchars($o['img']) : 'uploads/default.jpg';
  
  $priceInfo = calculateDiscountedPrice($o);
  $displayPrice = $priceInfo['final_price'];
  $isDiscounted = $priceInfo['is_discounted'];
?>
  <a class="card" href="property_view.php?id=<?= $o['id'] ?>">
    <div class="thumb"><img src="<?= $img ?>" alt=""></div>
    <div class="card-body">
      <h3><?= htmlspecialchars($o['title']) ?></h3>
      <div class="muted"><?= htmlspecialchars($o['address']) ?></div>
      
      <div class="price-display">
      <?php if ($isDiscounted): ?>
          <div style="text-decoration: line-through; color: var(--muted); font-size: 12px;">
              $<?= number_format($priceInfo['original_price']) ?>
          </div>
          <div class="price" style="color: red;">$<?= number_format($displayPrice) ?></div>
      <?php else: ?>
          <div class="price">$<?= number_format($displayPrice) ?></div>
      <?php endif; ?>
      </div>
      </div>
  </a>
<?php endforeach; ?>
</div>
<?php require 'includes/footer.php'; ?>