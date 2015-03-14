<?php

namespace Tools\Converter;
use Classes\Micro_Templater;
use Classes\Tool;

require_once __DIR__ . '/../../classes/Tool.php';
require_once __DIR__ . '/../../classes/Micro_Templater.php';



/**
 * Class Controller
 *
 * @package Tools\Converter
 */
class Controller extends Tool {

    public $title = 'Converter';

    private $encodings = array(
        'UTF' => array(
            'UTF-8'  => 'utf-8',
            'UTF-16' => 'utf-16',
            'UTF-32' => 'utf-32',
        ),
        'ASCII' => array(
            'ASCII' => 'ASCII',
        ),
        'CP' => array(
            'CP1250' => 'cp1250',
            'CP1251' => 'cp1251',
            'CP1252' => 'cp1252',
            'CP1253' => 'cp1253',
            'CP1254' => 'cp1254',
            'CP1255' => 'cp1255',
            'CP1256' => 'cp1256',
            'CP1257' => 'cp1257',
        ),
        'ISO' => array(
            'ISO-8859-1'  => 'ISO-8859-1',
            'ISO-8859-2'  => 'ISO-8859-2',
            'ISO-8859-3'  => 'ISO-8859-3',
            'ISO-8859-4'  => 'ISO-8859-4',
            'ISO-8859-5'  => 'ISO-8859-5',
            'ISO-8859-6'  => 'ISO-8859-6',
            'ISO-8859-7'  => 'ISO-8859-7',
            'ISO-8859-8'  => 'ISO-8859-8',
            'ISO-8859-9'  => 'ISO-8859-9',
            'ISO-8859-10' => 'ISO-8859-10',
            'ISO-8859-11' => 'ISO-8859-11',
            'ISO-8859-12' => 'ISO-8859-12',
            'ISO-8859-13' => 'ISO-8859-13',
            'ISO-8859-14' => 'ISO-8859-14',
            'ISO-8859-15' => 'ISO-8859-15'
        ),
        'KOI8' => array(
            'KOI8-R'  => 'KOI8-R',
            'KOI8-U'  => 'KOI8-U',
            'KOI8-RU' => 'KOI8-RU',
            'KOI8-T'  => 'KOI8-T',
        )
    );

    public function index() {

        $method    = isset($_POST['method']) ? $_POST['method'] : 'base64_text';
        $text_data = isset($_POST['text_data']) ? $_POST['text_data'] : '';


        $tpl = new Micro_Templater(__DIR__ . '/html/converter.html');

        // Encoding
        $encoding_from = isset($_POST['encoding_from']) ? $_POST['encoding_from'] : '';
        $encoding_to   = isset($_POST['encoding_to']) ? $_POST['encoding_to'] : '';

        $tpl->fillDropDown('#encoding_from', $this->encodings, $encoding_from);
        $tpl->fillDropDown('#encoding_to',   $this->encodings, $encoding_to);


        switch ($method) {
            case 'base64_text':
                $encoding_text_data = base64_decode($text_data);
                $tpl->setAttr('input[value="base64_text"]', 'checked', 'checked');
                break;

            case 'text_base64':
                $encoding_text_data = base64_encode($text_data);
                $tpl->setAttr('input[value="text_base64"]', 'checked', 'checked');
                break;

            case 'base64_unserialize_text':
                ob_start();
                print_r(unserialize(base64_decode($text_data)));
                $encoding_text_data = ob_get_clean();
                $tpl->setAttr('input[value="base64_unserialize_text"]', 'checked', 'checked');
                break;

            case 'text_encoding':
                $encoding_text_data = iconv($encoding_from , $encoding_to, $text_data);
                $tpl->setAttr('input[value="text_encoding"]', 'checked', 'checked');
                break;

            default: $encoding_text_data = ''; break;
        }


        $tpl->assign('[TEXT_DATA]',          htmlspecialchars($text_data));
        $tpl->assign('[ENCODING_TEXT_DATA]', htmlspecialchars($encoding_text_data));

        return $tpl->render();
    }
}