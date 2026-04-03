<div class="form-container">
<h2>Авторизация</h2>
<h3><?= $message ?? ''; ?></h3>

<h3><?= app()->auth->user()->name ?? ''; ?></h3>
<?php
if (!app()->auth::check()):
?>
<form method="post">
        <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>"/>
<label>Логин <input type="text" name="login"></label>
<label>Пароль <input type="password" name="password"></label>
<button class="btn">Войти</button>
</form>
</div>
<?php endif;