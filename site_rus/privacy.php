<?php
session_start();
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Макетная студия | Политика конфиденциальности</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background: #F2F0ED;
            color: #2C2B28;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            background: #FFFFFF;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            border: 1px solid #EBE9E4;
        }

        .logo h1 {
            font-size: 1.2rem;
            font-weight: 450;
            letter-spacing: 3px;
            color: #2C2B28;
        }

        .logo p {
            font-size: 0.65rem;
            color: #A8A59E;
            letter-spacing: 0.5px;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #E3E1DB;
            color: #6B6963;
            padding: 0.5rem 1.2rem;
            border-radius: 30px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.2s;
        }

        .btn-outline:hover {
            background: #F5F4F1;
            border-color: #C2BFB8;
            color: #2C2B28;
        }

        .content {
            background: #FFFFFF;
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid #EBE9E4;
        }

        .content h1 {
            font-size: 1.3rem;
            font-weight: 450;
            letter-spacing: -0.3px;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #EBE9E4;
        }

        .content h2 {
            font-size: 0.95rem;
            font-weight: 500;
            margin: 1.5rem 0 0.75rem;
            color: #2C2B28;
        }

        .content p {
            font-size: 0.8rem;
            color: #4A4945;
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }

        .content ul {
            margin: 0.75rem 0 0.75rem 1.5rem;
        }

        .content li {
            font-size: 0.8rem;
            color: #4A4945;
            margin-bottom: 0.5rem;
        }

        .update-date {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #EBE9E4;
            font-size: 0.7rem;
            color: #A8A59E;
            text-align: center;
        }

        footer {
            margin-top: 2rem;
            text-align: center;
        }

        .back-link {
            color: #A8A59E;
            text-decoration: none;
            font-size: 0.7rem;
            transition: color 0.2s;
        }

        .back-link:hover {
            color: #2C2B28;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            .content {
                padding: 1.25rem;
            }
            .header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <h1>Макетная студия</h1>
                <p>Ильи Филиппенко</p>
            </div>
            <a href="index.php" class="btn-outline">← На главную</a>
        </div>

        <div class="content">
            <h1>Политика конфиденциальности</h1>
            
            <p>Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных пользователей сайта <strong>Макетная студия</strong>.</p>

            <h2>1. Общие положения</h2>
            <p>1.1. Оператором персональных данных является ИП Филиппенко И.Н. (далее — Оператор).</p>
            <p>1.2. Пользователь — физическое лицо, использующее сайт и передающее свои персональные данные при обращении к Оператору.</p>
            <p>1.3. Сайт не собирает персональные данные автоматически. Вся информация передается исключительно по инициативе пользователя.</p>

            <h2>2. Какие данные мы собираем</h2>
            <p>2.1. При обращении к Оператору через указанные контакты (телефон, Telegram) пользователь может предоставить следующие данные:</p>
            <ul>
                <li>Имя (по желанию);</li>
                <li>Номер телефона (обязательно для связи);</li>
                <li>Параметры заказа (размеры макета, особенности и т.д.).</li>
            </ul>
            <p>2.2. Сайт не использует файлы cookie и не собирает автоматически IP-адреса или технические данные об устройстве пользователя.</p>

            <h2>3. Цели обработки персональных данных</h2>
            <p>Оператор обрабатывает персональные данные пользователя в следующих целях:</p>
            <ul>
                <li>Консультирование по вопросам изготовления макетов;</li>
                <li>Обсуждение параметров заказа и формирование коммерческого предложения;</li>
                <li>Обратная связь и ответы на запросы пользователей.</li>
            </ul>

            <h2>4. Правовые основания обработки</h2>
            <p>4.1. Обработка персональных данных осуществляется на основании:</p>
            <ul>
                <li>Согласия пользователя, выражаемого путем инициативного обращения по телефону или в мессенджеры;</li>
                <li>Требований законодательства Российской Федерации.</li>
            </ul>
            <p>4.2. Пользователь вправе отозвать свое согласие на обработку персональных данных, направив письменное уведомление на контактные данные Оператора.</p>

            <h2>5. Порядок обработки и передачи данных</h2>
            <p>5.1. Оператор принимает необходимые организационные и технические меры для защиты персональных данных от неправомерного доступа, уничтожения, изменения или разглашения.</p>
            <p>5.2. Персональные данные не передаются третьим лицам, за исключением случаев, предусмотренных законодательством.</p>
            <p>5.3. Хранение данных осуществляется в электронной переписке на устройствах Оператора.</p>

            <h2>6. Права пользователя</h2>
            <p>Пользователь имеет право:</p>
            <ul>
                <li>Получить информацию о своих персональных данных, хранящихся у Оператора;</li>
                <li>Требовать уточнения, изменения или удаления своих персональных данных;</li>
                <li>Отозвать согласие на обработку персональных данных в любое время.</li>
            </ul>

            <h2>7. Сроки обработки и хранения</h2>
            <p>7.1. Персональные данные обрабатываются до достижения целей обработки либо до отзыва согласия пользователем.</p>
            <p>7.2. По истечении срока хранения или при отзыве согласия персональные данные подлежат уничтожению.</p>

            <h2>8. Контактная информация</h2>
            <p>По всем вопросам, связанным с обработкой персональных данных, пользователь может обратиться:</p>
            <ul>
                <li>По телефону/МАХ: <strong>+7 902 516 78 07</strong></li>
                <li>В Telegram: <strong>@irkcopy</strong></li>
            </ul>

            <h2>9. Изменение Политики конфиденциальности</h2>
            <p>Оператор вправе вносить изменения в настоящую Политику конфиденциальности. Актуальная версия всегда доступна на этой странице.</p>

            <div class="update-date">
                <small>Дата последнего обновления: 16 мая 2026 г.</small>
            </div>
        </div>

        <footer>
            <a href="index.php" class="back-link">← Вернуться на сайт</a>
        </footer>
    </div>
</body>
</html>