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
    foreach ($telephone as $telephone) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($telephone->phone_number) . '</td>';
        echo '<td>' . htmlspecialchars($telephone->room) . '</td>';
        echo '<td>' . htmlspecialchars($telephone->user->name) . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>