<?php namespace App\MapService\Facade;

use Illuminate\Support\Facades\Facade;
/**
 * @see \Illuminate\Html\HtmlBuilder
 */
class MapServiceFacade extends Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'MapService'; }
}