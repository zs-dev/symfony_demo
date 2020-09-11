<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Url;

class UrlController extends AbstractController
{
    /**
     * @Route("/", methods="GET|POST", name="url_form")
     */
    public function index(ValidatorInterface $validator): Response
    {
        $url = new Url();
        $url->setShortUrl('x');
        $errors = $validator->validate($url);
        dump($errors->__toString());
        die;
        return new Response('Hello there', Response::HTTP_OK);
    }
}
