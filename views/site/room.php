<div class="form-container">
    <form method="post" class="add-form">
        <input type="text" name="room_name" placeholder="Название подразделения" required>
        <input type="text" name="room_number_room" placeholder="Тип подразделения" required>
        <input type="text" name="room_type" placeholder="Тип подразделения" required>
        <select name="room_subdivision_id" id="subdivision_select" required>
            <option value="">Выберите подразделение</option>
            <?php foreach ($subdivisions as $subdivision): ?>
                <option value="<?= $subdivision->id ?>">
                    <?= htmlspecialchars($subdivision->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Добавить подразделение</button>
    </form>
</div>

<h1>Помещения</h1>
<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Наименование помещения</th>
        <th>Номер помещения</th>
        <th>Тип помещения</th>
    </tr>
    </thead>

    <tbody>
    <?php
    foreach ($rooms as $room) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($room->name) . '</td>';
        echo '<td>' . htmlspecialchars($room->number_room) . '</td>';
        echo '<td>' . htmlspecialchars($room->type) . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>