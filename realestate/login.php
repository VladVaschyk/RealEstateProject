<?php
require 'includes/config.php';
if(isset($_POST['login'])){
  $u = $_POST['user'] ?? '';
  $p = $_POST['pass'] ?? '';
  if($u==='admin' && $p==='admin'){
    $_SESSION['admin'] = true;
    header('Location: admin.php'); exit;
  } else {
    $error = 'Невірний логін або пароль';
  }
}
require 'includes/header.php';
?>
<div class="card auth">
  <h2>Вхід</h2>
  <?php if(!empty($error)) echo '<div class="error">'.htmlspecialchars($error).'</div>'; ?>
  <form method="post">
    <label>Логін<input name="user" required placeholder="Введіть логін"></label>
    <label>Пароль<input name="pass" type="password" required placeholder="Введіть пароль"></label>
    <button class="btn" name="login">Увійти</button>
  </form>
</div>
<?php require 'includes/footer.php'; ?>