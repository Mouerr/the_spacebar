<?php
/**
 * Created by PhpStorm.
 * User: mo.errahmouni
 * Date: 12/03/2018
 * Time: 15:51
 */

namespace App\Controller;


use App\Entity\Article;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Nexy\Slack\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ArticleController extends AbstractController
{

    public function __construct(Client $slack)
    {

    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(ArticleRepository $repository)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $articles = $repository->findAllPublishedOrderedByNewest();

        return $this->render('article/homepage.html.twig', [
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/users/{username}", name="user_view")
     */
    public function viewUserAction(User $user)
    {
        if (!$this->isGranted('USER_VIEW', $user)) {
            throw $this->createAccessDeniedException('NO!');
        }
        dump('Access granted!', $user);
        die;
    }

    /**
     * @Route("/news/{slug}", name="article_show")
     */
    public function show(Article $article, MarkdownHelper $markdownHelper/*,EntityManagerInterface $em*/ /*,CommentRepository $commentRepository*/)
    {

        $article->getSlug();
        $markdownHelper->parse('bacon');
        //dump($article);die;
        //$comments = $article->getComments();
        //dump($comments);die;

        return $this->render('article/show.html.twig', [
            'article' => $article,
            //'comments' => $comments,
        ]);

        /*$html = $twigEnvirement->render('article/show.html.twig', ['title' => $name, 'name' => $name]);
        return new Response($html);*/
    }

    /**
     * @Route("/news/{slug}/heart",name="article_toggle_heart", methods={"POST"})
     */
    public function toggleArticleHeart(Article $article, LoggerInterface $logger, EntityManagerInterface $em)
    {

        $article->incrementHeartCount();
        $em->flush();

        return new JsonResponse(['hearts' => $article->getHeartCount()]);
    }
}