ОРИГИНАЛ  https://www.nulled.cc/threads/234480/
также смотреть сюда: https://wordpress.stackexchange.com/questions/1567/best-collection-of-code-for-your-functions-php-file
http://www.wpfunction.me/

Собственно, при разработке шаблонов для Wordpress и сайтов на этой CMS собираются своеобразные плюшки, которые потом часто используются. 
Решил поделиться. Думаю для новичков это будет полезно, да и сам)) не забуду и не потеряю. Все коды вставляются в файл functions.php 
в папке установленной темы.
Итак, что у нас сегодня:

1. Удаляем всякую байду из хедера страницы, типа версии вашего вордпресса, дополнительных ссылок на RSS, ссылок для всяких сервисов 
типа Really Simple Discovery, программ Windows Live Writer и т.п. чепуху, которая нам не нужна и использоваться не будет.
# удаляем всякую байду из head страницы
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'index_rel_link' );
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
remove_action( 'wp_head', 'wp_generator' );


2. Удаляем всплывающие подсказки (тег title="") типа: "Просмотреть все записи в рубрике…" из ссылок на категории и теги. 
Выглядят они достаточно глупо, особенно если мы делаем не блог, а например корпоративный сайт, портфолио и т.п.
# удаляем title из ссылок категорий и тегов
function removeTitle($str){
$str = preg_replace("/title=\".*\"/", '', $str);
return $str;
}
add_filter("wp_list_categories", "removeTitle");
add_filter("wp_list_pages", "removeTitle");
add_filter("the_category", "removeTitle");

3. Удаляем все HTML теги из комментариев, пусть любители) втиснуть ссылку в комментарий отдохнут. Кстати, в интернете широко известен другой способ, но он html-код не удаляет, а тупо делает его в комментариях простым текстом. Меня это не прикалывает, этот вариант - удаляет все теги полностью.
// удаляем HTML в комментариях при их добавлении.
function preprocess_comment_striptags($commentdata) {
$commentdata['comment_content'] = strip_tags($commentdata['comment_content']);
return $commentdata;
}
add_filter('preprocess_comment', 'preprocess_comment_striptags');

// удаляем HTML в комментариях при показе, если на сайте уже есть комменты с ссылками и т.п.
function comment_text_striptags($string) {
return strip_tags($string);
}
add_filter('comment_text', 'comment_text_striptags');


4. Убираем дурацкие символы [...] в конце кратких анонсов на главной, категориях, архивах, результатах поиска и т.п. (к слову, такие киксы даже у флагманов Перейти по ссылке - прокрутить страницу вниз.) Собственно, мы их не просто убиваем, а меняем на симпатичную ссылку "читать далее.."
# удаляем дурацкие символы в конце кратких анонсов
function new_excerpt_more($more) {
global $post;
return ' <a href="'. get_permalink($post->ID) . '">читать далее..</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');

5. При навешивании кучи плагинов и прочих свистелок & перделок, хорошо бы видеть, сколько времени занимает генерация страницы, а так же сколько при этом было запросов в базу и использовано памяти. Этот код выводит в футере страницы инфу о времени генерации, запросах и потреблении памяти.
# выводим время генерации, запросы и потребление памяти
function usage(){
printf( ('SQL запросов:%d. Время генерации:%s сек. Потребление памяти:'), get_num_queries(), timer_stop(0, 3) );
if ( function_exists('memory_get_usage') ) echo round( memory_get_usage()/1024/1024, 2 ) . ' mb ';
}
add_filter('admin_footer_text', 'usage');
add_filter('wp_footer', 'usage');

6. Удаляем поле "сайт" из формы комментариев. Еще один гвоздик в гроб любителей тискануть ссылку на свой гс. Да я и сам)) хоть и очень редко где-то что-то комментирую, но увидев поле "ваш сайт", как говорится, трудно пройти)) мимо. Так что убираем, нафиг, это искушение:
# удаляем поле сайт из формы комментариев
function remove_comment_fields($fields) {
unset($fields['url']);
return $fields;
}
add_filter('comment_form_default_fields', 'remove_comment_fields');

7.Отключаем создание ненужных превью картинок в WordPress
    1 выясняем какие картинки создаются у нас при создании/парсинге новостей
  Чтобы узнать какие размеры зарегистрированы на сайте, добавьте следующий код в темы header.php или footer.php. Так вы увидите какие на сайте существуют размеры и как они называются.
  <?
