<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends AbstractController
{
    public function blog(BlogPostRepository $blogPostRepository): Response
    {

        return $this->render('blog.html.twig', [
            'posts' => $blogPostRepository->findAll(),
            'term' => '',
        ]);

    }

    public function blogSearch(Request $request, BlogPostRepository $blogPostRepository): Response
    {
        $term = $request->get('search', '');
        return $this->render('blog.html.twig', [
            'posts' => $term ? $blogPostRepository->findByTerm($term) : $blogPostRepository->findAll(),
            'term' => $term,
        ]);

    }
}