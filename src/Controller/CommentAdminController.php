<?php

namespace App\Controller;

use App\Repository\CommentRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentAdminController extends AbstractController
{
    /**
     * @Route("/admin/comment", name="comment_admin")
     */
    public function index(CommentRepository $repository, Request $request, PaginatorInterface $paginator)
    {
        $q = $request->query->get('q');
        //$comments = $repository->findBy([], ['createdAt' => 'DESC']);
        /*$comments = $repository->findAllWithSearch($q);
        return $this->render('comment_admin/index.html.twig', [
            'comments' => $comments,
        ]);*/

        $queryBuilder = $repository->getWithSearchQueryBuilder($q);
        $pagination = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        return $this->render('comment_admin/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
