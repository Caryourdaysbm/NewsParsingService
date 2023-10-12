<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use App\Models\NewsArticle;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Cache\RateLimiter;

class ParseNews extends Command
{
    protected $signature = 'parse:news';
    protected $description = 'Fetch and parse news articles from an RSS feed.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Rate Limiting Configuration
        $key = 'news_parsing'; // Unique identifier for the rate limiter
        $maxAttempts = 10; // Maximum allowed attempts
        $decayMinutes = 1; // Time window (in minutes)

        $limiter = app(RateLimiter::class);

        if ($limiter->tooManyAttempts($key, $maxAttempts, $decayMinutes)) {
            $this->error('Too Many Attempts.'); // Return an error message
            return;
        }

        $limiter->hit($key, $decayMinutes);

        // RSS Feed URL
        $rssFeedUrl = 'https://saharareporters.com/articles/rss-feed';

        try {
            $response = Http::timeout(60)->get($rssFeedUrl);
            $feedContent = $response->body();

            $xml = simplexml_load_string($feedContent);

            if ($xml) {
                foreach ($xml->channel->item as $item) {
                    $title = (string)$item->title;
                    $description = (string)$item->description;
                    $link = (string)$item->link;
                    $dateAdded = date('Y-m-d H:i:s', strtotime((string)$item->pubDate));

                    // Check for duplicate articles
                    $article = NewsArticle::firstOrNew(['title' => $title]);

                    if (!$article->exists) {
                        $article->description = $description;
                        $article->link = $link;
                        $article->date_added = $dateAdded;
                        $article->save();

                        // Send notification through Telegram bot
                        $this->sendTelegramNotification($title, $description, $link);
                    }
                }

                $this->info('News parsing completed successfully.');
            } else {
                $this->error('Failed to parse the RSS feed.');
            }
        } catch (\Exception $e) {
            Log::error('News parsing failed: ' . $e->getMessage());
            $this->error('News parsing failed. Check the log for details.');
        }
    }

    private function sendTelegramNotification($title, $description, $link)
    {
        // Telegram Configuration
        $message = "New article: $title\nDescription: $description";

        $chatId = config('app.telegram_chat_id'); // Fetch the chat ID from configuration

        try {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
            ]);
        } catch (\Exception $e) {
            // Handle any errors or exceptions here, e.g., log the error.
            Log::error('Telegram notification failed: ' . $e->getMessage());
        }
    }
}
