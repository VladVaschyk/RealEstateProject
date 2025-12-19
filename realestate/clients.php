<?php

require 'includes/config.php';
$action = $_GET['action'] ?? 'view';
$id = intval($_GET['id'] ?? 0);


if ($action === 'delete') {
    $pdo->prepare("DELETE FROM clients WHERE id=:id")->execute([':id' => $id]);
    header('Location: clients.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO clients (surname,name,phone) VALUES (:surname, :name, :phone)");
        $stmt->execute([':surname' => $_POST['surname'], ':name' => $_POST['name'], ':phone' => $_POST['phone']]);
        
    } elseif ($action === 'edit' && $id) {
        $stmt = $pdo->prepare('UPDATE clients SET surname=:surname, name=:name, phone=:phone WHERE id=:id');
        $stmt->execute([':surname'=>$_POST['surname'], ':name'=>$_POST['name'], ':phone'=>$_POST['phone'], ':id'=>$id]);
    }
    
    header('Location: clients.php');
    exit;
}

require 'includes/header.php';

if ($action === 'add' || ($action === 'edit' && $id)) {
    $item = null;
    $h1 = 'Додати клієнта';
    
    if ($action === 'edit' && $id) {
        $item = $pdo->prepare('SELECT * FROM clients WHERE id=:id');
        $item->execute([':id'=>$id]);
        $item = $item->fetch();
        $h1 = 'Редагувати клієнта';
    }
    ?>
    <h1><?= $h1 ?></h1>
    <form method="post" class="card">
        <label>Прізвище<input name="surname" value="<?= htmlspecialchars($item['surname'] ?? '') ?>" required></label>
        <label>Ім'я<input name="name" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required></label>
        <label>Телефон<input name="phone" value="<?= htmlspecialchars($item['phone'] ?? '') ?>"></label>
        <button class="btn" name="save">Зберегти</button>
        <p><a href="clients.php">Назад до списку</a></p>
    </form>
    <?php
} else {
    
    $sort = $_GET['sort'] ?? 'id_desc';
    
    $orderBy = match ($sort) {
        'surname_asc' => 'surname ASC',
        'surname_desc' => 'surname DESC',
        default => 'id DESC',
    };
    
    $res = $pdo->query("SELECT * FROM clients ORDER BY $orderBy")->fetchAll();
    ?>
    
    <h1>Клієнти (CRUD)</h1>
    <p><a class="btn" href="?action=add">Додати нового клієнта</a></p>
    
    <table class="admin-table">
        <tr>
            <th>ID</th>
            <th>Прізвище
                <a href="?sort=surname_asc">▲</a>
                <a href="?sort=surname_desc">▼</a>
            </th>
            <th>Ім'я</th>
            <th>Телефон</th>
            <th>Дії</th>
        </tr>
        <?php foreach($res as $r): ?>
            <tr>
                <td><?php echo $r['id'];?></td>
                <td><?php echo htmlspecialchars($r['surname']);?></td>
                <td><?php echo htmlspecialchars($r['name']);?></td>
                <td><?php echo htmlspecialchars($r['phone']);?></td>
                
                <td>
                    <a href="?action=edit&id=<?= $r['id'] ?>">Редагувати</a>
                    <a href="?action=delete&id=<?php echo $r['id'];?>" onclick="return confirm('Видалити?')">Видалити</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <?php
}

require 'includes/footer.php';
?>