<?php

namespace ParseBundle\Controller;

use Symfony\Component\Config\Definition\Exception\Exception;

class ParseController
{
    const HDOM_TYPE_ELEMENT = 1;
    const HDOM_TYPE_COMMENT = 2;
    const HDOM_TYPE_TEXT = 3;
    const HDOM_TYPE_ENDTAG = 4;
    const HDOM_TYPE_ROOT = 5;
    const HDOM_TYPE_UNKNOWN = 6;
    const HDOM_QUOTE_DOUBLE = 0;
    const HDOM_QUOTE_SINGLE = 1;
    const HDOM_QUOTE_NO = 3;
    const HDOM_INFO_BEGIN = 0;
    const HDOM_INFO_END = 1;
    const HDOM_INFO_QUOTE = 2;
    const HDOM_INFO_SPACE = 3;
    const HDOM_INFO_TEXT = 4;
    const HDOM_INFO_INNER = 5;
    const HDOM_INFO_OUTER = 6;
    const HDOM_INFO_ENDSPACE = 7;
    const DEFAULT_TARGET_CHARSET = 'UTF-8';
    const DEFAULT_BR_TEXT = "\r\n";
    const DEFAULT_SPAN_TEXT = " ";
    const MAX_FILE_SIZE = 600000;

    public function get_price($url,$query)
    {
        error_reporting(0);
        $html = $this->file_get_html($url);
        $result = '';
        $count_res = 0;
        if(!is_object($html) && isset($html['error'])){
            return $html;
        } elseif(!$html)
            return false;

        if ($html->innertext != '' and count($html->find($query))) {
            foreach ($html->find($query) as $price) {
                if ($count_res < 1) {
                    $result = str_replace(" ", "", $price->innertext);
                    $result = preg_replace('~<[^><]+>~', '', $result); // delete tags
                    $result = preg_replace('~[^0-9]+~', '', $result);  //  delete text
                    break;
                } else {
                    return false;
                }
            }
        }
        $html->clear();
        unset($html);
        return intval($result);
    }

