<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="../../public/css/styles.css">
    <title>Main Site</title>
</head>
<body>

<header>
    <nav>
        <?php
        if (!app()->auth::check()):
            ?>
            <a href="<?= app()->route->getUrl('/login') ?>">Вход</a>
            <a href="<?= app()->route->getUrl('/signup') ?>">Регистрация</a>
        <?php
        else:
            ?>
            <a href="<?= app()->route->getUrl('/subscribers') ?>">Абоненты</a>
            <a href="<?= app()->route->getUrl('/subdivisions') ?>">Подразделения</a>
            <a href="<?= app()->route->getUrl('/rooms') ?>">Помещения</a>
            <a href="<?= app()->route->getUrl('/telephones') ?>">Телефоны</a>

            <a href="<?= app()->route->getUrl('/logout') ?>">Выход</a>
        <?php
        endif;
        ?>
    </nav>
    <p>Этот пользователь: <?= app()->auth::user()->name ?></p>
</header>

<main>
    <?= $content ?? '' ?>
</main>

</body>
</html>