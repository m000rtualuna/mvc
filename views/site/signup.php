<div class="form-container">
    <h2>Регистрация нового пользователя</h2>

    <!-- Вывод общего сообщения -->
    <h3><?= $message ?? ''; ?></h3>
    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <div class="form-el">
            <label>Логин
                <input type="text" name="login" value="<?= $_POST['login'] ?? '' ?>">
            </label>
        </div>
        <div class="form-el">
            <label>Пароль
                <input type="password" name="password">
            </label>
        </div>
        <button class="btn">Зарегистрироваться</button>
    </form>
</div>