<?php

namespace Classes;

require_once 'Micro_Templater.php';


/**
 * Class Init
 *
 * @package Classes
 */
class Init {

    const TOOLS_DIR = 'tools';
    
    protected static $tools = array();


    /**
     * @return string
     * @throws \Exception
     */
    public function dispatch() {

        $tpl   = new Micro_Templater(__DIR__ . '/../html/index.html');
        $tools = $this->getTools();
        $current_tool = isset($_GET['tool']) ? $_GET['tool'] : false;

        if ( ! empty($tools)) {
            foreach ($tools as $name => $tool) {
                $tpl->tool->assign('[NAME]',   $name);
                $tpl->tool->assign('[TITLE]',  ! empty($tool['vars']['title']) ? $tool['vars']['title'] : $name);
                $tpl->tool->assign('[ACTIVE]', $name == $current_tool ? 'active': '');
                $tpl->tool->reassign();
            }
        }

        if ($current_tool) {
            ob_start();
            $content = $this->callTool($current_tool);
            $ob = ob_get_clean();

            $tpl->assign('[CONTENT]', $ob . $content);

        } else {
            $tpl->setAppendAttr('#menu:first-child li', 'class', 'active');
            $tpl->assign('[CONTENT]', file_get_contents(__DIR__ . '/../html/home.html'));
        }

        return $tpl->render();
    }


    /**
     * @return array
     */
    protected function getTools() {
        
        if (empty(self::$tools) && is_dir(self::TOOLS_DIR)) {
            $h = opendir(self::TOOLS_DIR);
            while ($tool = readdir($h)) {
                if ($tool != '.' && $tool != '..' &&
                    is_dir(self::TOOLS_DIR . '/' . $tool) &&
                    is_file(self::TOOLS_DIR . "/{$tool}/Controller.php")
                ) {
                    require_once self::TOOLS_DIR . "/{$tool}/Controller.php";

                    $tool_class = '\\Tools\\' . ucfirst($tool) . '\\Controller';
                    if (class_exists($tool_class)) {
                        $tool_vars = get_class_vars($tool_class);
                        $parents   = class_parents($tool_class);
                        if (in_array('Classes\\Tool', $parents)) {
                            self::$tools[$tool] = array(
                                'vars' => $tool_vars
                            );
                        }
                    }
                }
            }

            // Sorting
            if ( ! empty(self::$tools)) {
                $array_positions = array();
                foreach (self::$tools as $name => $params) {
                    $array_positions[$name] = $params['vars']['position'];
                }
                asort($array_positions);
                $sorted_tools = array();
                foreach ($array_positions as $name => $position) {
                    $sorted_tools[$name] = self::$tools[$name];
                }
                self::$tools = $sorted_tools;
            }
        }

        return self::$tools;
    }


    /**
     * @param  string     $tool
     * @return string
     * @throws \Exception
     */
    protected function callTool($tool) {

        $tools = $this->getTools();

        if (isset($tools[$tool])) {
            $tool_class = '\\Tools\\' . ucfirst($tool) . '\\Controller';

            $tool_controller = new $tool_class();
            return $tool_controller->index();

        } else {
            throw new \Exception("Tool '{$tool}' not found");
        }
    }
}