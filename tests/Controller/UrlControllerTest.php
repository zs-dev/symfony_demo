<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Url;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @coversDefaultClass App\Controller\UrlController
 */
class UrlControllerTest extends WebTestCase
{

    protected static RecursiveValidator $validator;
    protected static EntityManager $entityManager;
    protected static KernelBrowser $client;

    public function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;
        self::$validator = $container->get('validator');
        self::$entityManager = $container->get('doctrine')->getManager();
        self::$client = $container->get('test.client');
    }

    public function tearDown(): void
    {

    }

    /**
     * @test
     */
    public function x(): void
    {

        $x = self::$client->request('GET', '/');
dump($x->text());
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
    }
}
