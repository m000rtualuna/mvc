<h1>Абоненты</h1>
<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Фамилия</th>
        <th>Отчество</th>
        <th>Дата рождения</th>
    </tr>
    </thead>

    <tbody>
    <?php
    foreach ($subscribers as $subscriber) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($subscriber->surname ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($subscriber->patronymic ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($subscriber->date_of_birth ?? '-') . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>