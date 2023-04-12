<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Form\BlogPostType;
use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/blog/post')]
class BlogPostController extends AbstractController
{
    #[Route('/', name: 'app_blog_post_index', methods: ['GET'])]
    public function index(BlogPostRepository $blogPostRepository): Response
    {
        return $this->render('blog_post/index.html.twig', [
            'blog_posts' => $blogPostRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_blog_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BlogPostRepository $blogPostRepository, SluggerInterface $slugger): Response
    {
        $blogPost = new BlogPost();
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagenFile = $form->get('imagen')->getData();

            // this condition is needed because the 'blog image' field is not required
            // so the img file must be processed only when a file is uploaded
            if ($imagenFile) {
                if ( !empty($blogPost->getImgPath()) ) {
                    unlink($blogPost->getImgPath());
                }
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imagenFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imagenFile->move(
                        $this->getParameter('blog_post_image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $this->render('blog_post/new.html.twig', [
                        'blog_post' => $blogPost,
                        'form' => $form,
                        'error' => $e->getMessage(),
                    ]);
                }

                $blogPost->setImgPath($newFilename);
            }

            /*$tags = $blogPost->getTags();
            $blogPost->setTags(str_replace(',', ' ', $tags))*/;

            $blogPostRepository->save($blogPost, true);

            return $this->redirectToRoute('app_blog_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog_post/new.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_post_show', methods: ['GET'])]
    public function show(BlogPost $blogPost): Response
    {
        return $this->render('blog_post/show.html.twig', [
            'blog_post' => $blogPost,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlogPost $blogPost, BlogPostRepository $blogPostRepository, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(BlogPostType::class, $blogPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $imagenFile = $form->get('imagen')->getData();

            // this condition is needed because the 'blog image' field is not required
            // so the img file must be processed only when a file is uploaded
            if ($imagenFile) {
                if ( !empty($blogPost->getImgPath()) ) {
                    unlink($this->getParameter('blog_post_image_directory').'/'.$blogPost->getImgPath());
                }
                $originalFilename = pathinfo($imagenFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imagenFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $imagenFile->move(
                        $this->getParameter('blog_post_image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $this->render('blog_post/new.html.twig', [
                        'blog_post' => $blogPost,
                        'form' => $form,
                        'error' => $e->getMessage(),
                    ]);
                }

                $blogPost->setImgPath($newFilename);
            }

            $blogPostRepository->save($blogPost, true);

            return $this->redirectToRoute('app_blog_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog_post/edit.html.twig', [
            'blog_post' => $blogPost,
            'form' => $form,
            'blog_img' => $blogPost->getImgPath(),
        ]);
    }

    #[Route('/{id}', name: 'app_blog_post_delete', methods: ['POST'])]
    public function delete(Request $request, BlogPost $blogPost, BlogPostRepository $blogPostRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blogPost->getId(), $request->request->get('_token'))) {
            $blogPostRepository->remove($blogPost, true);
        }

        return $this->redirectToRoute('app_blog_post_index', [], Response::HTTP_SEE_OTHER);
    }
}
