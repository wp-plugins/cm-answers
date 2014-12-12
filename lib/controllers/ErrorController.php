<?php


class CMA_ErrorController extends CMA_BaseController {
    public static function errorAction() {
        return array('errors'=>self::_getErrors());
    }
}

?>