    /**
     * @param $url
     * @param bool $use_include_path
     * @param null $context
     * @param int $offset
     * @param int $maxLen
     * @param bool $lowercase
     * @param bool $forceTagsClosed
     * @param string $target_charset
     * @param bool $stripRN
     * @param string $defaultBRText
     * @param string $defaultSpanText
     * @return bool|array|SimpleHtmlDomController
     */
    private function file_get_html($url, $use_include_path = false, $context=null, $offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, $target_charset = self::DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=self::DEFAULT_BR_TEXT, $defaultSpanText=self::DEFAULT_SPAN_TEXT)
    {
        $dom = new SimpleHtmlDomController(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        $out='';
        try {
            if ($curl = \curl_init()) {

                \curl_setopt($curl, CURLOPT_URL, $url);
                \curl_setopt($curl, CURLOPT_HEADER, false);
                \curl_setopt($curl, CURLOPT_FAILONERROR, 1);
                \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                \curl_setopt($curl, CURLOPT_POST, 0);
                \curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                \curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
                \curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
                \curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36');
                //\curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '/ca.crt');
                \curl_setopt($curl, CURLOPT_SSLCERT, __DIR__ . '/cert.pem');
                \curl_setopt($curl, CURLOPT_SSLCERTPASSWD, "9932");
                \curl_setopt($curl, CURLOPT_SSLCERTTYPE, "PEM");
                \curl_setopt($curl, CURLOPT_SSLKEY, __DIR__ . '/keys.pem');
                \curl_setopt($curl, CURLOPT_SSLKEYPASSWD, "9932");
                $out = \curl_exec($curl);
                if($out === false)
                    throw new Exception(curl_error($curl),curl_errno($curl));
                \curl_close($curl);
            }
        }catch (Exception $e){
            $error_codes=array(
                1 => 'Неподдерживаемый протокол',
                2 => 'Ошибка инициализации запроса',
                3 => 'URL не был должным образом отформатирована',
                4 => 'Запрашиваемый функция, протокол или параметр не был найден встроенный в этом Libcurl из-за решения сборки времени',
                5 => 'Не удалось разрешить прокси-сервер',
                6 => 'Не удалось разрешить хост',
                7 => 'Не удалось подключиться',
                //8 => 'CURLE_FTP_WEIRD_SERVER_REPLY',
                9 => 'Нам было отказано в доступе к ресурсу, указанному в URL',
                10 => 'Во время ожидания сервера для подключения назад, когда используется активный FTP сеанса, код ошибки был послан через управляющее соединение',
                11 => 'После того, как отправив пароль FTP на сервер, Libcurl ожидает правильного ответа',
                12 => 'Время ожидания истекло',
                13 => 'Не удалось получить разумный результат от сервера',
                14 => 'Недопустимый формат ответа',
                15 => 'Внутренний отказ для поиска хоста, используемый для нового соединения',
                16 => 'Недопустимый формат ответа',
                17 => 'Недопустимый формат ответа',
                18 => 'Передача файла была короче, или больше, чем ожидалось',
                19 => 'Недопустимый формат ответа',
                21 => 'Удаленный сервер возвратил ошибку страницы, проверте URL обращения',
                22 => 'Удаленный сервер возвратил ошибку страницы, проверте URL обращения',
                23 => 'Произошла ошибка при записи полученных данных в локальный файл',
                25 => 'Не удалось начать загрузку',
                26 => 'Существовал проблема чтения локального файла или сообщение об ошибке, возвращенное для чтения обратного вызова',
                27 => 'Недостаточно памяти для получения контента страницы',
                28 => 'Время ожидания истекло, сервер не дал ответа',
                30 => 'Проблема соединения с удаленным сервером',
                31 => 'Удаленный сервер возвратил ошибку страницы',
                33 => 'Сервер не поддерживает или принимать запросы диапазона',
                34 => 'Удаленный сервер возвратил ошибку страницы',
                35 => 'Ошибка SSL соединения',
                36 => 'Ошибка загрузки',
                37 => 'Файл дается с FILE: // не может быть открыт',
                38 => 'LDAP операции произошел сбой привязки',
                39 => 'Поиск LDAP не удалось',
                41 => 'Функция не найдена',
                42 => 'Обратный вызов возвращается "прервать" в Libcurl',
                43 => 'Внутренняя ошибка. Функция была вызвана плохим параметром.',
                45 => 'Ошибка интерфейса',
                47 => 'Слишком много переадресаций на удаленном сервере',
                //48 => 'CURLE_UNKNOWN_TELNET_OPTION',
                49 => 'Строка Опция Telnet была Незаконно отформатирована',
                51 => 'SSL сертификат или SSH md5 отпечатков пальцев удаленного сервера был признан не в порядке',
                52 => 'Ничего не было возвращено с сервера, а также при обстоятельствах, не получая ничего считается ошибкой',
                53 => 'Указанный крипто двигатель не был найден',
                54 => 'Ошибка установки выбранного крипто двигатель SSL по умолчанию',
                55 => 'Ошибка отправки данных по сети',
                56 => 'Сбой при получении данных по сети',
                58 => 'Проблема с локальным сертификатом клиента',
                59 => 'Невозможно использовать указанный шифром',
                60 => 'Сертификат Peer не может пройти проверку подлинности с помощью известных сертификатов CA',
                61 => 'Непризнанный кодирование передачи',
                62 => 'Неверный LDAP URL',
                63 => 'Максимальный размер файла превышен',
                64 => 'Запрошенный FTP уровень SSL не удалось',
                65 => 'При выполнении операции завиток отправки пришлось перемотать данные ретранслируют, но операция перемотки назад не удалось',
                66 => 'Инициирование SSL Engine не удалось',
                67 => 'Удаленный сервер отказал логин для входа',
                68 => 'Файл не найден на сервере TFTP',
                69 => 'Проблема Разрешение на TFTP-сервере',
                70 => 'Недостаток дискового пространства на сервере',
                71 => 'Незаконные операции TFTP',
                72 => 'Неизвестный перевод TFTP ID',
                73 => 'Файл уже существует и не будет перезаписан',
                74 => 'Незаконные операции TFTP',
                75 => 'Преобразование символов не удалось',
                76 => 'Caller должны зарегистрировать обратные вызовы преобразования',
                77 => 'Проблема с чтением сертификат SSL CA',
                78 => 'Ресурс, указанный в URL не существует',
                79 => 'Произошла неизвестная ошибка во время SSH-сессии',
                80 => 'Не удалось закрыть соединение SSL',
                81 => 'Ошибка ответа удаленного сервера',
                82 => 'Не удалось загрузить файл CRL',
                83 => 'SSL ошибка ISSUER',
                84 => 'FTP - сервер не понимает команду',
                85 => 'Несовпадение чисел RTSP CSeq',
                86 => 'Несовпадение RTSP Session идентификаторами',
                87 => 'Не удалось разобрать список файлов FTP',
                88 => 'Часть обратного вызова сообщила об ошибке',
                89 => 'Нет доступных соединений, сеанс будет поставлен в очередь',
                90 => 'Не удалось соответствовать возлагали ключ',
                91 => 'Статус возвращается сбой',
                92 => 'Ошибка потока в обрамление слое HTTP / 2',
            );
            return
                [
                    'error'=>(isset($error_codes[$e->getCode()])?$error_codes[$e->getCode()]:sprintf('Запрос вернул ошибку #%d: %s',$e->getCode(), $e->getMessage())),
                    'desc'=>$e->getMessage()
                ];

        }
        $contents = $out;
        if (empty($contents) /*|| strlen($contents) > MAX_FILE_SIZE*/)
        {
            return false;
        }
        $dom->load($contents, $lowercase, $stripRN);

        return $dom;
    }

    public static function str_get_html($str, $lowercase=true, $forceTagsClosed=true, $target_charset = self::DEFAULT_TARGET_CHARSET, $stripRN=true, $defaultBRText=self::DEFAULT_BR_TEXT, $defaultSpanText=self::DEFAULT_SPAN_TEXT)
    {
        $dom = new SimpleHtmlDomController(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
        if (empty($str) || strlen($str) > self::MAX_FILE_SIZE)
        {
            $dom->clear();

        }
        $dom->load($str, $lowercase, $stripRN);

        return $dom;
    }
}