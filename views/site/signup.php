<div class="form-container">
    <h2>Регистрация нового пользователя</h2>

    <h3><?= $message ?? ''; ?></h3>

    <form method="post">
        <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>"/>
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