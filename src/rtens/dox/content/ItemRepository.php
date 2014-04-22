<?php
namespace rtens\dox\content;

use rtens\dox\content\item\CodeItem;
use rtens\dox\content\item\CommentItem;
use rtens\dox\content\item\StepsItem;

class ItemRepository {

    private $items = array();

    function __construct() {
        $this->items = self::defaultItems();
    }

    private static function defaultItems() {
        return array(
            new CommentItem(),
            new StepsItem(),
            new CodeItem(),
        );
    }

    public function getItems() {
        return $this->items;
    }

    public function addItem(Item $r) {
        $this->items[] = $r;
    }

} 