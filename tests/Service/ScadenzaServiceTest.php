<?php

namespace App\Tests\Service;

use App\Service\ScadenzaService;
use App\Repository\VetturaRepository;
use App\Repository\AssicurazioneRepository;
use App\Repository\BolloRepository;
use App\Repository\PatenteRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit test per ScadenzaService.
 * Verifica la logica di calcolo stati scadenza e i badge Bootstrap corrispondenti.
 *
 * @covers \App\Service\ScadenzaService
 */
class ScadenzaServiceTest extends TestCase
{
    private ScadenzaService $service;

    /** @var VetturaRepository&MockObject */
    private VetturaRepository $vetturaRepo;

    /** @var AssicurazioneRepository&MockObject */
    private AssicurazioneRepository $assicurazioneRepo;

    /** @var BolloRepository&MockObject */
    private BolloRepository $bolloRepo;

    /** @var PatenteRepository&MockObject */
    private PatenteRepository $patenteRepo;

    protected function setUp(): void
    {
        $this->vetturaRepo       = $this->createMock(VetturaRepository::class);
        $this->assicurazioneRepo = $this->createMock(AssicurazioneRepository::class);
        $this->bolloRepo         = $this->createMock(BolloRepository::class);
        $this->patenteRepo       = $this->createMock(PatenteRepository::class);

        $this->service = new ScadenzaService(
            $this->vetturaRepo,
            $this->assicurazioneRepo,
            $this->bolloRepo,
            $this->patenteRepo,
        );
    }

    // ─── calcolaStato ────────────────────────────────────────────────────────

    public function testCalcolaStatoNullReturnsNonDefinito(): void
    {
        $this->assertSame(
            ScadenzaService::STATO_NON_DEFINITO,
            $this->service->calcolaStato(null)
        );
    }

    public function testCalcolaStatoDataPassataReturnsScaduto(): void
    {
        $ieri = new \DateTime('-1 day');
        $this->assertSame(
            ScadenzaService::STATO_SCADUTO,
            $this->service->calcolaStato($ieri)
        );
    }

    public function testCalcolaStatoOggiReturnsInScadenza(): void
    {
        $oggi = new \DateTime('today');
        $this->assertSame(
            ScadenzaService::STATO_IN_SCADENZA,
            $this->service->calcolaStato($oggi)
        );
    }

    public function testCalcolaStatoEntro30GiorniReturnsInScadenza(): void
    {
        $tra29 = new \DateTime('+29 days');
        $this->assertSame(
            ScadenzaService::STATO_IN_SCADENZA,
            $this->service->calcolaStato($tra29)
        );
    }

    public function testCalcolaStatoTra31GiorniReturnsInAvvicinamento(): void
    {
        $tra31 = new \DateTime('+31 days');
        $this->assertSame(
            ScadenzaService::STATO_IN_AVVICINAMENTO,
            $this->service->calcolaStato($tra31)
        );
    }

    public function testCalcolaStatoTra60GiorniReturnsInAvvicinamento(): void
    {
        $tra60 = new \DateTime('+60 days');
        $this->assertSame(
            ScadenzaService::STATO_IN_AVVICINAMENTO,
            $this->service->calcolaStato($tra60)
        );
    }

    public function testCalcolaStatoOltre60GiorniReturnsOk(): void
    {
        $tra90 = new \DateTime('+90 days');
        $this->assertSame(
            ScadenzaService::STATO_OK,
            $this->service->calcolaStato($tra90)
        );
    }

    // ─── getBadge ────────────────────────────────────────────────────────────

    public function testGetBadgeScadutoReturnsDanger(): void
    {
        $this->assertSame('danger', $this->service->getBadge(ScadenzaService::STATO_SCADUTO));
    }

    public function testGetBadgeInScadenzaReturnsWarning(): void
    {
        $this->assertSame('warning', $this->service->getBadge(ScadenzaService::STATO_IN_SCADENZA));
    }

    public function testGetBadgeInAvvicinamentoReturnsInfo(): void
    {
        $this->assertSame('info', $this->service->getBadge(ScadenzaService::STATO_IN_AVVICINAMENTO));
    }

    public function testGetBadgeOkReturnsSuccess(): void
    {
        $this->assertSame('success', $this->service->getBadge(ScadenzaService::STATO_OK));
    }

    public function testGetBadgeNonDefinitoReturnsSecondary(): void
    {
        $this->assertSame('secondary', $this->service->getBadge(ScadenzaService::STATO_NON_DEFINITO));
    }

    public function testGetBadgeStatoSconosciutoReturnsSecondary(): void
    {
        $this->assertSame('secondary', $this->service->getBadge('stato_inesistente'));
    }

    // ─── getSommarioDashboard ────────────────────────────────────────────────

    public function testGetSommarioDashboardReturnsExpectedKeys(): void
    {
        $this->vetturaRepo->method('findRevisioniInRange')->willReturn([]);
        $this->assicurazioneRepo->method('findInScadenza')->willReturn([]);
        $this->bolloRepo->method('findInRange')->willReturn([]);
        $this->patenteRepo->method('findInScadenza')->willReturn([]);

        $sommario = $this->service->getSommarioDashboard();

        $this->assertArrayHasKey('revisioni_scadute', $sommario);
        $this->assertArrayHasKey('revisioni_30gg', $sommario);
        $this->assertArrayHasKey('assicurazioni_30gg', $sommario);
        $this->assertArrayHasKey('patenti_60gg', $sommario);
        $this->assertArrayHasKey('bolli_mese_corrente', $sommario);
    }

    public function testGetSommarioDashboardCallsRepositoriesWithCorrectArguments(): void
    {
        $this->vetturaRepo
            ->expects($this->exactly(2))
            ->method('findRevisioniInRange')
            ->willReturn([]);

        $this->assicurazioneRepo
            ->expects($this->once())
            ->method('findInScadenza')
            ->willReturn([]);

        $this->patenteRepo
            ->expects($this->once())
            ->method('findInScadenza')
            ->willReturn([]);

        $this->bolloRepo
            ->expects($this->once())
            ->method('findInRange')
            ->willReturn([]);

        $this->service->getSommarioDashboard();
    }

    // ─── BADGE_MAP completezza ───────────────────────────────────────────────

    public function testBadgeMapCoversAllStates(): void
    {
        $stati = [
            ScadenzaService::STATO_SCADUTO,
            ScadenzaService::STATO_IN_SCADENZA,
            ScadenzaService::STATO_IN_AVVICINAMENTO,
            ScadenzaService::STATO_OK,
            ScadenzaService::STATO_NON_DEFINITO,
        ];

        foreach ($stati as $stato) {
            $badge = $this->service->getBadge($stato);
            $this->assertNotEmpty($badge, "Badge vuoto per stato: {$stato}");
        }
    }
}
