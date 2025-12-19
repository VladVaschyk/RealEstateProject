<?php

require 'includes/config.php';

$action = $_GET['action'] ?? 'view';
$id = intval($_GET['id'] ?? 0);


$props = $pdo->query("SELECT id, title, address FROM property")->fetchAll();
$clients = $pdo->query("SELECT id, surname, name FROM clients")->fetchAll();
$workers = $pdo->query("SELECT id, surname, name FROM workers")->fetchAll();


if ($action === 'delete') {
    $pdo->prepare("DELETE FROM deals WHERE id=:id")->execute([':id' => $id]);
    header('Location: deals.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = [
        ':date' => $_POST['date'],
        ':type' => $_POST['type'],
        ':amount' => floatval($_POST['amount']),
        ':commission' => floatval($_POST['commission']),
        ':property_id' => intval($_POST['property_id']),
        ':buyer_id' => intval($_POST['buyer_id']),
        ':worker_id' => intval($_POST['worker_id'])
    ];

        if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO deals (date_signed,type,amount,agency_commission,property_id,buyer_id,worker_id)
            VALUES (:date,:type,:amount,:commission,:property_id,:buyer_id,:worker_id)");
        $stmt->execute($data);
        
    } elseif ($action === 'edit' && $id) {
        $data[':id'] = $id;
        $stmt = $pdo->prepare('UPDATE deals SET date_signed=:date, type=:type, amount=:amount, agency_commission=:commission, property_id=:property_id, buyer_id=:buyer_id, worker_id=:worker_id WHERE id=:id');
        $stmt->execute($data);
    }
    
    header('Location: deals.php');
    exit;
}

require 'includes/header.php';


if ($action === 'add' || ($action === 'edit' && $id)) {
    $item = null;
    $h1 = 'Додати угоду';
    
    if ($action === 'edit' && $id) {
        $item = $pdo->prepare('SELECT * FROM deals WHERE id=:id'); $item->execute([':id'=>$id]); $item = $item->fetch();
        $h1 = 'Редагувати угоду';
    }
    ?>
    <h1 class="section-title"><?= $h1 ?></h1>
    <form method="post" class="crud-form">
        <label>Дата підписання<input type="date" name="date" value="<?= htmlspecialchars($item['date_signed'] ?? date('Y-m-d')) ?>" required></label>
        
        <label>Тип угоди
            <select name="type">
                <option <?= ($item['type'] ?? '')=='Продаж'?'selected':'' ?>>Продаж</option>
                <option <?= ($item['type'] ?? '')=='Оренда'?'selected':'' ?>>Оренда</option>
            </select>
        </label>
        
        <label>Сума<input name="amount" value="<?= htmlspecialchars($item['amount'] ?? '') ?>" required></label>
        <label>Комісія агентства<input name="commission" value="<?= htmlspecialchars($item['agency_commission'] ?? '') ?>"></label>
        
        <label>Об'єкт
            <select name="property_id">
                <?php foreach($props as $p): ?>
                    <option value="<?php echo $p['id'];?>" <?= ($item['property_id'] ?? 0)==$p['id']?'selected':'' ?>><?php echo htmlspecialchars($p['title'].' ('.$p['address'].')');?></option>
                <?php endforeach; ?></select>
        </label>
        
        <label>Клієнт
            <select name="buyer_id"><option value="0">--клієнт--</option>
                <?php foreach($clients as $c): ?>
                    <option value="<?php echo $c['id'];?>" <?= ($item['buyer_id'] ?? 0)==$c['id']?'selected':'' ?>><?php echo htmlspecialchars($c['surname'].' '.$c['name']);?></option>
                <?php endforeach; ?></select>
        </label>
        
        <label>Працівник
            <select name="worker_id">
                <?php foreach($workers as $w): ?>
                    <option value="<?php echo $w['id'];?>" <?= ($item['worker_id'] ?? 0)==$w['id']?'selected':'' ?>><?php echo htmlspecialchars($w['surname'].' '.$w['name']);?></option>
                <?php endforeach; ?></select>
        </label>
        
        <div class="crud-actions"><button class="btn" name="save">Зберегти</button><a class="btn secondary" href="deals.php">Скасувати</a></div>
    </form>
    <?php
    
} else {
    $sort = $_GET['sort'] ?? 'date_desc';
    $orderBy = match ($sort) {
        'date_asc' => 'd.date_signed ASC',
        'amount_desc' => 'd.amount DESC',
        'type_asc' => 'd.type ASC',
        default => 'd.date_signed DESC',
    };
    
    $res = $pdo->query("SELECT d.*, p.address, p.title, c.surname AS buyer_surname, w.surname AS worker_surname
            FROM deals d
            LEFT JOIN property p ON d.property_id=p.id
            LEFT JOIN clients c ON d.buyer_id=c.id
            LEFT JOIN workers w ON d.worker_id=w.id
            ORDER BY $orderBy")->fetchAll();
    ?>
    <h1 class="section-title">Угоди (CRUD)</h1>
    <p><a class="btn" href="?action=add">Додати нову угоду</a></p>
    
    <table class="admin-table">
        <tr>
            <th>ID</th>
            <th>Дата
                <a href="?sort=date_asc">▲</a>
                <a href="?sort=date_desc">▼</a>
            </th>
            <th>Тип</th>
            <th>Сума
                <a href="?sort=amount_desc">▼</a>
            </th>
            <th>Об'єкт</th>
            <th>Покупець</th>
            <th>Працівник</th>
            <th>Дії</th>
        </tr>
        <?php foreach($res as $r): ?>
            <tr>
                <td><?php echo $r['id'];?></td>
                <td><?php echo $r['date_signed'];?></td>
                <td><?php echo htmlspecialchars($r['type']);?></td>
                <td><?php echo $r['amount'];?></td>
                <td><?php echo htmlspecialchars($r['title'].' ('.$r['address'].')');?></td>
                <td><?php echo htmlspecialchars($r['buyer_surname']);?></td>
                <td><?php echo htmlspecialchars($r['worker_surname']);?></td>
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