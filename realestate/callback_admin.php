<?php

$title='Заявки на дзвінок';

require 'includes/config.php';

if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    
    $pdo->prepare("DELETE FROM callback_requests WHERE id=:id")->execute([':id' => $id]);
    
    header('Location: callback_admin.php');
    exit;
}

$sort = $_GET['sort'] ?? 'id_desc';

$orderBy = match ($sort) {
    'name_asc' => 'r.client_name ASC',
    'time_desc' => 'r.request_time DESC',
    default => 'r.id DESC',
};

$requests = $pdo->query("SELECT r.*, p.title AS property_title
            FROM callback_requests r
            LEFT JOIN property p ON r.property_id = p.id
            ORDER BY $orderBy")->fetchAll();

require 'includes/header.php';
?>

<h1>Заявки на зворотній дзвінок</h1>

<?php if(!$requests): ?>
    <div class="card">Заявок на зворотній дзвінок немає.</div>
<?php else: ?>
    <table class="admin-table">
        <tr>
            <th>ID</th>
            <th>Ім'я</th>
            <th>Телефон</th>
            <th>Об'єкт (PID)
                <a href="?sort=name_asc">▲</a>
            </th>
            <th>Час заявки
                <a href="?sort=time_desc">▼</a>
            </th>
            <th>Дії</th>
        </tr>
        <?php foreach($requests as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td><?= htmlspecialchars($r['client_name']) ?></td>
                <td><?= htmlspecialchars($r['client_phone']) ?></td>
                
                <td>
                    <?php if($r['property_id']): ?>
                        <a href="property_view.php?id=<?= $r['property_id'] ?>" target="_blank">
                            <?= htmlspecialchars($r['property_title'] ?? 'Об\'єкт не знайдено') ?> (<?= $r['property_id'] ?>)
                        </a>
                    <?php else: ?>
                        Загальна заявка
                    <?php endif; ?>
                </td>
                
                <td><?= htmlspecialchars($r['request_time']) ?></td>
                
                <td>
                    <a href="?del=<?= $r['id'] ?>" onclick="return confirm('Видалити цю заявку?')">Видалити</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php
require 'includes/footer.php';
?>