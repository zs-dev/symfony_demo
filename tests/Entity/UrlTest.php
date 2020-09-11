<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Url;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use App\DataFixtures\UrlFixture;

/**
 * @coversDefaultClass App\Entity\Url
 */
class UrlTest extends WebTestCase
{
    const UNIQUE_MESSAGE = 'Short url must be unique.';

    protected static $validator;
    protected static $entityManager;

    public function setUp(): void
    {
        self::bootKernel();

        // $container = self::$kernel->getContainer();

        $container = self::$container;
        self::$validator = $container->get('validator');
        self::$entityManager = $container->get('doctrine')->getManager();
        // self::$badSellerManager = self::$container->get('bvpluscore.bad_seller_manager');
        // self::$storeManager = self::$container->get('bvpluscore.store_manager');

        $loader = new ContainerAwareLoader($container);
        $loader->addFixture(new UrlFixture());
        (new ORMExecutor(self::$entityManager))->execute($loader->getFixtures(), true);
    }

    public function tearDown(): void
    {

    }

    /**
     * @test
     */
    public function shortUrlMustBeUnique(): void
    {
        $url = self::$entityManager->getRepository(Url::class)->findOneBy([]);
        $urlTest = (new Url())
                    ->setLongUrl('http://www.cnn.com')
                    ->setShortUrl($url->getShortUrl());
        $this->assertContains(self::UNIQUE_MESSAGE, self::$validator->validate($urlTest)->__toString());
    }
}
