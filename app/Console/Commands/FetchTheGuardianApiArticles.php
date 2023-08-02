<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchTheGuardianApiArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-the-guardian-api-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from the The Guardian API and store them in the database';

    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('THE_GUARDIAN_API_KEY');
        $this->baseUrl = 'https://content.guardianapis.com/';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endpoint = $this->baseUrl . 'search';
        $queryParams = [
            'api-key' => $this->apiKey,
            'show-fields' => 'headline,byline,bodyText,thumbnail,trailText',
            'page-size' => 50,
        ];

        $response = Http::get($endpoint, $queryParams);

        if ($response->successful()) {
            $articles = $response->json()['response']['results'];

            foreach ($articles as $articleData) {
                $sourceName = 'The Guardian';
                $source = Source::firstOrCreate(['name' => $sourceName, 'description' => 'The Guardian', 'url' => 'https://www.theguardian.com/']);
                $categoryName = $articleData['sectionName'];
                $category = Category::firstOrCreate(['name' => $categoryName]);

                $article = new Article([
                    'source_id' => $source->id,
                    'source_name' => 'The Guardian',
                    'category_id' => $category->id,
                    'author' => $articleData['fields']['byline'] ?? 'The Guardian',
                    'title' => $articleData['fields']['headline'],
                    'description' => $articleData['fields']['trailText'],
                    'url' => $articleData['webUrl'],
                    'url_to_image' => $articleData['fields']['thumbnail'] ?? null,
                    'published_at' => $articleData['webPublicationDate'],
                    'content' => $articleData['fields']['bodyText'] ?? null
                ]);
                $article->save();
            }
            $this->info('The Guardian API articles fetched and stored successfully');
        } else {
            $this->error('Failed to fetch The Guardian API articles: ' . $response->json()['message']);
        }
    }
}
