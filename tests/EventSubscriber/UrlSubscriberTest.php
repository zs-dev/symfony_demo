<?php

declare(strict_types=1);

namespace App\Tests\EventSubscriber;

use App\Entity\Url;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use App\DataFixtures\UrlFixture;
use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManager;

/**
 * @coversDefaultClass App\EventSubscriber\UrlSubscriber
 */
class UrlSubscriberTest extends WebTestCase
{
    CONST REGEX = '/^[a-z\d]{5,9}$/';

    protected static UrlRepository $urlRepo;
    protected static EntityManager $em;

    public function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;
        self::$em = $container->get('doctrine')->getManager();
        self::$urlRepo = self::$em->getRepository(Url::class);

        $loader = new ContainerAwareLoader($container);
        $loader->addFixture(new UrlFixture());
        (new ORMExecutor(self::$em))->execute($loader->getFixtures(), true);
    }

    public function tearDown(): void
    {

    }

    /**
     * @test
     */
    public function onPrePersistShouldUpdateShortUrl(): void
    {
        $urlTest = (new Url())->setLongUrl('aaaaa');
        $this->assertNull($urlTest->getShortUrl());

        self::$em->persist($urlTest);

        $this->assertMatchesRegularExpression(self::REGEX, $urlTest->getShortUrl());
    }

    /**
     * @test
     */
    public function onFlushShouldUpdateShortUrlWhenAppropriate(): void
    {
        $url = self::$urlRepo->findOneBy([]);
        $oldShortUrl = $url->getShortUrl();
        $url->setShortUrl($oldShortUrl);
        self::$em->flush();
        $this->assertEquals($oldShortUrl, $url->getShortUrl());

        $url->setShortUrl('1111111');
        self::$em->flush();
        $this->assertNotEquals($oldShortUrl, $url->getShortUrl());
        $this->assertMatchesRegularExpression(self::REGEX, $url->getShortUrl());

        $oldShortUrl = $url->getShortUrl();
        $url->setLongUrl($url->getLongUrl() . 'new');
        self::$em->flush();
        $this->assertNotEquals($oldShortUrl, $url->getShortUrl());
        $this->assertMatchesRegularExpression(self::REGEX, $url->getShortUrl());
    }
}
