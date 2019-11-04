<?php
/**
 * Created by PhpStorm.
 * User: ianrothmann
 * Date: 10/25/19
 * Time: 8:55 AM
 */

namespace IanRothmann\InertiaApp\Menu;


use Illuminate\Contracts\Support\Arrayable;

class MenuItem extends AbstractMenuItem implements \JsonSerializable, Arrayable
{
    protected $itemLink, $itemRoute, $itemRouteParams=[];

    /**
     * @param String $url
     * @return $this
     */
    public function link($url){
        $this->itemLink=$url;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function target($value){
        $this->itemTarget=$value;
        return $this;
    }

    /**
     * @param String $name
     * @param array $params
     * @return $this
     */
    public function route($name, $params=[]){
        $this->itemRoute=$name;
        $this->itemRouteParams=$params;
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['target']=$this->itemTarget;
        $data['url']=$this->itemLink;
        $data['route']=$this->itemRoute;
        $data['route_params']=$this->itemRouteParams;

        return $data;
    }
}