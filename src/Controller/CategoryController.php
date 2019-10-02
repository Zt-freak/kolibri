<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Post;
use App\Form\CategoryType;
use App\Form\CommentType;
use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Knp\Component\Pager\PaginatorInterface;

class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="category_index", methods={"GET"})
     */
    public function index(CategoryRepository $categoryRepository, PostRepository $postRepository): Response
    {
        $categories = $categoryRepository->findAll();

        $categoryData = [];
        $i = 0;
        foreach ($categories as &$category) {
            $postCount = $postRepository->countByCategoryNoParent($category);
            array_push($categoryData, [
                "id" => $category->getId(),
                "name" => $category->getName(),
                "description" => $category->getDescription(),
                "postCount" => $postCount
            ]);
            $i++;
        }

        return $this->render('category/index.html.twig', [
            'categories' => $categoryData,
        ]);
    }

    /**
     * @Route("category/new", name="category_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("category/{id}", name="category_show", methods={"GET"})
     */
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("category/{id}/posts", name="category_posts", methods={"GET", "POST"})
     */
    public function posts(Request $request, Category $category, PostRepository $postRepository, PaginatorInterface $paginator): Response
    {
        $newPost = new Post();
        $newPost->setCategory($category);
        $newPost->setUser($this->getUser());

        if ($category->getApproval() == 1) {
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
        }


        $posts = $paginator->paginate(
            $postRepository->findByCategoryNoParent($category), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        
        return $this->render('category/posts.html.twig', [
            'form' => $form->createView(),
            'posts' => $posts,
        ]);
    }

    /**
     * @Route("category/{id}/edit", name="category_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("category/{id}", name="category_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('category_index');
    }
}
