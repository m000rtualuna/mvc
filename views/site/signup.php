<div class="form-container">
    <h2>Регистрация нового пользователя</h2>
    <h3><?= $message ?? ''; ?></h3>
    <form method="post">
        <div class="form-el">
            <label>Имя <input type="text" name="name"></label>
        </div>
        <div class="form-el">
            <label>Логин <input type="text" name="login"></label>
        </div>
        <div class="form-el">
            <label>Пароль <input type="password" name="password"></label>
        </div>
        <button class="btn">Зарегистрироваться</button>
    </form>
</div>