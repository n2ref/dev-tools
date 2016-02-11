<?php


namespace Tools\Formatter;
use Classes\Micro_Templater;
use Classes\Tool;

require_once __DIR__ . '/../../classes/Tool.php';
require_once __DIR__ . '/../../classes/Micro_Templater.php';


/**
 * Class Controller
 *
 * @package Tools\Formatter
 */
class Controller extends Tool {

    public $title = 'Formatter';

    public function index() {

        $tpl = new Micro_Templater(__DIR__ . '/html/formatter.html');
        return $tpl->render();
    }
}