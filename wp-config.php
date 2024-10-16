<?php
/**
 * Основные параметры WordPress.
 *
 * Скрипт для создания wp-config.php использует этот файл в процессе установки.
 * Необязательно использовать веб-интерфейс, можно скопировать файл в "wp-config.php"
 * и заполнить значения вручную.
 *
 * Этот файл содержит следующие параметры:
 *
 * * Настройки MySQL
 * * Секретные ключи
 * * Префикс таблиц базы данных
 * * ABSPATH
 *
 * @link https://ru.wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Параметры MySQL: Эту информацию можно получить у вашего хостинг-провайдера ** //
/** Имя базы данных для WordPress */
define( 'DB_NAME', "sdalwp" );

/** Имя пользователя MySQL */
define( 'DB_USER', "root" );

/** Пароль к базе данных MySQL */
define( 'DB_PASSWORD', "root" );

/** Имя сервера MySQL */
define( 'DB_HOST', "localhost" );

/** Кодировка базы данных для создания таблиц. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Схема сопоставления. Не меняйте, если не уверены. */
define( 'DB_COLLATE', '' );

define( 'DISABLE_WP_CRON', true ); 

/**#@+
 * Уникальные ключи и соли для аутентификации.
 *
 * Смените значение каждой константы на уникальную фразу. Можно сгенерировать их с помощью
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ сервиса ключей на WordPress.org}.
 *
 * Можно изменить их, чтобы сделать существующие файлы cookies недействительными.
 * Пользователям потребуется авторизоваться снова.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '5$~wGYGfQe{ARA,*f(O%1s]J5zI)Jxn^Cm/^wGY5L>NoLTtgB}UYSj!D:1=A(0;M' );
define( 'SECURE_AUTH_KEY',  '~XcU?niZkVb/h(sG5:<#<RWWp`y7{U9h;,(imxUPyNtj[RG><*^zOIqsmVjzcuO4' );
define( 'LOGGED_IN_KEY',    ']<_^IV=JCkT{Ad>GoR7v ,pF2kTbF`@* >5*Wm,0?lP=u/QM/Llb();mvgZNaX&}' );
define( 'NONCE_KEY',        '},-^o+$iYW!*sv`[4X&n&K{7}Ih[K8e&?5heE0{CinhM.<Xv1sPjl,p`P=g P`_L' );
define( 'AUTH_SALT',        'I}qpO?^|g,zR/u3)yJ [87{j pC2AG{{z`vP0{p?Cn0{Z]2=Ow#-MS85nnC)OpM0' );
define( 'SECURE_AUTH_SALT', '9n4]yfF@S8$2%%@P_VhQNwb #_@M5P3FKAN3CSch;8[_C+2r0cez)vFENk6yZK>z' );
define( 'LOGGED_IN_SALT',   'Bma},C&*wPcK#tZPk^>Gz6u{XRjR/=|+pE&6u=JfjAcA9ciu}6l@KXW+l=-@SE*3' );
define( 'NONCE_SALT',       'oqieAK80arMbCCm/N-_U&=|Hq}QAOKQk~G!::{P0%8tv6G>yEeZYaCrC%hn>tAYp' );

/**#@-*/

/**
 * Префикс таблиц в базе данных WordPress.
 *
 * Можно установить несколько сайтов в одну базу данных, если использовать
 * разные префиксы. Пожалуйста, указывайте только цифры, буквы и знак подчеркивания.
 */
$table_prefix = 'wp_';

/**
 * Для разработчиков: Режим отладки WordPress.
 *
 * Измените это значение на true, чтобы включить отображение уведомлений при разработке.
 * Разработчикам плагинов и тем настоятельно рекомендуется использовать WP_DEBUG
 * в своём рабочем окружении.
 *
 * Информацию о других отладочных константах можно найти в документации.
 *
 * @link https://ru.wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Произвольные значения добавляйте между этой строкой и надписью "дальше не редактируем". */



/* Это всё, дальше не редактируем. Успехов! */

/** Абсолютный путь к директории WordPress. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname(__FILE__) . '/' );
}

/** Инициализирует переменные WordPress и подключает файлы. */
require_once ABSPATH . 'wp-settings.php';
