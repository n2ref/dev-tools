<?php

namespace Tools\Base64;
use Classes\Micro_Templater;
use Classes\Tool;

require_once __DIR__ . '/../../classes/Tool.php';
require_once __DIR__ . '/../../classes/Micro_Templater.php';



/**
 * Class Controller
 *
 * @package Tools\Base64
 */
class Controller extends Tool {

    public $title = 'Base64';

    public function index() {

        $method    = isset($_POST['method']) ? $_POST['method'] : 'base64_text';
        $text_data = isset($_POST['text_data']) ? $_POST['text_data'] : '';


        $tpl = new Micro_Templater(__DIR__ . '/html/base64.html');

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

            default: $encoding_text_data = ''; break;
        }


        $tpl->assign('[TEXT_DATA]',          htmlspecialchars($text_data));
        $tpl->assign('[ENCODING_TEXT_DATA]', htmlspecialchars($encoding_text_data));

        return $tpl->render();
    }
}