<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController
{
    /**
     * @Route("/index/fu")
     * @return Response
     */
    public function index(): Response
    {
        $checkit = 'aösflkhas';

        return new Response(
            "<html><body><h1>Fuck you! <br> $checkit</h1></body></html>"
        );
    }

}
