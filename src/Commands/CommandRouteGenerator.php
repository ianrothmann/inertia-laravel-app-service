<?php
/**
 * Created by PhpStorm.
 * User: ianrothmann
 * Date: 10/26/19
 * Time: 3:23 PM
 */

namespace IanRothmann\InertiaApp\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Routing\Router;
use Tightenco\Ziggy\RoutePayload;
use Tightenco\Ziggy\Ziggy;

class CommandRouteGenerator extends Command
{
    protected $signature = 'framework:routes {path=./resources/js/ziggy.js} {--url=/} {--group=}';

    protected $description = 'Generate js file for including in build process';

    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $path = $this->argument('path');
        $generatedRoutes = $this->generate($this->option('group'));

        $this->makeDirectory($path);
        $this->files->put(base_path($path), $generatedRoutes);

        $this->info('File generated!');
    }

    private function generate($group = false)
    {
        $payload = (new Ziggy($group, $this->option('url') ? url($this->option('url')) : null))->toJson();

        return <<<JAVASCRIPT
const Ziggy = {$payload};

let baseUrl = location.protocol + '//' + location.hostname;
if(location.port) {
    baseUrl += (':' + location.port)
}
Ziggy.url = baseUrl;
Ziggy.port = location.port;

if (typeof window !== 'undefined' && typeof window.Ziggy !== 'undefined') {
    Object.assign(Ziggy.routes, window.Ziggy.routes);
}

export { Ziggy };

JAVASCRIPT;
    }

    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname(base_path($path)))) {
            $this->files->makeDirectory(dirname(base_path($path)), 0755, true, true);
        }

        return $path;
    }
}