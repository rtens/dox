<?php
namespace rtens\dox\model;

use rtens\dox\content\item\CommentItem;

class Specification {

    public $name;

    /** @var CommentItem */
    public $description;

    /** @var array|Method[] */
    public $scenarios = array();

    /** @var array|Method[] */
    public $methods = array();

    /** @var null|string */
    public $path;

    function __construct($name) {
        $this->name = $name;
    }

} 