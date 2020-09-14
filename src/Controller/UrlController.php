<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{ Response, Request };
use App\Entity\Url;
use App\Form\Type\UrlType;

class UrlController extends AbstractController
{
    /**
     * @Route("/{shortUrl}", methods="GET|POST", name="new_url")
     */
    public function index(Request $request, string $shortUrl = null): Response
    {
        $errors = [];

        if (!empty($shortUrl)) {
            $url = $this->getDoctrine()->getManager()->getRepository(Url::class)->findOneBy(['shortUrl' => $shortUrl]);

            if ($url instanceof Url) {
                return $this->redirect($url->getLongUrl());
            }

            $errors['search'] = 'Short url not found.';
        }

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
            'errors' => $errors,
        ]);
    }

    /**
     * @Route("/view/{shortUrl}", methods="GET", name="show_short_url")
     */
    public function viewShortUrl(string $shortUrl): Response
    {
        $errors = [];
        $url = $this->getDoctrine()->getManager()->getRepository(Url::class)->findOneBy(['shortUrl' => $shortUrl]);
        if (!$url instanceof Url) {
            $errors['search'] = 'Short url not found.';
        }

        return $this->render('view.html.twig', [
            'url' => $this->getDoctrine()->getManager()->getRepository(Url::class)->findOneBy(['shortUrl' => $shortUrl]),
            'errors' => $errors
        ]);
    }
}
