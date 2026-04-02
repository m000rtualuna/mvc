<h1>Пользователи системы</h1>
<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Имя пользователя</th>
        <th>Логин пользователя</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($users as $user) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($user->name ?? '-') . '</td>';
        echo '<td>' . htmlspecialchars($user->login ?? '-') . '</td>';
        echo '</tr>';
    }
    ?>
    </tbody>
</table>