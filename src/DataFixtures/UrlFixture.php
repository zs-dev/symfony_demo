<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Url;

class UrlFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $url = (new Url())
                ->setLongUrl('http://google.com')
                ->setShortUrl('abcde')
        ;

        $manager->persist($url);

        $manager->flush();
    }
}
