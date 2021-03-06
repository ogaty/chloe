<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Post;
use App\Models\Settings;
use Illuminate\Queue\SerializesModels;

/**
 * Class BlogXmlData.
 */
class BlogXmlData
{
    use SerializesModels;

    protected $tag;

    /**
     * Constructor.
     *
     * @param string|null $tag
     */
    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    /**
     * Execute the command.
     *
     * @return array
     */
    public function handle()
    {
        return $this->xmlData();
    }

    /**
     * Return data for normal index page.
     *
     * @return array
     */
    protected function xmlData()
    {
        $posts = Post::with('tags')
            ->where('published_at', '<=', Carbon::now())
            ->where('is_published', 1)
            ->orderBy('published_at', 'desc')
            ->get();

        $tags = Tag::all();

        return [
            'url' => env('APP_URL', 'http://blog.ogatism.com'),
            'posts' => $posts,
            'tags' => $tags,
        ];
    }
}
