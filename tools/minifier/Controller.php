<?php

namespace Tools\Minifier;
use Classes\Micro_Templater;
use Classes\Tool;

require_once __DIR__ . '/../../classes/Tool.php';
require_once __DIR__ . '/../../classes/Micro_Templater.php';

require_once 'classes/CSSMin.php';
require_once 'classes/JSMin.php';
require_once 'classes/JSMinPlus.php';
require_once 'classes/HTML.php';


/**
 * Class Controller
 *
 * @package Tools\Base64
 */
class Controller extends Tool {

    public $title = 'Minifier';

    public function index() {

        $type_code = isset($_POST['type_code']) ? $_POST['type_code'] : '';

        $css_code  = isset($_POST['css_code']) ? $_POST['css_code'] : '';
        $js_code   = isset($_POST['js_code']) ? $_POST['js_code'] : '';
        $html_code = isset($_POST['html_code']) ? $_POST['html_code'] : '';

        $minify_css  = '';
        $minify_js   = '';
        $minify_html = '';

        $tpl = new Micro_Templater(__DIR__ . '/html/minifier.html');

        switch ($type_code) {
            case 'css':
                $minifier = new \CSSMin();
                $minify_css = $minifier->run($css_code);

                $original_len = strlen($css_code);
                $minify_len   = strlen($minify_css);

                $tpl->css_statistic->assign('[original]', $original_len);
                $tpl->css_statistic->assign('[minified]', $minify_len);
                $tpl->css_statistic->assign('[difference]', $original_len > $minify_len
                    ? '-' . ($original_len - $minify_len)
                    : ($minify_len !== $original_len ? '+' . ($minify_len - $original_len) : 0)
                );
                $tpl->css_statistic->assign('[difference_percent]', round($original_len > $minify_len
                    ? ((($original_len - $minify_len) / $original_len) * 100)
                    : ($minify_len !== $original_len ? (($minify_len - $original_len) / $minify_len) * 100 : 0), 2)
                );
                break;

            case 'js':
                $minify_js = \JSMinPlus::minify($js_code);

                $original_len = strlen($js_code);
                $minify_len   = strlen($minify_js);

                $tpl->js_statistic->assign('[original]', $original_len);
                $tpl->js_statistic->assign('[minified]', $minify_len);
                $tpl->js_statistic->assign('[difference]', $original_len > $minify_len
                    ? '-' . ($original_len - $minify_len)
                    : ($minify_len !== $original_len ? '+' . ($minify_len - $original_len) : 0)
                );
                $tpl->js_statistic->assign('[difference_percent]', round($original_len > $minify_len
                        ? ((($original_len - $minify_len) / $original_len) * 100)
                        : ($minify_len !== $original_len ? (($minify_len - $original_len) / $minify_len) * 100 : 0), 2)
                );
                break;

            case 'html':
                $minify_html = \Minify_HTML::minify($html_code);

                $original_len = strlen($html_code);
                $minify_len   = strlen($minify_html);

                $tpl->html_statistic->assign('[original]', $original_len);
                $tpl->html_statistic->assign('[minified]', $minify_len);
                $tpl->html_statistic->assign('[difference]', $original_len > $minify_len
                    ? '-' . ($original_len - $minify_len)
                    : ($minify_len !== $original_len ? '+' . ($minify_len - $original_len) : 0)
                );
                $tpl->html_statistic->assign('[difference_percent]', round($original_len > $minify_len
                        ? ((($original_len - $minify_len) / $original_len) * 100)
                        : ($minify_len !== $original_len ? (($minify_len - $original_len) / $minify_len) * 100 : 0), 2)
                );
                break;

            default : $type_code = 'css'; break;
        }


        $tpl->assign('[CSS_CODE]',    htmlspecialchars($css_code));
        $tpl->assign('[JS_CODE]',     htmlspecialchars($js_code));
        $tpl->assign('[HTML_CODE]',   htmlspecialchars($html_code));
        $tpl->assign('[MINIFY_CSS]',  htmlspecialchars($minify_css));
        $tpl->assign('[MINIFY_JS]',   htmlspecialchars($minify_js));
        $tpl->assign('[MINIFY_HTML]', htmlspecialchars($minify_html));
        $tpl->assign('_TYPE_CODE_',  $type_code);

        return $tpl->render();
    }
}