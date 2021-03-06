<?php

namespace App\Http\Controllers\Frontend;

use Auth;
use App\Models\Tag;
use App\Models\Post;
use App\Models\User;
use App\Models\Settings;
use Illuminate\Http\Request;
use App\Jobs\TechsIndexData;
use App\Jobs\BlogFeedData;
use App\Jobs\BlogXmlData;
use App\Http\Controllers\Controller;
use App\Extensions\NewThemeManager;
use Carbon\Carbon;

class TechsController extends Controller
{
    /**
     * Display the blog index page.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tag = $request->get('tag');
        //$layout = $tag ? Tag::layout($tag)->first() : config('blog.tag_layout');
        $data = $this->dispatch(new TechsIndexData($tag));
        $layout = (new NewThemeManager(resolve('app'), resolve('files')))->getViewPath() . "frontend.blog.index";
        $socialHeaderIconsUser = User::where('id', Settings::socialHeaderIconsUserId())->first();
        $css = Settings::customCSS();
        $js = Settings::customJS();

        return view($layout, $data, compact('css', 'js', 'socialHeaderIconsUser'));
    }

    /**
     * Display a blog post page.
     *
     * @param $slug
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function showPost($slug, Request $request)
    {
        $post = Post::with('tags')->whereSlug($slug)->firstOrFail();
        $socialHeaderIconsUser = User::where('id', Settings::socialHeaderIconsUserId())->first();
        $user = User::where('id', $post->user_id)->firstOrFail();
        $tag = $request->get('tag');
        $title = $post->title;
        $css = Settings::customCSS();
        $js = Settings::customJS();

        if ($tag) {
            $tag = Tag::whereTag($tag)->firstOrFail();
        }

        if (! $post->is_published && ! Auth::check()) {
            return redirect()->route('canvas.blog.post.index');
        }

        $ad1 = $contents = Settings::ad1();
        $ad2 = $contents = Settings::ad2();
        $post->content_html = str_replace('<span id="ad1"></span>', $ad1, $post->content_html);
        $layout = (new NewThemeManager(resolve('app'), resolve('files')))->getViewPath() . "frontend.blog.post";
        return view($layout, compact('post', 'tag', 'slug', 'title', 'user', 'css', 'js', 'socialHeaderIconsUser'));
    }

    public function feed(Request $request)
    {
        $tag = $request->get('tag');
        $data = $this->dispatch(new BlogFeedData($tag));

        return response()->view('frontend.blog.feed', compact('data'))->header('Content-Type', 'application/rss+xml');
    }

    public function sitemap(Request $request)
    {
        $tag = null;
        $data = $this->dispatch(new BlogXmlData($tag));
        return response()->view('frontend.blog.sitemap', compact('data'))->header('Content-Type', 'application/xml');
    }
}
