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
        if(!$html)
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
     * @return bool|SimpleHtmlDomController
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
                \curl_setopt($curl, CURLOPT_CAINFO, getcwd() . '/ca.crt');
                \curl_setopt($curl, CURLOPT_SSLCERT, getcwd() . '/cert.pem');
                \curl_setopt($curl, CURLOPT_SSLCERTPASSWD, "9932");
                \curl_setopt($curl, CURLOPT_SSLCERTTYPE, "PEM");
                \curl_setopt($curl, CURLOPT_SSLKEY, getcwd() . '/keys.pem');
                \curl_setopt($curl, CURLOPT_SSLKEYPASSWD, "9932");
                $out = \curl_exec($curl);
                if($out === false)
                    throw new Exception(curl_error($curl),curl_errno($curl));
                \curl_close($curl);
            }
        }catch (Exception $e){
            var_dump(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()));exit();
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