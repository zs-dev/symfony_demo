<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Url;
use Symfony\Bridge\Doctrine\DataFixtures\ContainerAwareLoader;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use App\DataFixtures\UrlFixture;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * @coversDefaultClass App\Entity\Url
 */
class UrlTest extends WebTestCase
{
    const UNIQUE_MESSAGE = 'Short url must be unique.';
    const SHORT_URL_MIN_RANGE = 'Short url cannot be less than 5 characters.';
    const SHORT_URL_MAX_RANGE = 'Short url cannot be more than 9 characters.';
    const SHORT_URL_MANDATORY = 'Short url is mandatory.';
    const LONG_URL_MIN_RANGE = 'Long url cannot be less than 5 characters.';
    const LONG_URL_MAX_RANGE = 'Long url cannot be more than 300 characters.';
    const LONG_URL_MANDATORY = 'Long url is mandatory.';

    protected static RecursiveValidator $validator;
    protected static EntityManager $entityManager;

    public function setUp(): void
    {
        self::bootKernel();
        $container = self::$container;
        self::$validator = $container->get('validator');
        self::$entityManager = $container->get('doctrine')->getManager();

        $loader = new ContainerAwareLoader($container);
        $loader->addFixture(new UrlFixture());
        (new ORMExecutor(self::$entityManager))->execute($loader->getFixtures(), true);
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
        $url = self::$entityManager->getRepository(Url::class)->findOneBy([]);
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
    public function longUrlMustEnforceRange(): void
    {
        $urlTest = (new Url())->setLongUrl('');
        $this->assertStringContainsString(self::LONG_URL_MIN_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl('a');
        $this->assertStringContainsString(self::LONG_URL_MIN_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl(str_repeat('a', 5));
        $this->assertStringNotContainsString(self::LONG_URL_MIN_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl(str_repeat('a', 301));
        $this->assertStringContainsString(self::LONG_URL_MAX_RANGE, self::$validator->validate($urlTest)->__toString());

        $urlTest->setLongUrl(str_repeat('a', 300));
        $this->assertStringNotContainsString(self::LONG_URL_MAX_RANGE, self::$validator->validate($urlTest)->__toString());
    }

    /**
     * @test
     */
    public function onPrePersistShouldUpdateDateCreatedAndShortUrl(): void
    {
        $urlTest = (new Url())->setLongUrl('aaaaa');
        $this->assertNull($urlTest->getDateCreated());

        self::$entityManager->persist($urlTest);

        $this->assertInstanceOf('\DateTimeImmutable', $urlTest->getDateCreated());
        $this->assertMatchesRegularExpression('/^[a-z\d]{5,9}$/', $urlTest->getShortUrl());
    }
}
