<?php
/**
 * HelloWorldController.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HelloWorldController.
 *
 * @Route("/")
 */
class HelloWorldController extends AbstractController
{
    /**
     * Index action.
     *
     * @return Response HTTP response
     *
     * @Route(
     *     "/",
     *      name="home_index",
     * )
     */
    public function index(): Response
    {
        return $this->render(
            'hello-world/show.html.twig'
        );
    }
}
