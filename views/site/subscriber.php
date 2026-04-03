<div class="container">
    <div class="container-inside">
        <h3>Абоненты по подразделениям:</h3>
        <ul>
            <?php foreach ($subdivisions as $sub): ?>
                <li>
                    <?= $sub->name ?>: <?= $counts[$sub->id] ?> абонентов
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="container-inside">
        <h3>Абоненты по помещениям:</h3>
        <ul>
            <?php foreach ($rooms as $room): ?>
                <li>
                    <?= $room->name ?>: <?= $roomCounts[$room->id] ?> абонентов
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

</div>

<h1>Абоненты</h1>
<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Имя</th>
        <th>Фамилия</th>
        <th>Отчество</th>
        <th>Дата рождения</th>
    </tr>
    </thead>

    <tbody>
    <?php
    foreach ($subscribers as $subscriber) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($subscriber->name ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($subscriber->surname ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($subscriber->patronymic ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($subscriber->date_of_birth ?? '-') . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>