<div class="adding">
    <h1>Абоненты</h1>

    <form method="GET" action="" class="fltr" id="filterForm">
        <div class="fltr-cntnr">
            <input type="text" name="search" placeholder="Поиск по имени/фамилии" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn">Поиск</button>
        </div>

        <select name="department_id" id="department_select" onchange="this.form.submit()">
            <option value="">Все подразделения</option>
            <?php foreach ($subdivisions as $subdivision): ?>
                <option value="<?= $subdivision->id ?>"
                        <?php if (isset($_GET['department_id']) && $_GET['department_id'] == $subdivision->id): ?>
                            selected
                        <?php endif; ?>>
                    <?= htmlspecialchars($subdivision->name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <button type="button" id="showFormBtn" class="btn">Добавить абонента</button>
</div>

<div class="form-container" id="justForm" style="display: none;">
    <h4>Добавление абонента</h4>
    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="text" id="subscriber_name" name="subscriber_name" placeholder="Имя" required>
        <input type="text" id="subscriber_surname" name="subscriber_surname" placeholder="Фамилия" required>
        <input type="text" id="subscriber_patronymic" name="subscriber_patronymic" placeholder="Отчество" required>
        <input type="date" id="subscriber_date_of_birth" name="subscriber_date_of_birth" required>

        <select id="subscriber_subdivision_id" name="subscriber_subdivision_id" required>
            <option value="">Выберите подразделение</option>
            <?php foreach ($subdivisions as $sub): ?>
                <option value="<?= $sub->id ?>"><?= htmlspecialchars($sub->name) ?></option>
            <?php endforeach; ?>
        </select>

        <h3>Доступные номера</h3>
        <div class="checkboxes">
            <?php if (!$telephones->isEmpty()): ?>
                <?php foreach ($telephones as $phone): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" name="phone_ids[]" value="<?= $phone->id ?>">
                        <?= htmlspecialchars($phone->phone_number) ?>
                    </label><br>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Нет таких</p>
            <?php endif; ?>
        </div>



        <button type="submit" class="btn">Добавить абонента</button>
    </form>
</div>

<div class="count-container">
    <div class="count">
        <h4>Количество абонентов по подразделениям</h4>
        <?php foreach ($subdivisions as $subdivision): ?>

            <?= htmlspecialchars($subdivision->name) ?> - <?= $subdivision->subscribers_count ?><br>
        <?php endforeach; ?>
    </div>


    <div class="count">
        <h4>Количество абонентов по помещениям</h4>
        <?php foreach ($rooms as $room): ?>
            <?= htmlspecialchars($room->name) ?> - <?= $room->subscribers_count ?><br>
        <?php endforeach; ?>
    </div>
</div>


<table border="2" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 60%; text-align: center;">
    <thead>
    <tr>
        <th>Имя</th>
        <th>Фамилия</th>
        <th>Отчество</th>
        <th>Дата рождения</th>
        <th>Подразделение</th>
        <th>Номер телефона</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $displaySubscribers = !empty($phonesByDepartment) ? $phonesByDepartment : $subscribers;

    if (empty($displaySubscribers)):
        ?>
        <tr>
            <td colspan="6">Данные не найдены</td>
        </tr>
    <?php else: ?>
        <?php foreach ($displaySubscribers as $subscriber): ?>
            <tr>
                <td><?= htmlspecialchars($subscriber->name ?? '-') ?></td>
                <td><?= htmlspecialchars($subscriber->surname ?? '-') ?></td>
                <td><?= htmlspecialchars($subscriber->patronymic ?? '-') ?></td>
                <td><?= htmlspecialchars($subscriber->date_of_birth ?? '-') ?></td>
                <td><?= htmlspecialchars($subscriber->subdivision->name ?? '-') ?></td>
                <td>
                    <?php
                    $phonesDisplay = [];

                    if (!empty($subscriber->telephone) && is_iterable($subscriber->telephone)) {
                        foreach ($subscriber->telephone as $phone) {
                            if (is_object($phone) && isset($phone->phone_number)) {
                                $phonesDisplay[] = htmlspecialchars($phone->phone_number);
                            }
                        }
                    }
                    if (!empty($phonesDisplay)) {
                        echo implode('<br>', $phonesDisplay);
                    } else {
                        echo 'Не назначен';
                    }
                    ?>
                </td>

            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>


<script>
    document.getElementById('showFormBtn').onclick = function() {
        const form = document.getElementById('justForm');
        form.style.display = form.style.display === 'none' ? 'flex' : 'none';
    };
</script>
