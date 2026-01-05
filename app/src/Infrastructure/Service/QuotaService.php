<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use App\Application\Contract\QuotaServiceInterface;
use App\Application\Exception\QuotaExceededException;
use App\Application\Exception\UserNotFoundException;
use App\Infrastructure\Doctrine\Repository\TailoringQuotaRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class QuotaService implements QuotaServiceInterface
{
    private const int MAX_WEEKLY_ATTEMPTS = 5;

    public function __construct(
        private TailoringQuotaRepository $quotaRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function checkAndIncrementQuota(string $userId): void
    {
        if (36 !== strlen($userId)) {
            return;
        }

        // Atomic update logic as per plan
        $qb = $this->entityManager->createQueryBuilder();
        $qb->update(\App\Infrastructure\Doctrine\Entity\TailoringQuota::class, 'q')
            ->set('q.weeklyAttemptsCount', 'q.weeklyAttemptsCount + 1')
            ->where('q.user = :userId')
            ->andWhere('q.weeklyAttemptsCount < :max')
            ->setParameter('userId', $userId)
            ->setParameter('max', self::MAX_WEEKLY_ATTEMPTS);

        $updated = $qb->getQuery()->execute();

        if (0 === $updated) {
            // Check if it's because quota is full or because entry doesn't exist
            $quota = $this->quotaRepository->findOneBy(['user' => $userId]);
            if (null === $quota) {
                // Should probably be handled by a listener or during user creation,
                // but let's handle it here for safety.
                $user = $this->entityManager->find(\App\Infrastructure\Doctrine\Entity\User::class, \Symfony\Component\Uid\Uuid::fromString($userId));
                if (!$user instanceof \App\Infrastructure\Doctrine\Entity\User) {
                    throw new UserNotFoundException('User not found.');
                }
                $quota = new \App\Infrastructure\Doctrine\Entity\TailoringQuota($user);
                $quota->setWeeklyAttemptsCount(1);
                $this->entityManager->persist($quota);
                $this->entityManager->flush();

                return;
            }

            if ($quota->getWeeklyAttemptsCount() >= self::MAX_WEEKLY_ATTEMPTS) {
                throw new QuotaExceededException('Weekly tailoring quota exceeded.');
            }
        }
    }
}
