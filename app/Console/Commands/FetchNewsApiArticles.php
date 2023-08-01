<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\BrowserKit\HttpBrowser;

class FetchNewsApiArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-news-api-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from the News API and store them in the database';

    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('NEWS_API_KEY');
        $this->baseUrl = 'https://newsapi.org/v2/';
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endpoint = $this->baseUrl . 'top-headlines';
        $queryParams = [
            'apiKey' => $this->apiKey,
            'sources' => 'bbc-news, espn', //arbitrary values I chose for this API
        ];

        $response = Http::get($endpoint, $queryParams);

        if ($response->successful()) {
            $articles = $response->json()['articles'];

            foreach ($articles as $articleData) {
                $sourceName = $articleData['source']['name'];
                $sourceUrl = '';
                $categoryName = '';
                if ($sourceName === 'ESPN') {
                    $categoryName = 'Sports';
                    $sourceUrl = 'https://www.espn.com/';
                } elseif ($sourceName === 'BBC News') {
                    $categoryName = 'General';
                    $sourceUrl = 'https://www.bbc.co.uk/';
                }

                $source = Source::firstOrCreate(['name' => $sourceName, 'description' => $sourceName, 'url' => $sourceUrl]);
                $category = Category::firstOrCreate(['name' => $categoryName]);
                $article = new Article([
                    'source_id' => $source->id,
                    'source_name' => $sourceName,
                    'category_id' => $category->id,
                    'author' => $articleData['author'] ?? $sourceName,
                    'title' => $articleData['title'],
                    'description' => $articleData['description'],
                    'url' => $articleData['url'],
                    'url_to_image' => $articleData['urlToImage'],
                    'published_at' => $articleData['publishedAt'],
                    'content' => null, // Content will be fetched separately
                ]);
                $source->articles()->save($article);
            }
            $this->info('News API articles fetched and stored successfully');
        } else {
            $this->error('Failed to fetch News API articles: ' . $response->json()['message']);
        }

        $articlesWithNullContent = Article::whereNull('content')
            ->whereIn('source_name', ['BBC News', 'ESPN'])->get();
        foreach ($articlesWithNullContent as $article) {
            $article->content = $this->fetchContent($article->url, $article->source_name);
            $article->save();
        }
    }

    private function fetchContent($url, $sourceName)
    {
        $browser = new HttpBrowser();
        $crawler = $browser->request('GET', $url);

        // Implement the logic to extract the content from the webpage using the Goutte library
        // example dom element that has the content from the BBC News website: 
        // <div data-component="text-block" class="ssrcss-11r1m41-RichTextComponentWrapper ep2nwvo0"><div class="ssrcss-7uxr49-RichTextContainer e5tfeyi1"><p class="ssrcss-1q0x1qg-Paragraph eq5iqo00">People in the town shared photos of the approaching fire on social media, including the image above.</p></div></div> 
        // example dom element that has the content from the ESPN website:
        //<div class="Story__Body t__body"><p>LAS VEGAS -- <a href="http://www.espn.com/soccer/team?id=360">Manchester United</a> ended their tour of the United States with a 3-2 defeat to <a href="http://www.espn.com/soccer/team?id=124">Borussia Dortmund</a> at Allegiant Stadium.</p><p>Erik ten Hag's team were made to pay for a poor defensive performance as they fell to their third defeat in four games in the U.S.</p></div>

        $content = '';

        if ($sourceName === 'BBC News') {
            $textBlocks = $crawler->filter('[data-component="text-block"], .story-body, article');
            $textBlocks->each(function ($textBlock) use (&$content) {
                $paragraphs = $textBlock->filter('p');
                $paragraphs->each(function ($paragraph) use (&$content) {
                    $content .= $paragraph->text() . "\n\n";
                });
            });
        } elseif ($sourceName === 'ESPN') {
            $storyBody = $crawler->filter('.Story__Body, .article-body');
            $paragraphs = $storyBody->filter('p');
            $paragraphs->each(function ($paragraph) use (&$content) {
                $content .= $paragraph->text() . "\n\n";
            });
        } else {
            $content = 'Content extraction not implemented for this source.';
        }

        return $content;
    }
}