/**
* Получает информацию обо всех зарегистрированных размерах картинок.
*
* @global $_wp_additional_image_sizes
* @uses   get_intermediate_image_sizes()
*
* @param  boolean [$unset_disabled = true] Удалить из списка размеры с 0 высотой и шириной?
* @return array Данные всех размеров.
*/
function get_image_sizes( $unset_disabled = true ) {
    $wais = & $GLOBALS['_wp_additional_image_sizes'];

    $sizes = array();

    foreach ( get_intermediate_image_sizes() as $_size ) {
        if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
            $sizes[ $_size ] = array(
                'width'  => get_option( "{$_size}_size_w" ),
                'height' => get_option( "{$_size}_size_h" ),
                'crop'   => (bool) get_option( "{$_size}_crop" ),
            );
        }
        elseif ( isset( $wais[$_size] ) ) {
            $sizes[ $_size ] = array(
                'width'  => $wais[ $_size ]['width'],
                'height' => $wais[ $_size ]['height'],
                'crop'   => $wais[ $_size ]['crop'],
            );
        }

        // size registered, but has 0 width and height
        if( $unset_disabled && ($sizes[ $_size ]['width'] == 0) && ($sizes[ $_size ]['height'] == 0) )
            unset( $sizes[ $_size ] );
    }

    return $sizes;
}

die( print_r( get_image_sizes() ) );
?>

      2. открываем functions.php и вставляем туда код.
Этот вариант более полный, потому что он выключает размеры в нескольких местах, а не только во время загрузки изображения в папку uploads.
## отключаем создание миниатюр файлов для указанных размеров
add_filter( 'intermediate_image_sizes', 'delete_intermediate_image_sizes' );
function delete_intermediate_image_sizes( $sizes ){
    // размеры которые нужно удалить
    return array_diff( $sizes, array(
        'thumbnail',
        'medium',
        'medium_large',
        'large',
        'shop_thumbnail',
        'shop_catalog',
        'shop_single',
        'pw-page-box',
        'pw-inline',
        'pw-latest-news',
    ) );
}
}


8. Задался вопросом о том, что когда приходит письмо через плагин CF7 отправитель является wordpress@mysite.ru
Порылся в интернете, нашёл занятное решение.

Изменить данное поведение движка можно, добавив следующий код в файл functions.php вашей темы:

PHP:
//изменение имени и email писем start
function change_fromemail($email){return 'noreply@mysite.ru';}
function change_fromname($name){return 'New name';}
add_filter('wp_mail_from', 'change_fromemail');
add_filter('wp_mail_from_name', 'change_fromname');
//изменение имени и email писем end
Где "New name" это имя, а "noreply@mysite.ru" это email.


9.Чтобы на странице checkout спрятать лишние поля, не обязательно ставить плагины, достаточно добавить в functions.php
PHP:
add_filter('woocommerce_checkout_fields','bp_rename_field');
function bp_rename_field ($fields){
    unset($fields['billing']['billing_first_name']);
        unset($fields['billing']['billing_last_name']);
        unset($fields['billing']['billing_company']);
        unset($fields['billing']['billing_address_1']);
        unset($fields['billing']['billing_address_2']);
        unset($fields['billing']['billing_city']);
        unset($fields['billing']['billing_postcode']);
        unset($fields['billing']['billing_country']);
        unset($fields['billing']['billing_state']);
        unset($fields['billing']['billing_phone']);
        unset($fields['order']['order_comments']);
        unset($fields['billing']['billing_email']);
        unset($fields['account']['account_username']);
        unset($fields['account']['account_password']);
        unset($fields['account']['account_password-2']);


    return $fields;
}
То что нужно оставить, комментируем или удаляем


10.А еще вместо плагинов типа Cyr-to-Lat (если заголовок написан кириллицей, то и путь(slug) получается кириллицей. Данная функция заменяет в пути кириллицу на латиницу) я делаю так:
добавить в functions.php
PHP:
function rutranslit($title) {
    $chars = array(
//rus
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
        "Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
        "З"=>"Z","И"=>"I","Й"=>"Y","К"=>"K","Л"=>"L",
        "М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
        "С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"KH",
        "Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"",
        "Ы"=>"Y","Ь"=>"","Э"=>"YE","Ю"=>"YU","Я"=>"YA",
        "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
        "е"=>"e","ё"=>"yo","ж"=>"zh",
        "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"kh",
        "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
        "ы"=>"y","ь"=>"","э"=>"ye","ю"=>"yu","я"=>"ya",
//spec
        "—"=>"-","«"=>"","»"=>"","…"=>"","№"=>"N",
        "—"=>"-","«"=>"","»"=>"","…"=>"",
        "!"=>"","@"=>"","#"=>"","$"=>"","%"=>"","^"=>"","&"=>"",
//ukr
        "Ї"=>"Yi","ї"=>"i","Ґ"=>"G","ґ"=>"g",
        "Є"=>"Ye","є"=>"ie","І"=>"I","і"=>"i",
//kazakh
        //"Ә"=>"A","Ғ"=>"G","Қ"=>"K","Ң"=>"N","Ө"=>"O","Ұ"=>"U","Ү"=>"U","H"=>"H",
        //"ә"=>"a","ғ"=>"g","қ"=>"k","ң"=>"n","ө"=>"o", "ұ"=>"u","h"=>"h"
    );

        if (seems_utf8($title)) $title = urldecode($title);
        $title = preg_replace('/\.+/','.',$title);
        $r = strtr($title, $chars);
        return $r;
    }
    add_filter('sanitize_file_name','rutranslit');
    add_filter('sanitize_title','rutranslit');
    
    
