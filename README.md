# RocketLaravelAppFramework
RocketFramework for Vue Laravel Helpers.

## Installation 

```php
composer install ianrothmann/inertia-laravel-app-service
```
In config/app.php (if it does not auto-configure)

Service provider 
```php
IanRothmann\InertiaApp\ServiceProviders\InertiaAppServiceProvider::class
```

Facades
```php
'Rocket' =>IanRothmann\InertiaApp\Facades\InertiaApp::class
```

Publish the config

```php
php artisan vendor:publish --provider="IanRothmann\InertiaApp\ServiceProviders\InertiaAppServiceProvider"  --tag="config"
```

## Configuration

In the `boot` method of `AppServiceProvider.php`:

```php
InertiaApp::register()->resolveMenuItemRightsWith(function($code){
            //The code that is required to resolve a menu right
            return Auth::check() && Auth::user()->hasRight($code);
        });
```

## Menus
Menus can be specified in middleware, but can also be modified in any controller before passing the view.

### Usage
You can give the menu a name, for instance "main", and then chain the items. The icon is optional. If you need a custom item, you can use `->custom`

```php
 $group=InertiaApp::menuGroup('Sub-menu','mdi-account')
            ->route('Page 1','pages.page1',[],'mdi-phone')
            ->link('Google','http://google.com','mdi-link');

        InertiaApp::menu('main')
            ->route('Item 1','pages.spec',[1,2],'mdi-home')
            ->route('Item 2','pages.spec',[2,1],'mdi-phone','test')
            ->route('Item 3','pages.spec',[9,8],'mdi-phone')
            ->group($group)
            ->link('Google','http://google.com');

```

### Prepending
Sometimes one would like to prepend items (especially when modifying middleware defined menus from the controller. All item functions can start with `prepend` to prepend.

```php
$group=InertiaApp::menuGroup(Auth::user()->name,'mdi-account')
            ->route('Update profile','pages.user.profile',[],'mdi-account-card-details-outline')
            ->route('Change password','pages.user.changepassword',[],'mdi-key-variant');

        InertiaApp::menu('main')
            ->prependGroup($group); //or prependRoute, prependCustom etc.
```
### Helpers
For brevity, we registered `iview()` which is the same as `Inertia::render()`.
You can also use `page_title('Page title here',$addTobreadcrumbs=true)` to set the page title and breadcrumbs.

### Front-end

Docs coming soon

### Breadcrumbs
Docs coming soon