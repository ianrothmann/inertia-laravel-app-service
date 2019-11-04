<?php


namespace IanRothmann\InertiaApp\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InertiaPageGenerator extends Command
{
    protected $signature = 'framework:view {name}';

    protected $description = 'Generates an Inertia View';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $name = $this->argument('name');

        $path = './resources/js/components/views/' . $name . '.vue';

        $page = $this->generatePage(basename($name));

        $this->makeDirectory($path);

        $this->files->put($path, $page);

        $this->info('Page generated!');
    }

    public function generatePage($name)
    {
        return <<<EOT
<template>
    <v-container fill-height>
        <v-col align-self="start">
            <v-row justify="center">
                
            </v-row>
        </v-col>
    </v-container>
</template>

<script>
    import AppNav from '../../layout/AppNav';
    
    export default{
        name: '{$name}',
        layout: AppNav,
        props: {},
        mixins: [],
        components: {},
        data: () => ({}),
        computed: {},
        mounted() {},
        methods: {}
    }
</script>

<style scoped>

</style>

EOT;
    }

    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
        return $path;
    }
}
