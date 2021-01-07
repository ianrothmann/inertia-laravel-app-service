<?php
/**
 * Created by PhpStorm.
 * User: ianrothmann
 * Date: 10/25/19
 * Time: 8:55 AM
 */

namespace IanRothmann\InertiaApp\Menu;


use Illuminate\Contracts\Support\Arrayable;

abstract class AbstractMenuItem implements \JsonSerializable, Arrayable
{
    protected $itemLabel, $itemHint, $itemIcon, $itemId, $itemTarget, $itemAccessRight;

    /**
     * @param String $value
     * @return $this
     */
    public function label($value){
        $this->itemLabel=$value;
        return $this;
    }

    /**
     * @param String $value
     * @return $this
     */
    public function icon($value){
        $this->itemIcon=$value;
        return $this;
    }

    /**
     * @param String|\Closure $rightOrClosure
     * @return $this
     */
    public function right($rightOrClosure){
        $this->itemAccessRight=$rightOrClosure;
        return $this;
    }
    
    /**
     * @param String $value
     * @return $this
     */
    public function id($value){
        $this->itemId=$value;
        return $this;
    }

    /**
     * @param String $value
     * @return $this
     */
    public function hint($value){
        $this->itemHint=$value;
        return $this;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray(){
        $data=[];
        $data['id']=$this->itemId;
        $data['label']=$this->itemLabel;
        $data['icon']=$this->itemIcon;
        $data['hint']=$this->itemHint;

        return $data;
    }
}