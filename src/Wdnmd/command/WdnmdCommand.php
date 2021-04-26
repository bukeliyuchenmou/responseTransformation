<?php


namespace Wdnmd\command;


use Illuminate\Console\Command;

class WdnmdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wdnmd:publish {--force : Overwrite any existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all of the Wdnmd resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'wdnmd-config',
            '--force' => $this->option('force'),
        ]);
    }
}