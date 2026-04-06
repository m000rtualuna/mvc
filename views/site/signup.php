<div class="form-container">
    <h2>Регистрация</h2>

    <h3><?= $message ?? ''; ?></h3>

    <form method="post" enctype="multipart/form-data">
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
        <input type="file" name="avatar" accept="image/*">
        <button class="btn">Зарегистрироваться</button>
    </form>
</div>