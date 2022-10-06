<?php
/**
 * Created by PhpStorm.
 * User: ianrothmann
 * Date: 10/25/19
 * Time: 8:55 AM
 */

namespace IanRothmann\InertiaApp\Menu;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Class MenuGroup
 * @package IanRothmann\InertiaApp\Menu
 * @property Collection $items;
 * @property \Closure guardResolver;
 */
class MenuGroup extends AbstractMenuItem implements \JsonSerializable, Arrayable
{
    protected $items;
    protected $guardResolver;

    public function __construct($guardResolver = null)
    {
        $this->items = new Collection();
        $this->guardResolver = $guardResolver;
    }

    public static function create($label, $icon = null)
    {
        return (new MenuGroup())
            ->label($label)
            ->icon($icon);
    }

    /**
     * @param $label
     * @param $routeName
     * @param array $params
     * @param null $icon
     * @param null $rightOrClosure
     * @param bool $prepend
     * @param array $options
     * @return $this
     * @throws \Exception
     */
    public function route($label, $routeName, $params = [], $icon = null, $rightOrClosure = null, $prepend = false, $options = [])
    {
        $item = (new MenuItem())
            ->label($label)
            ->route($routeName, $params)
            ->icon($icon)
            ->right($rightOrClosure)
            ->options($options);

        $this->addItem($item, $prepend);

        return $this;
    }

    /**
     * @param $label
     * @param $url
     * @param null $icon
     * @param null $rightOrClosure
     * @param bool $prepend
     * @return $this
     * @throws \Exception
     */
    public function link($label, $url, $icon = null, $rightOrClosure = null, $prepend = false)
    {
        $item = (new MenuItem())
            ->label($label)
            ->link($url)
            ->icon($icon)
            ->right($rightOrClosure);

        $this->addItem($item, $prepend);

        return $this;
    }

    /**
     * @param MenuItem $item
     * @param bool $prepend
     * @return $this
     * @throws \Exception
     */
    public function custom(MenuItem $item, $prepend = false)
    {
        $this->addItem($item, $prepend);
        return $this;
    }


    /**
     * @param MenuGroup $group
     * @param bool $prepend
     * @return $this
     * @throws \Exception
     */
    public function group(MenuGroup $group, $prepend = false)
    {
        if ($group->items->count() > 0) {
            $this->addItem($group, $prepend);
        }
        return $this;
    }

    /**
     * @param $label
     * @param $routeName
     * @param array $params
     * @param null $icon
     * @param null $rightOrClosure
     * @return $this
     * @throws \Exception
     */
    public function prependRoute($label, $routeName, $params = [], $icon = null, $rightOrClosure = null)
    {
        $this->route($label, $routeName, $params, $icon, $rightOrClosure, true);
        return $this;
    }

    /**
     * @param $label
     * @param $url
     * @param null $icon
     * @param null $rightOrClosure
     * @return $this
     * @throws \Exception
     */
    public function prependLink($label, $url, $icon = null, $rightOrClosure = null)
    {
        $this->link($label, $url, $icon, $rightOrClosure, true);
        return $this;
    }

    /**
     * @param MenuItem $item
     * @return $this
     * @throws \Exception
     */
    public function prependCustom(MenuItem $item)
    {
        $this->custom($item, true);
        return $this;
    }

    /**
     * @param MenuGroup $group
     * @return $this
     * @throws \Exception
     */
    public function prependGroup(MenuGroup $group)
    {
        $this->group($group, true);
        return $this;
    }

    /**
     * @param $routeName
     * @param array $params
     * @param null $rightOrClosure
     * @param string $label
     * @param string $icon
     * @return $this
     * @throws \Exception
     */
    public function back($routeName, $params = [], $rightOrClosure = null, $label = 'Back', $icon = 'mdi-arrow-left-bold')
    {
        $url = route($routeName, $params);
        $item = (new MenuItem())
            ->label($label)
            ->link($url)
            ->icon($icon)
            ->right($rightOrClosure);

        $this->addItem($item, false);

        return $this;
    }

    private function addItem($item, $prepend = false)
    {
        $shouldAdd = true;
        if ($item->itemAccessRight != null) {
            if (is_callable($item->itemAccessRight)) {
                $func = $item->itemAccessRight;
                $shouldAdd = $func();
            } else {
                if (!is_callable($this->guardResolver)) {
                    throw new \Exception('No menu item guard resolver specified.');
                } else {
                    $func = $this->guardResolver;
                    $shouldAdd = $func($item->itemAccessRight);
                }
            }
        }

        if ($shouldAdd) {
            $prepend ? $this->items->prepend($item) : $this->items->add($item);
        }
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $data = parent::toArray();
        $data['items'] = $this->items->toArray();
        return $data;
    }
}