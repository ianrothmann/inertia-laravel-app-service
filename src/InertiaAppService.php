<?php

namespace IanRothmann\InertiaApp;

use IanRothmann\InertiaApp\Menu\MenuGroup;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Session;

/**
 * Class InertiaAppService
 * @package IanRothmann\InertiaApp
 * @property Collection $menuContainer
 * @property \Closure $userRightResolver
 */
class InertiaAppService
{

    private $menuContainer;
    private $menuItemGuardResolver;
    private $currentPageTitle;
    private $breadcrumbsSessionKey='_breadcrumbs';
    private $breadcrumbCount=5;
    private $authData=[];

    public function __construct()
    {
        $this->menuContainer=new Collection();
    }

    private function addToBreadcrumbs($title,$url){
        $crumbs=collect(session($this->breadcrumbsSessionKey,[]));

        if(!$crumbs->contains('url', $url)){
            $crumbs[]=compact('title','url');
        }
        else {
            $index = $crumbs->search(function($crumb) use($url) {
                return $crumb['url'] === $url;
            });
            $crumbs = $crumbs->slice(0, $index + 1);
        }

        if($crumbs->count()>$this->breadcrumbCount){
            $crumbs->shift();
        }
        session([$this->breadcrumbsSessionKey=>$crumbs->toArray()]);
    }

    public function auth($authData){
        $this->authData=$authData;
        $this->authData['timeout']=config('session.lifetime')*60;
        return $this;
    }


    /**
     * @param $pageTitle
     * @param bool $addToBreadcrumbs
     * @return $this
     */
    public function page($pageTitle, $addToBreadcrumbs = true){
        $this->currentPageTitle=$pageTitle;
        if($addToBreadcrumbs){
            $this->addToBreadcrumbs($pageTitle,\URL::current());
        }

        return $this;
    }

    /**
     * @param $key
     * @return \IanRothmann\InertiaApp\Menu\MenuGroup
     */
    public function menu($key){
        if(!$this->menuContainer->has($key)){
            $this->menuContainer[$key]=new MenuGroup($this->menuItemGuardResolver);
        }
        return $this->menuContainer[$key];
    }

    /**
     * @param $label
     * @param null $icon
     * @param \Closure|string $itemAccessRight
     * @return \IanRothmann\InertiaApp\Menu\MenuGroup
     */
    public function menuGroup($label, $icon=null, $itemAccessRight=null){
        return MenuGroup::create($label,$icon)
            ->right($itemAccessRight);
    }

    /**
     * @param $closure
     * @return $this
     */
    public function resolveMenuItemRightsWith(\Closure $closure){
        $this->menuItemGuardResolver=$closure;
        return $this;
    }

    /**
     * @return $this
     */
    public function register(){
        $this->shareMenuData();
        $this->sharePageTitleData();
        $this->shareBreadcrumbData();
        $this->shareAuth();
        $this->shareErrorData();
        $this->shareSessionFlash();
        return $this;
    }

    public function shareMenuData(){
        Inertia::share('$menus',function(){
            return $this->menuContainer->toArray();
        });
    }

    public function shareAuth(){
        Inertia::share('$auth',function(){
            return $this->authData;
        });
    }

    public function sharePageTitleData(){
        Inertia::share('$title',function(){
            return $this->currentPageTitle;
        });
    }

    public function shareBreadcrumbData(){
        Inertia::share('$breadcrumbs',function(){
            return session($this->breadcrumbsSessionKey,[]);
        });
    }

    public function shareErrorData(){
        Inertia::share([
            '$errors' => function () {
                return Session::get('errors')
                    ? Session::get('errors')->getBag('default')->getMessages()
                    : (object) [];
            },
        ]);
    }

    public function shareSessionFlash(){
        Inertia::share('$flash', function () {
            if(Session::get('message')){
                return [
                    'message' => Session::get('message'),
                ];
            }elseif(Session::get('error')){
                return [
                    'error' => Session::get('error'),
                ];
            }elseif(Session::get('success')){
                return [
                    'success' => Session::get('success'),
                ];
            }
        });
    }

    public function clearNavigationHistory() {
        session()->forget(config('inertia-app.nav_history.session_key'));
        session()->forget('_previous');
    }
}