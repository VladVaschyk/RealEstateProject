<?php
require 'includes/config.php';

if(!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$id = intval($_GET['id'] ?? 0);
if(!$id){ header('Location: properties.php'); exit; }


if(isset($_POST['save'])){
  
  $price = (int)$_POST['price'];
  $originalPrice = (int)$_POST['original_price'] ?? $price;
  $date = $_POST['created_at'];
  
  if ($originalPrice !== (int)$_POST['old_price']) {
      $originalPrice = $price;
  }
  
  $pdo->prepare('UPDATE property SET title=:title,price=:price,original_price=:orig_price,rooms=:rooms,area=:area,address=:address,description=:description,created_at=:created_at WHERE id=:id')
    ->execute([
      ':title'=>$_POST['title'],
      ':price'=>$price,
      ':orig_price'=>$originalPrice,
      ':rooms'=>$_POST['rooms'],
      ':area'=>$_POST['area'],
      ':address'=>$_POST['address'],
      ':description'=>$_POST['description'],
      ':created_at'=>$date,
      ':id'=>$id
    ]);
    
  if(!empty($_FILES['imgs']['name'][0])){
    for($i=0;$i<count($_FILES['imgs']['name']);$i++){
      if($_FILES['imgs']['error'][$i]!==UPLOAD_ERR_OK) continue;
      
      $orig = basename($_FILES['imgs']['name'][$i]);
      $safe = time()."_".preg_replace('/[^A-Za-z0-9_.-]/','_',$orig);
      
      move_uploaded_file($_FILES['imgs']['tmp_name'][$i], __DIR__.'/uploads/'.$safe);
      
      $pdo->prepare('INSERT INTO property_images (property_id,image) VALUES (:pid,:img)')->execute([':pid'=>$id,':img'=>$safe]);
    }
  }
  header('Location: property_view.php?id='.$id); exit;
}


if(isset($_GET['delimg'])){
  $imgId = intval($_GET['delimg']);
  
  $row = $pdo->query('SELECT image FROM property_images WHERE id='.(int)$imgId)->fetch();
  
  if($row){
    @unlink(__DIR__.'/uploads/'.$row['image']);
    
    $pdo->prepare('DELETE FROM property_images WHERE id=:id')->execute([':id'=>$imgId]);
  }
  header('Location: property_edit.php?id='.$id); exit;
}


$item = $pdo->prepare('SELECT * FROM property WHERE id=:id');
$item->execute([':id'=>$id]);
$i=$item->fetch();

$imgs = $pdo->prepare('SELECT * FROM property_images WHERE property_id=:pid');
$imgs->execute([':pid'=>$id]);
$imgs=$imgs->fetchAll();

require 'includes/header.php';
?>
<h1 class="section-title">Редагувати об'єкт</h1>
<form method="post" enctype="multipart/form-data" class="crud-form">
  
  <label>Назва<input name="title" value="<?= htmlspecialchars($i['title']) ?>"></label>
  <label>Ціна $<input name="price" type="number" value="<?= htmlspecialchars($i['price']) ?>"></label>
  <input type="hidden" name="original_price" value="<?= htmlspecialchars($i['original_price'] ?? $i['price']) ?>">
  <input type="hidden" name="old_price" value="<?= htmlspecialchars($i['price']) ?>">
  
  <label>Дата додавання<input name="created_at" type="date" value="<?= htmlspecialchars($i['created_at']) ?>" required></label>

  <label>Кімнат<input name="rooms" type="number" value="<?= htmlspecialchars($i['rooms']) ?>"></label>
  <label>Площа<input name="area" type="number" value="<?= htmlspecialchars($i['area']) ?>"></label>
  <label>Адреса<input name="address" value="<?= htmlspecialchars($i['address']) ?>"></label>
  <label>Опис<textarea name="description"><?= htmlspecialchars($i['description']) ?></textarea></label>
  
  <div>Існуючі фото:</div>
  <div class="thumbs" style="display:flex; gap: 10px; flex-wrap: wrap;">
    <?php foreach($imgs as $im): ?>
      <div class="t" style="display:flex; flex-direction:column; align-items:center;">
        <img src="uploads/<?= htmlspecialchars($im['image']) ?>" style="width:100px; height:70px; object-fit:cover; border-radius: 4px; margin-bottom: 5px;">
        <a href="?delimg=<?= $im['id'] ?>&id=<?= $id ?>" onclick="return confirm('Видалити це фото?')">Видалити</a>
      </div>
    <?php endforeach; ?>
  </div>
  
  <label>Додати фото<input type="file" name="imgs[]" multiple accept="image/*"></label>
  <div class="crud-actions"><button class="btn" name="save">Зберегти</button></div>
</form>
<?php require 'includes/footer.php'; ?>