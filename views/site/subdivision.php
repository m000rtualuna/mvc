<div class="form-container">
    <form method="post" class="add-form">
        <input type="text" name="subdivision_name" placeholder="Название подразделения" required>
        <input type="text" name="subdivision_type" placeholder="Тип подразделения" required>
        <button type="submit" class="btn btn-primary">Добавить подразделение</button>
    </form>
</div>


<h1>Подразделения</h1>
<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Наименование подразделения</th>
        <th>Тип подразделения</th>
    </tr>
    </thead>

    <tbody>
    <?php
    foreach ($subdivisions as $subdivision) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($subdivision->name ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($subdivision->type ?? '-') . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>