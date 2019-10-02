<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/post")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="post_index", methods={"GET"})
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

    /**
     * @Route("/unapproved", name="post_unapproved", methods={"GET"})
     */
     public function unapproved(PostRepository $postRepository): Response
     {
         return $this->render('post/index.html.twig', [
             'posts' => $postRepository->findNotApproved(),
         ]);
     }

    /**
     * @Route("/new", name="post_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('post_index');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Post $post, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        $newPost = new Post();
        $newPost->setParent($postRepository->findById($post->getId())[0]);
        $newPost->setCategory($post->getCategory());
        $newPost->setUser($this->getUser());

        if ($post->getCategory()->getApproval() == 1) {
            $newPost->setApproved(0);
        }
        else {
            $newPost->setApproved(1);
        }

        $form = $this->createForm(CommentType::class, $newPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newPost);
            $entityManager->flush();

            $post = $newPost;
        }

        $parent = $postRepository->findById($post->getParent());

        $children = $paginator->paginate(
            $postRepository->findByParent($post),
            $request->query->getInt('page', 1),
            10/*limit per page*/
        );

        return $this->render('post/show.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'parent' => $parent,
            'children' => $children,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="post_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('post_show', ['id' => $post]);
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="post_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('post_index');
    }
    
}
