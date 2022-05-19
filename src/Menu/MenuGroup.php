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

    public function back(string $defaultRouteName = null, array $defaultParams = [], $rightOrClosure = null, string $label = 'Back', string $icon = 'mdi-arrow-left-bold')
    {
        $key = config('inertia-app.nav_history.session_key') . '.' . $this->getCallingClass();
        $fromBackUrlKey = config('inertia-app.nav_history.request_key');
        $url = $this->getBackUrl($key);
        $right = null;
        //If url not in session, try previous or use default
        if (!$url) {
//        Detect if previous url was a back url from another menu, if true fallback default url
//        This prevents navigation deeper into the stack via the back url
            $url = intval(session()->get($fromBackUrlKey)) === 1 ? null : url()->previous();
            if (!$url || !$this->isValidBackUrl($url)) {
                if ($defaultRouteName) {
                    $url = route($defaultRouteName, $defaultParams);
                    $right = $rightOrClosure;
                }
            } else {
                $this->setBackUrl($key, $url);
            }
        }
        if ($url) {
            //Append param to detect when url was clicked, via SetFromBackUrlInSession middleware.
            $url .= (parse_url($url, PHP_URL_QUERY) ? '&' : '?') . $fromBackUrlKey . '=1';

            $item = (new MenuItem())
                ->label($label)
                ->link($url)
                ->icon($icon);
            if ($right) {
                $item->right($right);
            }
            $this->addItem($item);
        }
        return $this;
    }

    private function getBackUrl(string $key)
    {
        $url = session()->get($key, null);
        return $url;
    }

    private function setBackUrl(string $key, string $url)
    {
        session()->put($key, $url);
    }

    private function isValidBackUrl(string $url)
    {
        $parsed = parse_url($url);
        $path = ltrim($parsed['path'] ?? '', '/');
        $blacklist = config('inertia-app.nav_history.blacklist', []);
        return
            //Validate host
            isset($parsed['host']) && $parsed['host'] === request()->getHttpHost() &&
            //Avoid using system base url as url
            isset($parsed['path']) && $parsed['path'] !== '/' &&
            //Url does not start with blacklisted paths
            !array_reduce($blacklist, function ($carry, $blacklistPath) use ($path) {
                return $carry or str_starts_with($path, ltrim($blacklistPath, '/'));
            }, false) &&
            //URL is not used as route inside menu
            !$this->currentMenuContainsUrl($url);
    }

    private function getCallingClass()
    {
        $trace = debug_backtrace();
        $class = $trace[1]['class'];

        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i]))
                if ($class != $trace[$i]['class']) {
                    return $trace[$i]['class'];
                }
        }

        throw new \Exception('Calling class could not be determined');
    }

    private function currentMenuContainsUrl(string $url)
    {
        //Merge groups with items and iterate through collection to find a match
        $items = $this->items->reduce(function ($carry, $item) {
            /**
             * @var Collection $carry
             */
            $class = get_class($item);
            if ($class === MenuItem::class) {
                $carry->push($item);
            } elseif ($class === MenuGroup::class) {
                $carry = $carry->concat($item->items);
            } else {
                throw new \LogicException();
            }

            return $carry;
        }, collect());

        //Remove query string
        //When there is a query string appended this function will return false even if the base url in contained withing the current middleware menu
        $url = preg_replace('/\?.*/', '', $url);

        return $items->contains(function ($item) use ($url) {
            $itemData = $item->toArray();
            if ($itemData['url']) {
                return $itemData['url'] === $url;
            } else {
                return route($itemData['route'], $itemData['route_params']) === $url;
            }
        });
    }
}
