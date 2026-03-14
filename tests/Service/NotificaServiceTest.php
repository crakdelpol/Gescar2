<?php

namespace App\Tests\Service;

use App\Entity\Anagrafica;
use App\Entity\Notifica;
use App\Entity\User;
use App\Entity\Vettura;
use App\Repository\NotificaRepository;
use App\Service\NotificaService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test per NotificaService.
 * Verifica la creazione di notifiche, il controllo duplicati e le query di storico.
 *
 * @covers \App\Service\NotificaService
 */
class NotificaServiceTest extends TestCase
{
    private NotificaService $service;

    /** @var EntityManagerInterface&MockObject */
    private EntityManagerInterface $em;

    /** @var NotificaRepository&MockObject */
    private NotificaRepository $repo;

    protected function setUp(): void
    {
        $this->em   = $this->createMock(EntityManagerInterface::class);
        $this->repo = $this->createMock(NotificaRepository::class);

        $this->service = new NotificaService($this->em, $this->repo);
    }

    // ─── registra ────────────────────────────────────────────────────────────

    public function testRegistraCreatesAndPersistsNotifica(): void
    {
        $anagrafica = new Anagrafica();
        $vettura    = new Vettura();
        $utente     = new User();

        $this->em->expects($this->once())->method('persist')
            ->with($this->isInstanceOf(Notifica::class));
        $this->em->expects($this->once())->method('flush');

        $notifica = $this->service->registra(
            $anagrafica,
            Notifica::TIPO_REVISIONE,
            Notifica::CANALE_EMAIL,
            Notifica::ESITO_INVIATA,
            'test note',
            $vettura,
            $utente
        );

        $this->assertInstanceOf(Notifica::class, $notifica);
        $this->assertSame($anagrafica, $notifica->getAnagrafica());
        $this->assertSame(Notifica::TIPO_REVISIONE, $notifica->getTipoScadenza());
        $this->assertSame(Notifica::CANALE_EMAIL, $notifica->getCanale());
        $this->assertSame(Notifica::ESITO_INVIATA, $notifica->getEsito());
        $this->assertSame('test note', $notifica->getNote());
        $this->assertSame($vettura, $notifica->getVettura());
        $this->assertSame($utente, $notifica->getUtente());
    }

    public function testRegistraSenzaOptionals(): void
    {
        $anagrafica = new Anagrafica();

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $notifica = $this->service->registra(
            $anagrafica,
            Notifica::TIPO_BOLLO,
            Notifica::CANALE_TELEFONO,
            Notifica::ESITO_NON_RISPONDE
        );

        $this->assertNull($notifica->getNote());
        $this->assertNull($notifica->getVettura());
        $this->assertNull($notifica->getUtente());
    }

    // ─── getStoricoCliente ───────────────────────────────────────────────────

    public function testGetStoricoClienteCallsRepository(): void
    {
        $anagrafica       = new Anagrafica();
        $notificaAttesa   = new Notifica();

        $this->repo->expects($this->once())
            ->method('findByAnagrafica')
            ->with($anagrafica)
            ->willReturn([$notificaAttesa]);

        $result = $this->service->getStoricoCliente($anagrafica);

        $this->assertCount(1, $result);
        $this->assertSame($notificaAttesa, $result[0]);
    }

    // ─── getRecenti ──────────────────────────────────────────────────────────

    public function testGetRecentiDefaultSetteGiorni(): void
    {
        $this->repo->expects($this->once())
            ->method('findRecenti')
            ->with(7)
            ->willReturn([]);

        $result = $this->service->getRecenti();

        $this->assertIsArray($result);
    }

    public function testGetRecentiCustomDays(): void
    {
        $this->repo->expects($this->once())
            ->method('findRecenti')
            ->with(30)
            ->willReturn([]);

        $this->service->getRecenti(30);
    }

    // ─── èGiàAvvisato ────────────────────────────────────────────────────────

    public function testEGiaAvvisatoReturnsTrueWhenCountPositive(): void
    {
        $anagrafica = new Anagrafica();

        $query = $this->createMock(AbstractQuery::class);
        $query->method('getSingleScalarResult')->willReturn(1);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->repo->method('createQueryBuilder')->willReturn($qb);

        $result = $this->service->èGiàAvvisato($anagrafica, Notifica::TIPO_REVISIONE);

        $this->assertTrue($result);
    }

    public function testEGiaAvvisatoReturnsFalseWhenCountZero(): void
    {
        $anagrafica = new Anagrafica();

        $query = $this->createMock(AbstractQuery::class);
        $query->method('getSingleScalarResult')->willReturn(0);

        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('andWhere')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();
        $qb->method('getQuery')->willReturn($query);

        $this->repo->method('createQueryBuilder')->willReturn($qb);

        $result = $this->service->èGiàAvvisato($anagrafica, Notifica::TIPO_ASSICURAZIONE, 14);

        $this->assertFalse($result);
    }
}
