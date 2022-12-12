<?php

namespace Shanjing\LaravelStatistics\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\MountManager;

class PublishCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'laravel-statistics:publish
    {--force : Overwrite any existing files}
    {--migrations : Publish migrations files}
    {--config : Publish configuration files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Re-publish statistics configuration, migration files.
    If you want overwrite the existing files, you can add the `--force` option";

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var array
     */
    protected $tags = [];

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function handle()
    {
        $options = [];

        if ($this->option('force')) {
            $options['--force'] = true;
        }

        $tags = $this->getTags();

        foreach ($tags as $tag) {
            $this->call('vendor:publish', $options + ['--tag' => $tag]);
        }

        foreach ($this->tags as $tag) {
            $this->publishTag($tag);
        }

        $this->call('view:clear');
    }

    protected function getTags()
    {
        $tags = [];

        if ($this->option('migrations')) {
            $tags[] = 'shanjing-statistics-migrations';
        }

        if ($this->option('config')) {
            $tags[] = 'shanjing-statistics-config';
        }

        // 设置默认标签.
        if (! $tags && ! $this->tags) {
            $tags = [
                'shanjing-statistics-config',
                'shanjing-statistics-migrations',
            ];
        }

        return $tags;
    }

    protected function publishTag($tag)
    {
        $published = false;

        foreach ($this->pathsToPublish($tag) as $from => $to) {
            $this->publishItem($from, $to);

            $published = true;
        }

        if ($published) {
            $this->info('Publishing complete.');
        } else {
            $this->error('Unable to locate publishable resources.');
        }
    }

    protected function pathsToPublish($tag)
    {
        return ServiceProvider::pathsToPublish(null, $tag);
    }

    protected function publishItem($from, $to)
    {
        if ($this->files->isFile($from)) {
            $this->publishFile($from, $to);
        } elseif ($this->files->isDirectory($from)) {
            $this->publishDirectory($from, $to);
        }

        $this->error("Can't locate path: <{$from}>");
    }

    protected function publishFile($from, $to)
    {
        if (! $this->files->exists($to) || $this->option('force')) {
            $this->createParentDirectory(dirname($to));

            $this->files->copy($from, $to);

            $this->status($from, $to, 'File');
        }
    }

    protected function publishDirectory($from, $to)
    {
        $this->moveManagedFiles(new MountManager([
            'from' => new Flysystem(new LocalAdapter($from)),
            'to'   => new Flysystem(new LocalAdapter($to)),
        ]));

        $this->status($from, $to, 'Directory');
    }

    protected function moveManagedFiles($manager)
    {
        foreach ($manager->listContents('from://', true) as $file) {
            if (
                $file['type'] === strval('file')
                && (! $manager->has('to://' . $file['path']) || $this->option('force'))
                && ! $this->isExceptPath($manager, $file['path'])
            ) {
                $manager->put('to://' . $file['path'], $manager->read('from://' . $file['path']));
            }
        }
    }

    protected function isExceptPath($manager, $path)
    {
        return $manager->has('to://' . $path) && Str::contains($path, ['/menu.php', '/global.php']);
    }

    protected function createParentDirectory($directory)
    {
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }
    }

    protected function status($from, $to, $type)
    {
        $from = str_replace(base_path(), '', realpath($from));

        $to = str_replace(base_path(), '', realpath($to));

        $this->line('<info>Copied ' . $type . '</info> <comment>['
            . $from . ']</comment> <info>To</info> <comment>[' . $to . ']</comment>');
    }
}
