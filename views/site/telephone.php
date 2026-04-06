<div class="adding">
    <h1>Телефоны</h1>
    <button type="button" id="showFormBtn" class="btn">Добавить телефон</button>
</div>

<div class="form-container" id="phoneForm" style="display: none;">
    <h4>Добавление телефона</h4>

    <form method="post" class="add-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="telephone_phone_number" placeholder="Номер телефона" required>

        <select name="telephone_room_id" id="room_select" required>
            <option value="">Выберите помещение</option>
            <?php foreach ($rooms as $room): ?>
                <option value="<?= $room->id ?>">
                    <?= htmlspecialchars($room->name) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Добавить телефон</button>
    </form>
</div>

<?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Номер телефона</th>
        <th>Помещение</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($telephones as $telephone) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($telephone->phone_number ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($telephone->room->name ?? '-') . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>

<script>
    document.getElementById('showFormBtn').onclick = function() {
        const form = document.getElementById('phoneForm');
        form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    };
</script>