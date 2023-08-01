<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\BrowserKit\HttpBrowser;

class FetchNYTimesApiArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-n-y-times-api-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from the NYTimes API and store them in the database';

    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('NY_TIMES_API_KEY');
        $this->baseUrl = 'https://api.nytimes.com/svc/mostpopular/v2/';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endpoint = $this->baseUrl . 'viewed/1.json';
        $queryParams = [
            'api-key' => $this->apiKey,
        ];

        $response = Http::get($endpoint, $queryParams);

        if ($response->successful()) {
            $articles = $response->json()['results'];

            foreach ($articles as $articleData) {
                $article = new Article([
                    'source_id' => $articleData['id'],
                    'source_name' => $articleData['source'],
                    'author' => $articleData['byline'],
                    'title' => $articleData['title'],
                    'description' => $articleData['abstract'],
                    'url' => $articleData['url'],
                    'url_to_image' => $articleData['media'][0]['media-metadata'][0]['url'] ?? null,
                    'published_at' => $articleData['published_date'],
                    'content' => null, // Content will be fetched separately
                ]);
                $article->save();
            }
            $this->info('NYTimes API articles fetched and stored successfully');
        } else {
            $this->error('Failed to fetch NYTimes API articles: ' . $response->json()['message']);
        }

        $articlesWithNullContent = Article::whereNull('content')
            ->where('source_name', 'New York Times')
            ->get();
        foreach ($articlesWithNullContent as $article) {
            $article->content = $this->fetchContent($article->url);
            $article->save();
        }
    }

    private function fetchContent($url)
    {
        $browser = new HttpBrowser();
        $crawler = $browser->request('GET', $url);

        // Implement the logic to extract the content from the webpage using the Goutte library
        // example dom element that has the content: 
        // <div class="css-s99gbd StoryBodyCompanionColumn"><div class="css-53u6y8"><p class="css-at9mc1 evys1bk0">Lured by the promise of jobs, legal assistance and a more welcoming environment, hundreds of asylum seekers have boarded buses headed north to Albany, in search of a life better than they had found in New York City.</p><p class="css-at9mc1 evys1bk0">But once they settled in the state capital, many said they realized they had been misled and all but abandoned.</p><p class="css-at9mc1 evys1bk0">Instead of state identification cards, they were given dubious work eligibility and residency letters on what appeared to be a fake letterhead. At the bargain-rate motels where the migrants were relocated, many said they were treated like prisoners in halfway houses, living under written threats that they would be barred from seeking asylum if they were caught drinking or smoking.</p><p class="css-at9mc1 evys1bk0">They complained that crucial mail about their asylum cases had been lost, and worried that they now faced an hourslong trip to the courts where those cases will be heard.</p></div><aside class="css-ew4tgv" aria-label="companion column"></aside></div>

        $articleBodySection = $crawler->filter('section[name="articleBody"]');

        if ($articleBodySection->count() > 0) {
            $content = '';
            $paragraphs = $articleBodySection->filter('p');

            // Loop through the paragraphs and print their text content
            $paragraphs->each(function ($paragraph) use (&$content) {
                echo "Paragraph: {$paragraph->text()} \n";
                $content .= $paragraph->text() . "\n\n";
            });

            return $content;
        } else {
            echo "Article body element not found.\n";
            return 'No content found';
        }
    }
}
