<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\PostType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', name: 'index')]
    public function index(Request $request, SluggerInterface $slugger, PaginatorInterface $paginator): Response
    {
        $post = new Post();
        $query = $this->em->getRepository(Post::class)->findAllPosts();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            2 /*limit per page*/
        );

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $form->get('file')->getData();
            $url = str_replace(" ", "-", $form->get('title')->getData());

            if ($file){
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('files_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    throw new \Exception('Ups there is a problem with your file');
                }
                $post->setFile($newFilename);
            }

            $post->setUrl($url);
            $user = $this->em->getRepository(User::class)->find(1);
            $post->setUser($user);
            $this->em->persist($post);
            $this->em->flush();
            return $this->redirectToRoute('index');
        }


        return $this->render('post/index.html.twig', [
            'form' => $form->createView(),
            'posts' => $pagination
        ]);
    }

    #[Route('/post/details/{id}', name: 'postDetails')]
    public function postDetails(Post $post){
        return $this->render('post/post-details.html.twig', ['post' => $post]);
    }



    // #[Route('/post/{id}', name: 'app_post')]
    // public function index($id): Response
    // {
    //     // $post = $this->em->getRepository(Post::class)->find($id) Trae los datos dependiendo el id parametro que le pasemos
    //     // $post = $this->em->getRepository(Post::class)->findAll(); Trae todos los datos de la tabla Post
    //     // $post = $this->em->getRepository(Post::class)->findBy(['title' => 'Post de prueba']); Trae los datos por dependiendo el campo que pasemosy trae todos los datos del campo pasa paremetro
    //     // $post = $this->em->getRepository(Post::class)->findOneBy(['id' => 2]); Este trae los datos por ID
    //     // $custom_post = $this->em->getRepository(Post::class)->findPost($id);
    //     $post = $this->em->getRepository(Post::class)->find($id);
    //     $custom_post = $this->em->getRepository(Post::class)->findPost($id);
    //     return $this->render('post/index.html.twig', [
    //         'post' => $post,
    //         'custom_post' => $custom_post
    //     ]);
    // }

    // #[Route('/insert/post', name: 'insert_post')]
    // public function insert(){
    //     $post = new Post('Post Insertado', 'Opinion', 'Post prueba', 'prueba.jpg', 'url-prueba');
    //     $user = $this->em->getRepository(User::class)->find(1);
    //     $post->setUser($user);
    //     $this->em->persist($post);
    //     $this->em->flush();
    //     return new JsonResponse(['success' => true]);
    // }

    // #[Route('/update/post', name: 'update_post')]
    // public function update(){
    //     $post = $this->em->getRepository(Post::class)->find(5);
    //     $post->setTitle('Titulo update');
    //     $this->em->flush();
    //     return new JsonResponse(['success' => true]);
    // }

    // #[Route('/remove/post', name: 'remove_post')]
    // public function remove(){
    //     $post = $this->em->getRepository(Post::class)->find(5);
    //     $this->em->remove($post);
    //     $this->em->flush();
    //     return new JsonResponse(['success' => true]);
    // }
}
