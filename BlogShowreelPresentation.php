<?php
/**
 * Created by PhpStorm.
 * User: vic
 * Date: 2010-nov-26
 * Time: 11:22:45
 * To change this template use File | Settings | File Templates.
 */

class BlogShowreelPresentation {

    private $name;
    private $url;
    private $active;
    private $img_path;
    private $id;

    public function __construct($id, $name, $url, $active, $img_path=null) {
        $this->name = $name;
        $this->id = $id;
        $this->url = $url;
        $this->active = $active;
        $this->img_path = $img_path;
    }

    public function id() {
        return $this->id;
    }

    public function image() {
        return $this->img_path;
    }

    public function name() {
        return $this->name;
    }

    public function url() {
        return $this->url;
    }

    public function isActive() {
        return $this->active;
    }
}

?>