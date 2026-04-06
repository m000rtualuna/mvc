<div class="adding">
    <h1>Помещения</h1>
    <button type="button" id="showFormBtn" class="btn">Добавить помещение</button>
</div>

<div class="form-container" id="justForm" style="display: none;">
    <h4>Добавление помещения</h4>
    <form method="post" class="add-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="room_name" placeholder="Название помещения" required>
        <input type="text" name="room_number_room" placeholder="Номер помещения" required>
        <input type="text" name="room_type" placeholder="Тип помещения" required>
        <select name="room_subdivision_id" id="subdivision_select" required>
            <option value="">Выберите подразделение</option>
            <?php foreach ($subdivisions as $subdivision): ?>
                <option value="<?= $subdivision->id ?>">
                    <?= htmlspecialchars($subdivision->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Добавить помещение</button>
    </form>
</div>

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

<script>
    document.getElementById('showFormBtn').onclick = function() {
        const form = document.getElementById('justForm');
        form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    };
</script>