11.


ДОБАВЛЕННЫЕ ПЛЮШКИ:
--
//Не выводить определенные Рубрики на главной странице сайта!
function exclude_category($query) {
if ( $query->is_home ) {
$query->set('category__not_in', array(81,83,82,85));}
return $query;
}
add_filter('pre_get_posts', 'exclude_category');
Где, в моём случае 81,83,82,85 - это ID тех рубрик которые нужно скрыть. думаю как определить Id все знают))

//удаляем любую категорию из фида, где 1 - номер категории:
function filter_feed_cat($query) {
if ($query->is_feed) {
$query->set('cat','-1');
}
return $query;
}
add_filter('pre_get_posts','filter_feed_cat');

--------
// Отключение обновления темы
remove_action('load-update-core.php','wp_update_themes');
add_filter('pre_site_transient_update_themes',create_function('$a', "return null;"));
wp_clear_scheduled_hook('wp_update_themes');

// Отключение обновления плагинов
remove_action( 'load-plugins.php', 'wp_update_plugins' );
remove_action( 'load-update.php', 'wp_update_plugins' );
remove_action( 'admin_init', '_maybe_update_plugins' );
remove_action( 'wp_update_plugins', 'wp_update_plugins' );
add_filter( 'pre_transient_update_plugins', create_function( '$a',
"return null;" ) );

// Отключение обновления движка
remove_action( 'wp_version_check', 'wp_version_check' );
remove_action( 'admin_init', '_maybe_update_core' );
add_filter( 'pre_transient_update_core', create_function( '$a',
"return null;" ) );

// Убираем meta generator
remove_action('wp_head', 'wp_generator');

// Отключаем RSS
function fb_disable_feed() {
wp_die( __('No feed available,please visit our <a href="'. get_bloginfo('url') .'">homepage</a>!') );
}
add_action('do_feed', 'fb_disable_feed', 1);
add_action('do_feed_rdf', 'fb_disable_feed', 1);
add_action('do_feed_rss', 'fb_disable_feed', 1);
add_action('do_feed_rss2', 'fb_disable_feed', 1);
add_action('do_feed_atom', 'fb_disable_feed', 1);


// Удаляем лишние теги
remove_filter( ‘the_content’, ‘wpautop’ );
remove_filter( ‘the_excerpt’, ‘wpautop’ );

