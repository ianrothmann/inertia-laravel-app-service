<?php


namespace IanRothmann\InertiaApp\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MenuGenerator extends Command
{
    protected $signature = 'framework:menu {name}';

    protected $description = 'Generates a Menu';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');

        $path = './app/Http/Middleware/Menus/' . $name . '.php';

        $menu = $this->generateMenu($name);

        $this->makeDirectory($path);

        $this->files->put($path, $menu);

        $this->info('Menu generated!');
    }

    public function generateMenu($name)
    {
        $explode = explode("/", $name);
        $className = array_pop($explode);
        $namespace = implode("\\", $explode);
        return <<<EOT
<?php

namespace $namespace;

use IanRothmann\InertiaApp\Menu\MenuGroup;
use IanRothmann\InertiaApp\Middleware\Menu;

class $className extends Menu
{
    public function menu(MenuGroup \$menu): void
    {
    
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
EOT;
    }

    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
        return $path;
    }
}
