<?php
/**
 * web application interface to hide your php code
 */

namespace Tools\Obfuscate;
use Classes\Micro_Templater;
use Classes\Tool;

require_once __DIR__ . '/../../classes/Tool.php';
require_once __DIR__ . '/../../classes/Micro_Templater.php';



/**
 * Class Controller
 *
 * @package Tools\Obfuscate
 */
class Controller extends Tool {

    public $title    = 'Obfuscate';
    public $position = 4;


    /**
     * @return string
     */
    public function index() {

        // Download File
        if (isset($_FILES['obfuscate_file']) && $_FILES['obfuscate_file']['error'] == 0) {
            $filename       = $_FILES['obfuscate_file']['tmp_name'];
            $obfuscate_code = $this->obfuscate($filename);

            unlink($filename);

            return $this->downloadFile($_FILES['obfuscate_file']['name'], $obfuscate_code);


        } else {
            if ( ! empty($_POST['php_code'])) {
                $php_code = trim($_POST['php_code']);
                if ( ! preg_match('~^\s*(<\?php|<\?)~', $php_code)) $php_code = '<?php ' . $php_code;
                $filename = tempnam(sys_get_temp_dir(), 'tmp');
                $handle   = fopen($filename, "w");
                fwrite($handle, $php_code);
                fclose($handle);

            } else {
                $php_code = '';
                $filename = '';
            }

            if (file_exists($filename)) {
                $obfuscate_code = $this->obfuscate($filename);
                unlink($filename);
            } else {
                $obfuscate_code = '';
            }


            $tpl = new Micro_Templater(__DIR__ . '/html/obfuscate.html');
            $tpl->assign('[PHP_CODE]',       htmlspecialchars($php_code));
            $tpl->assign('[OBFUSCATE_CODE]', htmlspecialchars($obfuscate_code));

            return $tpl->render();
        }
    }


    /**
     * @param $filename
     * @return string
     */
    private function obfuscate($filename) {

        // Минификация
        $php_code = php_strip_whitespace($filename);
        $php_code = preg_replace('~^(\t|\n|\r| |)*(<\?php|<\?)(\t|\n|\r| |)~', '', $php_code);
        $php_code = preg_replace('~(\?>(\t|\n|\r| |)*)$~', '', $php_code);
        $php_code = addcslashes($php_code, '\\');


        // Сжатие
        $php_code = gzcompress($php_code);
        $php_code = base64_encode($php_code);


        // Упаковка - 1
        $create_function = '$hodpe="\143"."\x72\145\x61\164\x65\137\x66\165\x6e\143\x74\151\x6f\156";'; // уловка
        $assert          = '$hojfh="\141"."\x73\163\x65\162\x74";';
        $gzuncompress    = '$hojdt="\147"."\x7a\165\x6e\143\x6f\155\x70\162\x65\163\x73";';
        $base64_decode   = '$hodla="\142"."\x61\163\x65\66\x34\137\x64\145\x63\157\x64\145";';

        $php_code        = '$houtr=$hojdt($hodla("' . $php_code .'"));@eval($houtr);';


        // Упаковка - 2
        $php_code        = base64_encode($php_code);
        $php_code        = "<?php\n$create_function $assert $gzuncompress $base64_decode @\$hojfh(\"\\145\\x76\\141\\x6c(\$hodla(\\\"" . $php_code . "\\\"));\");";

        return $obfuscate_code = wordwrap($php_code, 90, "\n", true);
    }


    /**
     * @param  string $string
     * @return string
     */
    private function strtohex($string) {
        $string = str_split($string);
        $i = 1;
        foreach ($string as &$char) {
            $char = (++$i % 2)
                ? "\x" . dechex(ord($char))
                : "\\" . decoct(ord($char));
        }
        return implode('', $string);
    }


    /**
     * @param  string $name
     * @param  string $content
     * @return string
     */
    private function downloadFile($name, $content) {

        header('Content-Description: File Transfer');
        header('Content-Type: plain/text');
        header('Content-Disposition: attachment; filename="' . $name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . mb_strlen($content));
        ob_clean();
        flush();
        echo $content;
        exit;
    }
}