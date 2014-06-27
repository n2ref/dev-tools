<?php
/**
 * php-obfuscate - web application interface to hide your php code.
 *
 * Copyright (C) 2014  Shabunin Igor
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @author: Shabunin Igor
 * @email: shabuninil24@gmail.com
 * @see: https://github.com/shinji00/php-obfuscate
 */

function strtohex ($string) {
    $string = str_split($string);
    $i = 1;
    foreach ($string as &$char)
        $char = (++$i % 2) ? "\x" . dechex(ord($char)) : "\\" . decoct(ord($char));
    return implode('', $string);
}


function downloadFile ($name, $content) {

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . mb_strlen($content));
    ob_clean();
    flush();
    echo $content;
    exit;
}



try {
    if (isset($_FILES['obfucate_file']) && $_FILES['obfucate_file']['error'] == 0) {
        $php_file = $_FILES['obfucate_file']['tmp_name'];
        $method = 'file';
    } elseif ($_POST['php_code'] != '') {
        $php_code = trim($_POST['php_code']);
        if ( ! preg_match('~^(<\?php|<\?)~', $php_code)) $php_code = '<?php ' . $php_code;
        $php_file = tempnam(sys_get_temp_dir(), 'tmp');
        $handle = fopen($php_file, "w");
        fwrite($handle, $php_code);
        fclose($handle);
        $method = 'text';
    }


    if ($php_file != '') {
        // Минификация
        $php_code = php_strip_whitespace($php_file);
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
        $php_code        = "<?php \n$create_function $assert $gzuncompress $base64_decode @\$hojfh(\"\\145\\x76\\141\\x6c(\$hodla(\\\"" . $php_code . "\\\"));\");";

        $obfuscate_code = wordwrap($php_code, 90, "\n", true);


        if ($method == 'file') {
            downloadFile($_FILES['obfucate_file']['name'], $obfuscate_code);
        }

        unlink($php_file);

    } else {
        $obfuscate_code = '';
    }

} catch (Exception $e) {
    echo '<pre>';
    throw new $e;
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP - Obfuscator</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link type="text/css" href="css/bootstrap.min.css" rel="stylesheet">
    <link type="text/css" href="css/style.css" rel="stylesheet">
    <script type="application/javascript" src="js/jquery.js"></script>
    <script type="application/javascript" src="js/bootstrap.min.js"></script>
    <script type="application/javascript" src="js/bootstrap.filestyle.js"></script>
    <script type="application/javascript">
        $(document).ready(function () {
            $('#myTab a.text').tab('show');
            $(":file").filestyle();
            $('#myTab a').click(function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    </script>
</head>

<body>

<p style='padding:50 0 0 0'>

<div id="content">

    <div>
        <ul class="nav nav-tabs" id="myTab">
            <li><a class="text" href="#text">Text</a></li>
            <li><a class="file" href="#file">File</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane" id="text">
                <form action="" method="post">
                    <div class="field-wrapper">
                        <label>
                            <span class="field_name">PHP code:</span>
                            <textarea name="php_code"><?php echo htmlspecialchars($_POST['php_code']); ?></textarea>
                        </label>
                    </div>

                    <div class="field-wrapper">
                        <label>
                            <span class="field_name">Result:</span>
                            <textarea readonly="readonly" itemscope=""><?php echo htmlspecialchars($obfuscate_code); ?></textarea>
                        </label>
                    </div>

                    <div class="submit-wrapper">
                        <input type="submit" value="Send" class="btn btn-primary">
                    </div>
                </form>
            </div>
            <div class="tab-pane" id="file">
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="field-wrapper">
                        <label>
                            <span class="field_name">File with PHP code:</span>
                            <input type="file" name="obfucate_file">
                        </label>
                    </div>

                    <div class="submit-wrapper">
                        <input type="submit" value="Send" class="btn btn-primary">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
</p>

</body>
</html>