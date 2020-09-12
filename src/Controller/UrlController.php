<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{ Response, Request };
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Url;
use App\Form\Type\UrlType;

class UrlController extends AbstractController
{
    /**
     * @Route("/", methods="GET|POST", name="new_url")
     */
    public function index(Request $request): Response
    {
        $url = new Url();

        $form = $this->createForm(UrlType::class, $url);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $url = $form->getData();


$this->getDoctrine()->getManager()->persist($url);
$this->getDoctrine()->getManager()->flush();


            return $this->redirectToRoute('show_short_url', ['shortUrl' => $url->getShortUrl()]);
        }


        return $this->render('new_url.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/view/{shortUrl}", methods="GET", name="show_short_url")
     */
    public function viewShortUrl(string $shortUrl): Response
    {
return new Response('Hello there', Response::HTTP_OK);
    }
}
