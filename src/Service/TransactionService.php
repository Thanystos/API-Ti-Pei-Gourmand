<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TransactionService
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
    }

    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }

    public function commitTransaction()
    {
        $this->entityManager->commit();
    }

    public function rollbackTransaction()
    {
        $this->entityManager->rollback();
    }

    public function isTransactionStarted(): bool
    {
        return $this->entityManager->getConnection()->isTransactionActive();
    }

    public function handleException(\Exception $e)
    {
        return UtilsService::handleException($e->getMessage(), $e->getCode());
    }
}
