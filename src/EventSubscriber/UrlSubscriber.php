<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Url;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\EventSubscriber;

class UrlSubscriber implements EventSubscriber
{
    const PERMITTED = '0123456789abcdefghijklmnopqrstuvwxyz';

    public function getSubscribedEvents(): array
    {
        return [
            'prePersist',
            'preUpdate'
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->uniqueShortUrl($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->uniqueShortUrl($args);
    }

    private function uniqueShortUrl(LifecycleEventArgs $args): void
    {
        $entity        = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if (!$entity instanceof Url) {
            return;
        }

        $repo = $args->getObjectManager()->getRepository(Url::class);
        while ($repo->findOneBy(['shortUrl' => $entity->getShortUrl()]) || empty($entity->getShortUrl())) {
            $entity->setShortUrl(substr(str_shuffle(self::PERMITTED), 5, 9));
        }
    }
}
