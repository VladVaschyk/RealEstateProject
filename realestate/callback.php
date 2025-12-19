<?php

$title='Замовити дзвінок';

require 'includes/config.php';

$propertyId = intval($_GET['pid'] ?? 0);
$propertyName = '';

if ($propertyId > 0) {
    $stmt = $pdo->prepare("SELECT title FROM property WHERE id=:id");
    $stmt->execute([':id' => $propertyId]);
    $property = $stmt->fetch();
    
    $propertyName = $property ? ' для об\'єкта: ' . htmlspecialchars($property['title']) : '';
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    
    $name = trim($_POST['client_name'] ?? '');
    $phone = trim($_POST['client_phone'] ?? '');
    $pid = intval($_POST['property_id'] ?? 0);

    if (!empty($name) && !empty($phone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO callback_requests (client_name, client_phone, property_id) VALUES (:name, :phone, :pid)");
            $stmt->execute([':name' => $name, ':phone' => $phone, ':pid' => $pid]);
            
            header('Location: callback.php?status=success');
            exit;
            
        } catch (PDOException $e) {
            $error = "Помилка при збереженні: " . $e->getMessage();
        }
    } else {
        $error = "Будь ласка, заповніть усі обов'язкові поля.";
    }
}

require 'includes/header.php';
?>

<div class="card auth">
    <h1>Замовити зворотній дзвінок<?= $propertyName ?></h1>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
        <div class="success-message" style="color: var(--green); margin-bottom: 15px; font-weight: bold; text-align: center;">
            Ваша заявка успішно відправлена! Наш менеджер зв'яжеться з вами найближчим часом.
        </div>
        <p style="text-align: center;"><a class="btn secondary" href="index.php">Повернутися на головну</a></p>
    
    <?php else: ?>
        <?php if (!empty($error)) echo '<div class="error" style="color: red; margin-bottom: 15px; text-align: center;">'.htmlspecialchars($error).'</div>'; ?>
        
        <form method="post">
            <input type="hidden" name="property_id" value="<?= $propertyId ?>">
            
            <label>Введіть своє ПІБ<input name="client_name" value="<?= htmlspecialchars($_POST['client_name'] ?? '') ?>" required placeholder="Ваше ПІБ"></label>
            
            <label>Номер телефону<input name="client_phone" value="<?= htmlspecialchars($_POST['client_phone'] ?? '') ?>" required placeholder="+380..."></label>
            
            <button class="btn" name="save" type="submit">Перетелефонуйте мені</button>
        </form>
    <?php endif; ?>
</div>

<?php
require 'includes/footer.php';
?>