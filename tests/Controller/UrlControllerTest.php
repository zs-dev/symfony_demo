<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Url;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use App\DataFixtures\UrlFixture;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @coversDefaultClass App\Controller\UrlController
 */
class UrlControllerTest extends WebTestCase
{
    const LONG_URL_VALID_URL = 'The url is not a valid url.';
    const LONG_URL_MAX_RANGE = 'Long url cannot be more than 300 characters.';
    const LONG_URL_MANDATORY = 'Long url is mandatory.';
    const VIEW_FAILED = 'Short url not found.';

    protected static UrlRepository $urlRepo;
    protected static KernelBrowser $client;

    public function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;
        $em = $container->get('doctrine')->getManager();
        self::$urlRepo = $em->getRepository(Url::class);
        self::$client = $container->get('test.client');

        $loader = new ContainerAwareLoader($container);
        $loader->addFixture(new UrlFixture());
        (new ORMExecutor($em))->execute($loader->getFixtures(), true);
    }

    public function tearDown(): void
    {

    }

    /**
     * @test
     */
    public function formWorksAsExpected(): void
    {
        $this->assertEquals(1, count(self::$urlRepo->findBy([])));
        $crawler = self::$client->request('GET', '/');
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Save')->form();
        $crawler = self::$client->submit($form);
        $this->assertStringContainsString(self::LONG_URL_MANDATORY, $crawler->text());
        $this->assertEquals(1, count(self::$urlRepo->findBy([])));

        $form[$crawler->filterXPath("//input[contains(@name, 'longUrl')]")->attr('name')]->setValue('fail');
        $crawler = self::$client->submit($form);

        $this->assertStringContainsString(self::LONG_URL_VALID_URL, $crawler->text());
        $this->assertEquals(1, count(self::$urlRepo->findBy([])));

        $form[$crawler->filterXPath("//input[contains(@name, 'longUrl')]")->attr('name')]->setValue(str_repeat('a', 301));
        $crawler = self::$client->submit($form);

        $this->assertStringContainsString(self::LONG_URL_MAX_RANGE, $crawler->text());
        $this->assertEquals(1, count(self::$urlRepo->findBy([])));


        $longUrl = 'https://cnn.com';
        $form[$crawler->filterXPath("//input[contains(@name, 'longUrl')]")->attr('name')]->setValue($longUrl);
        $crawler = self::$client->submit($form);

        $this->assertEquals(302, self::$client->getResponse()->getStatusCode());

        $url = self::$urlRepo->findOneBy(['longUrl' => $longUrl]);
        $this->assertInstanceOf('\App\Entity\Url', $url);
        $this->assertStringContainsString(sprintf('Redirecting to /view/%s.', $url->getShortUrl()), $crawler->text());
        $crawler = self::$client->followRedirect();
        $this->getSharedViewAssertions($crawler, $url, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function searchWorksAsExpected(): void
    {
        $search = 'doesnotexist';
        $this->assertNull(self::$urlRepo->findOneBy(['shortUrl' => $search]));

        $crawler = self::$client->request('GET', '/' . $search);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::VIEW_FAILED, $crawler->text());

        $url = self::$urlRepo->findOneBy([]);
        $crawler = self::$client->request('GET', '/' . $url->getShortUrl());
        $this->assertEquals(302, self::$client->getResponse()->getStatusCode());

        $this->assertStringContainsString(sprintf('Redirecting to %s.', $url->getLongUrl()), $crawler->text());
        $crawler = self::$client->followRedirect();
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function viewWorksAsExpected(): void
    {
        $search = 'doesnotexist';
        $this->assertNull(self::$urlRepo->findOneBy(['shortUrl' => $search]));

        $crawler = self::$client->request('GET', '/view/' . $search);
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());
        $this->assertStringContainsString(self::VIEW_FAILED, $crawler->text());

        $url = self::$urlRepo->findOneBy([]);
        $crawler = self::$client->request('GET', '/view/' . $url->getShortUrl());

        $this->getSharedViewAssertions($crawler, $url, self::$client->getResponse()->getStatusCode());

    }

    private function getSharedViewAssertions(Crawler $crawler, Url $url, int $status): void
    {
        $this->assertEquals(200, $status);
        $this->assertStringNotContainsString(self::VIEW_FAILED, $crawler->text());
        $this->assertStringContainsString('Long Url: ' . $url->getLongUrl(), $crawler->text());
        $this->assertStringContainsString('Short Url: ' . $url->getShortUrl(), $crawler->text());
        $this->assertMatchesRegularExpression(
            '/Date Created: [0-9]{4}-[0-9]{1,2}-[0-9]{1,2} [0-9]{2}:[0-9]{2}:[0-9]{2}/',
            $crawler->text()
        );
    }
}
