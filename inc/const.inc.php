<?
################################################################################
#   
#   
#                     
#   Copyright (с) 2003
#                     
#   const.php         
#   Определения констант 
#                        
################################################################################

define ('EMAIL_HEADER',       'From: ......ru'."\n");

define ('CLOGIN_NO',               0);
define ('CLOGIN_ENTER_LOGIN',      1);
define ('CLOGIN_LOGIN_NOT_VALID',  2);
define ('CLOGIN_BAD_LOGIN_OR_PWD', 3);
define ('CLOGIN_OK',               4);
define ('CLOGIN_NEED_KEY',         5);
define ('CLOGIN_BAD_KEY',          6);
define ('CLOGIN_AUTH_REQ',         7);

define ('PATH_TO_PIC', 'pic/');
define ('PATH_TO_MENU', 'menu/');
define ('PATH_TO_ADMORDERS','orders/');
define ('CLIENT_ADM_PAGE', 'clients/clients_comp/');
define ('CLIENT_USER_ADM_PAGE', 'clients/userscards/');
define ('INDEX_PAGE','index');
define ('ADMIN_MAIL','info@arttech.ru');
define ('PRINT_ORD_PAGE','print_form.php');

define ('ERR_SEND_USER_QUESTION','Ваш вопрос успешно отправлен! Вы получите ответ в ближайшее время.');
define ('ERR_SEND_ADVERT_QUESTION','Ваш запрос успешно отправлен! Мы свяжемся с Вами в ближайшее время.');
define ('ERR_SEND_FEEDBACK','Большое спасибо! Ваше мнение очень ценно для нас.');
define ('ERR_PAGE_NOT_ALLOWED','У Вас недостаточно прав для просмотра данной страницы.');
define ('ERR_PAGE_NOT_ALLOWED2','Войдите на сайт с использованием Вашего логина и пароля.');
define ('ERR_ADD_AMOUNT','Неверно указана сумма для пополнения счета.');
define ('ERR_MENU_CONT','Не найдено меню на указанную дату');
define ('ERR_NO_ORDER','Заказов на указанную дату не обнаружено!');
define ('ERR_ORDER_CONFIRMED','<H1>Спасибо, Ваш заказ добавлен в общий заказ компании.</H1><P>Вы можете его изменить до момента, пока общий заказ не отправлен поставщику.</P>');

$week_days_arr = array("","Понедельник","Вторник", "Среда", "Четверг", "Пятница","Суббота","Воскресенье");
$months_array_to = array("1" => "января","2" => "февраля","3" => "марта","4" => "апреля","5" => "мая","6" => "июня","7" => "июля","8" => "августа","9" => "сентября","10" => "октября","11" => "ноября","12" => "декабря");
$months_array = array("1" => "январь","2" => "февраль","3" => "март","4" => "апрель","5" => "май","6" => "июнь","7" => "июль","8" => "август","9" => "сентябрь","10" => "октябрь","11" => "ноябрь","12" => "декабрь");

?>