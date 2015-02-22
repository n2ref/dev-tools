<?php

namespace Classes;


/**
 * Class Tool
 * @package Classes
 */
abstract class Tool {

    public $title    = '';
    public $position = 5;

    abstract public function index();
}