<h1>Пользователи системы</h1>
<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Логин пользователя</th>
        <th>Роль пользователя</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user->login ?? '-'); ?></td>
            <td>
                <form method="POST" action="/users">
                    <input type="hidden" name="user_id" value="<?php echo $user->id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <select name="role_id" onchange="this.form.submit()" style="width: 100%;">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role->id); ?>"
                                    <?php
                                    $currentRoleId = $user->role_id ?? null;
                                    if ($currentRoleId === $role->id): ?> selected<?php endif; ?>>
                                <?php echo htmlspecialchars($role->id); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>