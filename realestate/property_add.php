<?php require 'includes/config.php'; if(!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
if(isset($_POST['save'])){
  $price = (int)$_POST['price'];
  $date = $_POST['created_at'];

  $stmt = $pdo->prepare('INSERT INTO property (title,price,original_price,rooms,area,address,description,created_at) VALUES (:title,:price,:orig_price,:rooms,:area,:address,:description,:created_at)');
  $stmt->execute([
    ':title'=>$_POST['title'],
    ':price'=>$price,
    ':orig_price'=>$price,
    ':rooms'=>$_POST['rooms'],
    ':area'=>$_POST['area'],
    ':address'=>$_POST['address'],
    ':description'=>$_POST['description'],
    ':created_at'=>$date
  ]);
  $id = $pdo->lastInsertId();
  if(!empty($_FILES['imgs']['name'][0])){
    for($i=0;$i<count($_FILES['imgs']['name']);$i++){
      if($_FILES['imgs']['error'][$i]!==UPLOAD_ERR_OK) continue;
      $orig = basename($_FILES['imgs']['name'][$i]);
      $safe = time()."_".preg_replace('/[^A-Za-z0-9_.-]/','_',$orig);
      move_uploaded_file($_FILES['imgs']['tmp_name'][$i], __DIR__.'/uploads/'.$safe);
      $pdo->prepare('INSERT INTO property_images (property_id,image) VALUES (:pid,:img)')->execute([':pid'=>$id,':img'=>$safe]);
    }
  }
  header('Location: properties.php'); exit;
}
require 'includes/header.php'; ?>
<h1>Додати об'єкт</h1>
<form method="post" enctype="multipart/form-data" class="card">
  <label>Назва<input name="title" required></label>
  <label>Ціна $<input name="price" type="number" required></label>
  <label>Дата додавання<input name="created_at" type="date" value="<?= date('Y-m-d') ?>" required></label>
  
  <label>Кількість кімнат<input name="rooms" type="number"></label>
  <label>Площа<input name="area" type="number"></label>
  <label>Адреса<input name="address"></label>
  <label>Опис<textarea name="description"></textarea></label>
  <label>Фото<input type="file" name="imgs[]" multiple accept="image/*"></label>
  <button class="btn" name="save">Зберегти</button>
</form>
<?php require 'includes/footer.php'; ?>