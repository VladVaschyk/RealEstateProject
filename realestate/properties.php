<?php

$title='Об\'єкти';

require 'includes/config.php';
require 'includes/header.php';

$q = $_GET['q'] ?? '';
$price_min_input = $_GET['price_min'] ?? '';
$price_max_input = $_GET['price_max'] ?? '';
$area_min = $_GET['area_min'] ?? '';
$area_max = $_GET['area_max'] ?? '';
$sort = $_GET['sort'] ?? '';

$where = [];
$params = [];

if(!empty($q)){
    $where[] = '(title LIKE :q OR address LIKE :q)';
    $params[':q']='%'.$q.'%';
}

if(is_numeric($area_min)){ $where[]='area>=:amin'; $params[':amin']=(float)$area_min; }
if(is_numeric($area_max)){ $where[]='area<=:amax'; $params[':amax']=(float)$area_max; }

$whereSql = $where ? 'WHERE '.implode(' AND ',$where) : '';

$stmt = $pdo->prepare("SELECT p.*, (SELECT image FROM property_images WHERE property_id=p.id LIMIT 1) AS img FROM property p $whereSql");
$stmt->execute($params);
$props = $stmt->fetchAll() ?: [];


$price_min = is_numeric($price_min_input) ? (float)$price_min_input : null;
$price_max = is_numeric($price_max_input) ? (float)$price_max_input : null;

if (!empty($props)) {
    $filtered_props = array_map(function($p) {
        $priceInfo = calculateDiscountedPrice($p);
        $p['final_price'] = $priceInfo['final_price'];
        return $p;
    }, $props);

    if ($price_min !== null || $price_max !== null) {
        $filtered_props = array_filter($filtered_props, function($p) use ($price_min, $price_max) {
            $price = $p['final_price'];
            
            $min_ok = ($price_min === null || $price >= $price_min);
            $max_ok = ($price_max === null || $price <= $price_max);
            
            return $min_ok && $max_ok;
        });
    }

    usort($filtered_props, function($a, $b) use ($sort) {
        $priceA = $a['final_price'];
        $priceB = $b['final_price'];
        
        if ($sort === 'price_asc') {
            return $priceA <=> $priceB;
        } elseif ($sort === 'price_desc') {
            return $priceB <=> $priceA;
        } elseif ($sort === 'area_desc') {
            return $b['area'] <=> $a['area'];
        } else {
            return $b['id'] <=> $a['id'];
        }
    });
    $props = $filtered_props;
}
?>

<h1>Об'єкти</h1>

<form class="filters" method="get">
  <input name="q" placeholder="Пошук (адреса, назва)" value="<?= htmlspecialchars($q) ?>">
  <input name="price_min" placeholder="Ціна від" type="number" step="1" value="<?= htmlspecialchars($price_min_input) ?>">
  <input name="price_max" placeholder="Ціна до" type="number" step="1" value="<?= htmlspecialchars($price_max_input) ?>">
  <input name="area_min" placeholder="Площа від" type="number" value="<?= htmlspecialchars($area_min) ?>">
  <input name="area_max" placeholder="Площа до" type="number" value="<?= htmlspecialchars($area_max) ?>">
  
  <select name="sort">
    <option value="">Сортування</option>
    <option value="price_asc" <?= $sort=='price_asc'?'selected':'' ?>>Ціна ↑</option>
    <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>Ціна ↓</option>
    <option value="area_desc" <?= $sort=='area_desc'?'selected':'' ?>>Площа ↓</option>
  </select>
  <button class="btn" type="submit">Застосувати</button>
  <a class="btn secondary" href="properties.php">Скинути</a>
</form>

<div class="grid cards">
<?php if(!$props): ?>
<p>Об'єктів не знайдено.</p>
<?php endif; ?>

<?php foreach($props as $p):
    $img = $p['img'] ? 'uploads/'.htmlspecialchars($p['img']) : 'uploads/default.jpg';
    
    $priceInfo = calculateDiscountedPrice($p);
    $displayPrice = $priceInfo['final_price'];
    $isDiscounted = $priceInfo['is_discounted'];
?>
<a class="card" href="property_view.php?id=<?= $p['id'] ?>">
    <div class="thumb"><img src="<?= $img ?>" alt=""></div>
    <div class="card-body">
    <h3><?= htmlspecialchars($p['title']) ?></h3>
    <div class="muted"><?= htmlspecialchars($p['address']) ?></div>

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