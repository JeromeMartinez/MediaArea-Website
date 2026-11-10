<?php

namespace BlogBundle\Controller;

use LimitIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use AppBundle\Lib\Paginator;
use BlogBundle\Lib\Parser\PostParser;
use BlogBundle\Lib\Posts;
use BlogBundle\Lib\PostsTagFilter;

#[Route('/blog')]
class DefaultController extends AbstractController
{
    const POSTS_PER_PAGE = 10;

    #[Route('', name: 'ma_blog_index', defaults: ['page' => 1])]
    #[Route('/{page}', name: 'ma_blog_index_page', requirements: ['page' => '\d+'])]
    #[Template('@Blog/Default/index.html.twig')]
    public function indexAction(Request $request, int $page)
    {
        $posts = new Posts($this->getParameter('blog.files.path'));
        $showPosts = new LimitIterator(
            $posts,
            ($page - 1) * self::POSTS_PER_PAGE,
            self::POSTS_PER_PAGE
        );

        // Throw 404 if there is no post
        if (0 == iterator_count($showPosts)) {
            throw new NotFoundHttpException();
        }

        // Redirect paginated page 1 to index
        if ('ma_blog_index_page' == $request->attributes->get('_route') && 1 == $page) {
            return $this->redirectToRoute('ma_blog_index', [], 301);
        }

        $paginator = new Paginator(
            $posts->count(),
            self::POSTS_PER_PAGE,
            'ma_blog_index_page',
            'ma_blog_index',
            $page
        );

        return ['posts' => $showPosts, 'paginator' => $paginator];
    }

    #[Route('/feed', name: 'ma_blog_feed', defaults: ['_format' => 'xml'])]
    #[Template('@Blog/Default/feed.xml.twig')]
    public function feedAction()
    {
        $posts = new Posts($this->getParameter('blog.files.path'));
        $showPosts = new LimitIterator(
            $posts,
            0,
            self::POSTS_PER_PAGE
        );

        // Throw 404 if there is no post
        if (0 == iterator_count($showPosts)) {
            throw new NotFoundHttpException();
        }

        return ['posts' => $showPosts];
    }

    #[Route('/{tag}', name: 'ma_blog_tag_index', requirements: ['tag' => '[a-z0-9-]+'], defaults: ['page' => 1])]
    #[Route('/{tag}/{page}', name: 'ma_blog_tag_index_page', requirements: ['page' => '\d+', 'tag' => '[a-z0-9-]+'])]
    #[Template('@Blog/Default/listingByTag.html.twig')]
    public function listingByTagAction(Request $request, int $page, $tag)
    {
        $posts = new PostsTagFilter(new Posts($this->getParameter('blog.files.path')), $tag);
        $showPosts = new LimitIterator(
            $posts,
            ($page - 1) * self::POSTS_PER_PAGE,
            self::POSTS_PER_PAGE
        );

        // Throw 404 if there is no post
        if (0 == iterator_count($showPosts)) {
            throw new NotFoundHttpException();
        }

        // Redirect paginated page 1 to index
        if ('ma_blog_tag_index_page' == $request->attributes->get('_route') && 1 == $page) {
            return $this->redirectToRoute('ma_blog_tag_index', ['tag' => $tag], 301);
        }

        $paginator = new Paginator(
            iterator_count($posts),
            self::POSTS_PER_PAGE,
            'ma_blog_tag_index_page',
            'ma_blog_tag_index',
            $page
        );
        $paginator->setPageRouteParams(['tag' => $tag]);

        // Get first result to get tag (instead of slug)
        $showPosts->rewind();
        $post = $showPosts->current();

        return ['posts' => $showPosts, 'paginator' => $paginator, 'tag' => $post->getTagBySlug($tag)];
    }

    #[Route(
        '/{tag}/feed',
        name: 'ma_blog_tag_feed',
        requirements: ['tag' => '[a-z0-9-]+'],
        defaults: ['_format' => 'xml'],
    )]
    #[Template('@Blog/Default/listingByTagFeed.xml.twig')]
    public function listingByTagFeedAction($tag)
    {
        $posts = new PostsTagFilter(new Posts($this->getParameter('blog.files.path')), $tag);
        $showPosts = new LimitIterator(
            $posts,
            0,
            self::POSTS_PER_PAGE
        );

        // Throw 404 if there is no post
        if (0 == iterator_count($showPosts)) {
            throw new NotFoundHttpException();
        }

        // Get first result to get tag (instead of slug)
        $showPosts->rewind();
        $post = $showPosts->current();

        return ['posts' => $showPosts, 'tag' => $post->getTagBySlug($tag)];
    }

    #[Route('/{year}/{month}/{day}/{slug}', name: 'ma_blog_post')]
    #[Template('@Blog/Default/post.html.twig')]
    public function postAction($year, $month, $day, $slug)
    {
        try {
            $postParser = new PostParser(
                $this->getParameter('blog.files.path').$year.'-'.$month.'-'.$day.'-'.$slug.'.md'
            );
        } catch (\Exception $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return ['post' => $postParser->getPost()];
    }
}
