<?php

namespace App\Console\Commands;

use App\Services\ArticleManagerService;
use Illuminate\Console\Command;

class CollectNews extends Command
{
    protected $signature = 'news:collect {api}';
    protected $description = 'Fetch articles from the specified API and store them in the database';

    protected ArticleManagerService $articleManager;

    public function __construct(ArticleManagerService $articleManager)
    {
        parent::__construct();
        $this->articleManager = $articleManager;
    }

    public function handle()
    {
        $apiName = $this->argument('api');

        try {
            $this->articleManager->fetchAndProcessArticles($apiName);

            $this->info("Articles from {$apiName} fetched and stored successfully.");
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }
    }
}
