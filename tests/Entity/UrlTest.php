<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Url;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use App\DataFixtures\UrlFixture;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManager;

/**
 * @coversDefaultClass App\Entity\Url
 */
class UrlTest extends WebTestCase
{
    const UNIQUE_MESSAGE = 'Short url must be unique.';
    const SHORT_URL_MIN_RANGE = 'Short url cannot be less than 5 characters.';
    const SHORT_URL_MAX_RANGE = 'Short url cannot be more than 9 characters.';
    const SHORT_URL_MANDATORY = 'Short url is mandatory.';
    const LONG_URL_VALID_URL = 'The url is not a valid url.';
    const LONG_URL_MAX_RANGE = 'Long url cannot be more than 300 characters.';
    const LONG_URL_MANDATORY = 'Long url is mandatory.';

    protected static RecursiveValidator $validator;
    protected static UrlRepository $urlRepo;
    protected static EntityManager $em;

    public function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;
        self::$validator = $container->get('validator');
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
     * @coversNothing
     */
    public function validationGroupsProvider(): array
    {
        return [['Default'], ['NewUrl']];
    }

    /**
     * @test
     */
    public function validationGroupsProviderShouldBePopulated(): void
    {
        $groups = $this->validationGroupsProvider();

        $this->assertEquals(2, count($groups));
        $this->assertEquals('Default', $groups[0][0]);
        $this->assertEquals('NewUrl', $groups[1][0]);
    }

    /**
     * @test
     */
    public function shortUrlMustBeUnique(): void
    {
        $url = self::$urlRepo->findOneBy([]);
        $this->assertInstanceOf('\App\Entity\Url', $url);

        $urlTest = (new Url())->setShortUrl($url->getShortUrl());
        $this->assertStringContainsString(self::UNIQUE_MESSAGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setShortUrl($url->getShortUrl() . 'x');
        $this->assertStringNotContainsString(self::UNIQUE_MESSAGE, self::$validator->validate($urlTest)->__toString());
    }

    /**
     * @test
     */
    public function shortUrlMustEnforceRange(): void
    {
        $urlTest = (new Url())->setShortUrl('');
        $this->assertStringContainsString(self::SHORT_URL_MIN_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest = (new Url())->setShortUrl('a');
        $this->assertStringContainsString(self::SHORT_URL_MIN_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setShortUrl('aaaaa');
        $this->assertStringNotContainsString(self::SHORT_URL_MIN_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest = (new Url())->setShortUrl('aaaaaaaaaaa');
        $this->assertStringContainsString(self::SHORT_URL_MAX_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setShortUrl('aaaaa');
        $this->assertStringNotContainsString(self::SHORT_URL_MAX_RANGE, self::$validator->validate($urlTest)->__toString());
    }

    /**
     * @test
     */
    public function shortUrlMustBePopulated(): void
    {
        $urlTest = new Url();
        $this->assertStringContainsString(self::SHORT_URL_MANDATORY, self::$validator->validate($urlTest)->__toString());

        $urlTest->setShortUrl('aaaaa');
        $this->assertStringNotContainsString(self::SHORT_URL_MANDATORY, self::$validator->validate($urlTest)->__toString());
    }



    /**
     * @test
     * @dataProvider validationGroupsProvider
     */
    public function longUrlMustBePopulated($validationGroup): void
    {
        $urlTest = new Url();
        $this->assertStringContainsString(
            self::LONG_URL_MANDATORY,
            self::$validator->validate($urlTest,
                null,
                [$validationGroup])->__toString()
        );

        $urlTest->setLongUrl('');
        $this->assertStringContainsString(
            self::LONG_URL_MANDATORY,
            self::$validator->validate($urlTest,
                null,
                [$validationGroup])->__toString()
        );

        $urlTest->setLongUrl('aaaaa');
        $this->assertStringNotContainsString(
            self::LONG_URL_MANDATORY,
            self::$validator->validate($urlTest,
                null,
                [$validationGroup])->__toString()
        );
    }

    /**
     * @test
     */
    public function longUrlMustEnforceMaxRange(): void
    {
        $urlTest = new Url();

        $urlTest->setLongUrl(str_repeat('a', 301));
        $this->assertStringContainsString(self::LONG_URL_MAX_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl(str_repeat('a', 300));
        $this->assertStringNotContainsString(self::LONG_URL_MAX_RANGE, self::$validator->validate($urlTest)->__toString());
    }

    /**
     * @test
     */
    public function longUrlMustBeAValidUrl(): void
    {
        $urlTest = new Url();

        $urlTest->setLongUrl('a');
        $this->assertStringContainsString(self::LONG_URL_VALID_URL, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl('http://');
        $this->assertStringContainsString(self::LONG_URL_VALID_URL, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl('http://google.com');
        $this->assertStringNotContainsString(self::LONG_URL_VALID_URL, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl('https://www.google.com/search?q=syfmony&oq=syfmony&aqs=chrome..69i57j0l7.2174j0j7&sourceid=chrome&ie=UTF-8');
        $this->assertStringNotContainsString(self::LONG_URL_VALID_URL, self::$validator->validate($urlTest)->__toString());
    }

    /**
     * @test
     */
    public function onPrePersistShouldUpdateDateCreated(): void
    {
        $urlTest = (new Url())->setLongUrl('aaaaa');
        $this->assertNull($urlTest->getDateCreated());

        self::$em->persist($urlTest);

        $this->assertInstanceOf('\DateTimeImmutable', $urlTest->getDateCreated());
    }
}
