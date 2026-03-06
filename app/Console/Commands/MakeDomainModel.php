<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeDomainModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:domain-model {name : The name of the model} {domain? : The domain the model belongs to} {--m|migration : Create a new migration file for the model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Eloquent model class inside a specific Domain';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');

        if (!$domain) {
            $domain = $this->ask('Which domain does this model belong to? (e.g., Wallet, Identity)');
        }

        if (!$domain) {
            $this->error('A domain is required to create a domain model.');
            return self::FAILURE;
        }

        // Format Domain/Model namespace properly
        $domain = \Illuminate\Support\Str::studly($domain);
        $name = \Illuminate\Support\Str::studly($name);

        $modelPath = "\\App\\Domains\\{$domain}\\Models\\{$name}";

        $options = [];

        if ($this->option('migration')) {
            $options['--migration'] = true;
        }

        $this->info("Creating model {$name} in Domain: {$domain}");

        $this->call('make:model', array_merge([
            'name' => $modelPath,
        ], $options));

        return self::SUCCESS;
    }
}
