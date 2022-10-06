<?php


namespace IanRothmann\InertiaApp\Middleware;


use Closure;
use Exception;
use IanRothmann\InertiaApp\Menu\MenuGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use InertiaApp;

abstract class Menu
{
    protected $disableBackButton = false;

    public function __construct()
    {
        $this->updateTabSession();
    }

    /**
     * @param MenuGroup $menu
     */
    abstract protected function menu(MenuGroup $menu): void;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    final public function handle(Request $request, Closure $next)
    {
        $menu = $this->newMenu();

        $this->menu($menu);

        !$this->disableBackButton && $this->addBackButton($menu);

        return $next($request);
    }

    private function newMenu(): MenuGroup
    {
        return InertiaApp::menu('main');
    }

    private function addBackButton(MenuGroup $menuGroup): void
    {
        if ($url = $this->getPreviousUrl()) {
            $menuGroup->link('Back', $url, 'mdi-arrow-left-bold');
        }
    }

    private function updateTabSession()
    {
        $prevMenu = tab_manager('menu_middleware');
        $currentMenu = get_called_class();

        tab_manager()->set([
            'prev_menu_middleware' => $prevMenu,
            'menu_middleware' => $currentMenu,
        ]);
    }

    private function getPreviousUrl()
    {
        $url = null;

        if (tab_manager()->has()) {
            $prevMenu = tab_manager('prev_menu_middleware');
            $currentMenu = tab_manager('menu_middleware');
            $url = URL::previous();

            //Check that prev menu is not descendant of current menu. In other words, the prev url cannot be used if we are navigating from a descendant to its ancestor.
            if ($url && $prevMenu && $prevMenu !== $currentMenu && !$prevMenu::isDescendantOf($currentMenu) && !$this->visitingPrevUrl()) {
                tab_manager()->set('navigation_history.' . $currentMenu, $url);
            } else {
                $url = tab_manager('navigation_history.' . $currentMenu);
            }
        }
        else {
            $parent = tab_manager()->getLatestForCurrentURL();
            if($parent) {
                $url = Arr::get($parent, 'navigation_history.' . get_called_class()) ?? null;
            }
        }

        if (!$url) {
            $url = $this->getDefaultPrevious();
        }

        return $url;
    }

    private function visitingPrevUrl() {
        $prevMenu = tab_manager('prev_menu_middleware');
        return tab_manager('navigation_history.' . $prevMenu) === request()->fullUrl();
    }

    protected static function isDescendantOf(string $menu): bool
    {
        $currentMenu = get_called_class();
        $allDescendants = $menu::getDescendants();

        $getDescendants = function($descendants) use(&$getDescendants, &$allDescendants) {
            foreach ($descendants as $descendant) {
                $newDescendants = $descendant::getDescendants();

                $allDescendants = array_merge($allDescendants, $newDescendants);

                $getDescendants($newDescendants);
            }
        };

        $getDescendants($allDescendants);

        return in_array($currentMenu, $allDescendants);
    }

    public function getDefaultPrevious(): ?string
    {
        return null;
    }

    public function getDescendants(): array
    {
        return [];
    }
}