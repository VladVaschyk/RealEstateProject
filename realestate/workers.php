<?php
require 'includes/config.php';

$action = $_GET['action'] ?? 'view';
$id = intval($_GET['id'] ?? 0);


if ($action === 'delete') {
    $pdo->prepare("DELETE FROM workers WHERE id=:id")->execute([':id' => $id]);
    header('Location: workers.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        ':surname' => $_POST['surname'], ':name' => $_POST['name'], ':phone' => $_POST['phone'],
        ':position' => $_POST['position'], ':commission' => floatval($_POST['commission'])
    ];
    
    if ($action === 'add') {
        $stmt = $pdo->prepare("INSERT INTO workers (surname,name,phone,position,commission_percent) VALUES (:surname, :name, :phone, :position, :commission)");
        $stmt->execute($data);

    } elseif ($action === 'edit' && $id) {
        $data[':id'] = $id;
        $stmt = $pdo->prepare('UPDATE workers SET surname=:surname, name=:name, phone=:phone, position=:position, commission_percent=:commission WHERE id=:id');
        $stmt->execute($data);
    }
    header('Location: workers.php'); exit;
}


require 'includes/header.php';

if ($action === 'add' || ($action === 'edit' && $id)) {
    $item = null;
    $h1 = 'Додати працівника';
    
    if ($action === 'edit' && $id) {
        $item = $pdo->prepare('SELECT * FROM workers WHERE id=:id');
        $item->execute([':id'=>$id]);
        $item = $item->fetch();
        $h1 = 'Редагувати працівника';
    }
    ?>
    <h1><?= $h1 ?></h1>
    <form method="post" class="card">
        <label>Прізвище<input name="surname" value="<?= htmlspecialchars($item['surname'] ?? '') ?>" required></label>
        <label>Ім'я<input name="name" value="<?= htmlspecialchars($item['name'] ?? '') ?>" required></label>
        <label>Телефон<input name="phone" value="<?= htmlspecialchars($item['phone'] ?? '') ?>"></label>
        <label>Посада<input name="position" value="<?= htmlspecialchars($item['position'] ?? '') ?>"></label>
        <label>Відсоток комісії<input name="commission" type="number" step="0.01" value="<?= htmlspecialchars($item['commission_percent'] ?? '') ?>"></label>
        <button class="btn" name="save">Зберегти</button>
        <p><a href="workers.php">Назад до списку</a></p>
    </form>
    <?php
} else {

    $sort = $_GET['sort'] ?? 'id_desc';
    $orderBy = match ($sort) {
        'surname_asc' => 'surname ASC',
        'commission_desc' => 'commission_percent DESC',
        'position_asc' => 'position ASC',
        default => 'id DESC',
    };
    
    $res = $pdo->query("SELECT * FROM workers ORDER BY $orderBy")->fetchAll();
    ?>
    
    <h1>Працівники (CRUD)</h1>
    <p><a class="btn" href="?action=add">Додати нового працівника</a></p>
    
    <table class="admin-table">
        <tr>
            <th>ID</th>
            <th>ПІБ
                <a href="?sort=surname_asc">▲</a>
            </th>
            <th>Телефон</th>
            <th>Посада
                <a href="?sort=position_asc">▲</a>
            </th>
            <th>Комісія %
                <a href="?sort=commission_desc">▼</a>
            </th>
            <th>Дії</th>
        </tr>
        <?php foreach($res as $r): ?>
            <tr>
                <td><?php echo $r['id'];?></td>
                <td><?php echo htmlspecialchars($r['surname'].' '.$r['name']);?></td>
                <td><?php echo htmlspecialchars($r['phone']);?></td>
                <td><?php echo htmlspecialchars($r['position']);?></td>
                <td><?php echo htmlspecialchars($r['commission_percent']);?></td>
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
