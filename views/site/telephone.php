<?php
?>

<div class="form-container">
    <h2>Добавить телефон</h2>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="post" class="add-form">
        <input type="text" name="telephone_name" placeholder="Название телефона" required>
        <input type="text" name="telephone_phone_number" placeholder="Номер телефона" required>

        <select name="telephone_room_id" id="room_select" required>
            <option value="">Выберите помещение</option>
            <?php foreach ($rooms as $room): ?>
                <option value="<?= $room->id ?>">
                    <?= htmlspecialchars($room->name) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="telephone_subscriber_id" id="subscriber_select" required>
            <option value="">Выберите абонента</option>
            <?php foreach ($subscribers as $subscriber): ?>
                <option value="<?= $subscriber->id ?>">
                    <?= htmlspecialchars($subscriber->name) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Добавить телефон</button>
    </form>
</div>


<h1>Телефоны</h1>
<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Номер телефона</th>
        <th>Помещение</th>
        <th>Абонент</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($telephones as $telephone) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($telephone->phone_number ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($telephone->room->name ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($telephone->subscriber->name . $telephone->subscriber->surname ?? '-') . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>