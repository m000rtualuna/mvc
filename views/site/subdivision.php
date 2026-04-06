<div class="adding">
    <h1>Подразделения</h1>
    <button type="button" id="showFormBtn" class="btn">Добавить подразделение</button>
</div>

<div class="form-container" id="justForm" style="display: none;">
    <h3>Добавление подразделения</h3>
    <form method="post" class="add-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" name="subdivision_name" placeholder="Название подразделения">
        <input type="text" name="subdivision_type" placeholder="Тип подразделения">
        <button type="submit" class="btn btn-primary">Добавить подразделение</button>
    </form>
</div>


<?php if ($message): ?>
    <p><?= $message ?></p>
<?php endif; ?>

<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Наименование подразделения</th>
        <th>Тип подразделения</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($subdivisions as $subdivision) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($subdivision->name ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($subdivision->type ?? '-') . '</td>';
        echo '</tr>';
    } ?>
    </tbody>
</table>

<script>
    document.getElementById('showFormBtn').onclick = function() {
        const form = document.getElementById('justForm');
        form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    };
</script>