// Избавляемся от ver в css/js
function remove_cssjs_ver( $src ) {
    if( strpos( $src, '?ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'remove_cssjs_ver', 10, 2 );
add_filter( 'script_loader_src', 'remove_cssjs_ver', 10, 2 );


--------
//Редирект результатов поиска с /?s=query на /search/query/ и редирект на главную если параметр поиска empty
//Редирект результатов поиска с /?s=query на /search/query/, конвертация %20% на +
function nice_search_redirect() {
  global $wp_rewrite;
  if (!isset($wp_rewrite) || !is_object($wp_rewrite) || !$wp_rewrite->using_permalinks()) {
    return;
  }

  $search_base = $wp_rewrite->search_base;
  if (is_search() && !is_admin() && strpos($_SERVER['REQUEST_URI'], "/{$search_base}/") === false) {
    wp_redirect(home_url("/{$search_base}/" . urlencode(get_query_var('s'))));
    exit();
  }
}

if (current_theme_supports('nice-search')) {
  add_action('template_redirect', 'nice_search_redirect');
}

//Редирект на главную если параметр поиска empty
function request_filter($query_vars) {
  if (isset($_GET['s']) && empty($_GET['s'])) {
    $query_vars['s'] = ' ';
  }

  return $query_vars;
}

add_filter('request', 'request_filter');

---------
Этот небольшой код удалит информацию о версии движка из шапки на всех страницах.
// remove version info from head and feeds
function complete_version_removal() {
return '';
}
add_filter('the_generator', 'complete_version_removal');

------
Подключение библиотеки JQuery с CDN сервера.
Впринципе js библиотеки нужно подключать в футере, быстрее будет показан контент. Исключение если js участвует в первоначальном формировании контента (в WP это редко встречается).
/**
* Отключение штаной загрузки библиотеки JQuery
*/
if (!is_admin()) {
    add_action("wp_enqueue_scripts", "deregister_jquery", 10);
}
function deregister_jquery() {
    wp_deregister_script('jquery');
}
/**
* Подключение js библиотек в футере через хук wp_footer
*/
if (!is_admin()) {
    add_action( 'wp_footer', 'get_footer_libs' );
}
function get_footer_libs() {
    echo '    <!-- JS -->';
    // Последний JQuery (при необходимеости нужно заменить на поддерживаемую версию)
    echo '    <script src="http://code.jquery.com/jquery-latest.min.js"></script>';
    // Для старых версий IE
    echo '    <!--[if lt IE 9]><script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
}

---------
Оформление страницы входа на сайт:
function gb_custom_login_css()
{
echo PHP_EOL .'
<style type="text/css">
#login h1 a {background-image: url(/logo.jpg)!important; width: 265px; height: 55px; -webkit-background-size: 265px 55px; background-size: 265px 55px; }
#login #backtoblog {display: none !important; }
</style>' . PHP_EOL;
}
add_action('login_head', 'gb_custom_login_css');

function gb_custom_loginlogo_url($url)
{
return 'http://myunreal.ru';
}
add_filter('login_headerurl', 'gb_custom_loginlogo_url');


function gb_custom_loginlogo_title($url)
{
return 'cайт посвященный группе UNREAL';
}
add_filter('login_headertitle', 'gb_custom_loginlogo_title');
function hide_admin_bar_settings() {
?>

--------
//Вывод заголовка, ограниченного по количеству символов
function trim_title_chars($count, $after) {
          $title = get_the_title();
          if (mb_strlen($title) > $count) $title = mb_substr($title,0,$count);
          else $after = '';
          echo $title . $after;
}
Меняем <?php the_title(); ?> в вашей теме на <?php trim_title_chars(30, '...'); ?>
30 - это количество символов.


//Вывод заголовка, ограниченного по количеству слов
function trim_title_words($count, $after) {
          $title = get_the_title();
          $words = split(' ', $title);
          if (count($words) > $count) {
                    array_splice($words, $count);
                    $title = implode(' ', $words);
          }
          else $after = '';
          echo $title . $after;
}
Меняем <?php the_title(); ?> в вашей теме на <?php trim_title_words(5, '...'); ?>
5 - это количество слов

---------
//Возможность автоматически вставлять в конец записей копирайт с бэклинком
function add_post_content($content) {
if (!is_home()) {         
 $content .= '<p>Источник: <a href="link">Ваш сайт</a></p>';
}
return $content;
}
add_filter('the_content', 'add_post_content'); 

-------
//Вставляем рекламу после определенного количества символов.( https://wp-kama.ru/id_236/reklamnyiy-blok-v-tekste-stati.html)
function kama_content_advertise($text){
//спустя сколько символов искать перенос строки и вставлять рекламу?
    $nu = 400;
//Код рекламы
    $adsense = <<<HTML
<div style="float:right;margin:0 0 10px 15px;">
Здесь ваш рекламный код
</div>
HTML;
    //    return str_replace('<span id="more-5424"></span>', $adsense.'<!--more-->', $text);
    return preg_replace('@([^^]{'.$nu.'}.*?)(\r?\n\r?\n|
)@', "\\1$adsense\\2", trim($text), 1);
}
add_filter('the_content', 'kama_content_advertise', -10);

--------
/*** Чистим заголовки сервера ***/
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );//Удаляем Link rel=shortlink
header_remove( 'x-powered-by' ); //Удаляем X-Powered-By
function ny_remove_x_pingback( $headers ) {
unset( $headers['X-Pingback'] );
return $headers;
}
add_filter( 'wp_headers', 'ny_remove_x_pingback' );//Удаляем X-Pingback

/*** Исправляем в мета-теге robots на страницах replytocom - noindex follow на noindex nofollow ***/ 
remove_action( 'wp_head', 'wp_no_robots' );
function replytocom_robots() {
echo "<meta name='robots' content='noindex,nofollow' />\n";
}
if ( isset( $_GET['replytocom'] ) )
add_action( 'wp_head', 'replytocom_robots' );


----------
// Отключение Emoji в редакторе tinyMCE:
add_filter( 'tiny_mce_plugins', 'disable_emojicons_tinymce' );

function disable_emojicons_tinymce( $plugins ) {
  if ( is_array( $plugins ) ) {
    return array_diff( $plugins, array( 'wpemoji' ) );
  } else {
    return array();
  }
}
