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
    foreach ($room as $room) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($room->name) . '</td>';
        echo '<td>' . htmlspecialchars($room->number_room) . '</td>';
        echo '<td>' . htmlspecialchars($room->type) . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>