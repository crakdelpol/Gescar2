# Gescar – Convenzioni di Codice e Pattern Symfony

## 1. Struttura delle Entity Doctrine

Ogni Entity deve seguire questo pattern standard:

```php
<?php
// src/Entity/Vettura.php

namespace App\Entity;

use App\Repository\VetturaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VetturaRepository::class)]
#[ORM\Table(name: 'vettura')]
class Vettura
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Anagrafica::class, inversedBy: 'vetture')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Anagrafica $intestatario = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $targa = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dataScadenzaRevisione = null;

    // Getter e Setter per ogni campo
    public function getId(): ?int { return $this->id; }
    // ...
}
```

**Regole:**
- Usare **attributi PHP 8** (`#[ORM\...]`), non annotazioni dottrine legacy (`@ORM\...`)
- Tutti i campi nullable devono essere `?tipo = null`
- Nomi proprietà in **camelCase**, campi DB in **snake_case** (Doctrine converte automaticamente)

---

## 2. Repository Pattern

Ogni Entity ha il suo Repository. La logica di query complessa va **sempre nel Repository**, mai nel Controller.

```php
<?php
// src/Repository/VetturaRepository.php

namespace App\Repository;

use App\Entity\Vettura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VetturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vettura::class);
    }

    /**
     * Restituisce veicoli con scadenza revisione entro N giorni.
     */
    public function findInScadenzaRevisione(int $giorniAvviso = 30): array
    {
        $oggi = new \DateTime();
        $limite = (new \DateTime())->modify("+{$giorniAvviso} days");

        return $this->createQueryBuilder('v')
            ->where('v.dataScadenzaRevisione BETWEEN :oggi AND :limite')
            ->andWhere('v.esenteRevisione = false OR v.esenteRevisione IS NULL')
            ->setParameter('oggi', $oggi)
            ->setParameter('limite', $limite)
            ->orderBy('v.dataScadenzaRevisione', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
```

---

## 3. Controller: Regole

- I Controller **non devono contenere logica di business**
- Usare `#[Route(...)]` con attributi PHP 8
- Iniettare dipendenze tramite constructor injection o parametri del metodo

```php
<?php
// src/Controller/VetturaController.php

namespace App\Controller;

use App\Entity\Vettura;
use App\Form\VetturaType;
use App\Repository\VetturaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vettura', name: 'gescar_vettura_')]
class VetturaController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(VetturaRepository $repo): Response
    {
        return $this->render('vettura/index.html.twig', [
            'vetture' => $repo->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $vettura = new Vettura();
        $form = $this->createForm(VetturaType::class, $vettura);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($vettura);
            $em->flush();
            $this->addFlash('success', 'Veicolo aggiunto con successo.');
            return $this->redirectToRoute('gescar_vettura_index');
        }

        return $this->render('vettura/new.html.twig', [
            'form' => $form,
        ]);
    }
}
```

---

## 4. Naming Convention Route

Tutte le route seguono il pattern: `gescar_{entità}_{azione}`

| Azione | Metodo HTTP | Nome Route |
|---|---|---|
| Lista | GET | `gescar_vettura_index` |
| Dettaglio | GET | `gescar_vettura_show` |
| Nuova (form) | GET | `gescar_vettura_new` |
| Nuova (submit) | POST | `gescar_vettura_new` |
| Modifica (form) | GET | `gescar_vettura_edit` |
| Modifica (submit) | PUT/PATCH | `gescar_vettura_edit` |
| Elimina | DELETE | `gescar_vettura_delete` |

---

## 5. Template Twig

Struttura directory template:
```
templates/
├── base.html.twig              ← Layout principale Bootstrap
├── anagrafica/
│   ├── index.html.twig
│   ├── show.html.twig
│   ├── _form.html.twig         ← Partial form (riusabile in new e edit)
│   ├── new.html.twig
│   └── edit.html.twig
├── vettura/
│   └── ...
└── dashboard/
    └── index.html.twig
```

**Convenzioni Twig:**
- Usare `{% block title %}` e `{% block body %}` nel layout base
- I partial iniziano con `_` (underscore)
- Usare `path('gescar_vettura_index')` per i link, mai URL hardcodati
- Flash messages gestiti nel layout base

---

## 6. Form Type

```php
<?php
// src/Form/VetturaType.php

namespace App\Form;

use App\Entity\Vettura;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VetturaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('targa', TextType::class, [
                'label' => 'Targa',
                'required' => false,
                'attr' => ['class' => 'form-control text-uppercase'],
            ])
            ->add('tipoVettura', ChoiceType::class, [
                'label' => 'Tipo veicolo',
                'choices' => [
                    'Autovettura' => 'autovettura',
                    'Autocarro' => 'autocarro',
                    'Motovettura' => 'motovettura',
                    'Rimorchio' => 'rimorchio',
                    'Altro' => 'altro',
                ],
                'placeholder' => '-- Seleziona --',
                'required' => false,
            ])
            ->add('dataScadenzaRevisione', DateType::class, [
                'label' => 'Scadenza revisione',
                'widget' => 'single_text',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Vettura::class]);
    }
}
```

---

## 7. Servizi

La logica complessa (calcolo scadenze, invio notifiche, ecc.) va in classi **Service**:

```
src/Service/
├── ScadenzaService.php       ← Calcola stati e scadenze imminenti
├── NotificaService.php       ← Gestisce l'invio e tracciamento avvisi
└── ImportService.php         ← Migrazione dati legacy (Scad_Pat)
```

I Service si iniettano nel Controller tramite **autowiring** automatico di Symfony.

---

## 8. Sicurezza

- **Non committare mai** credenziali, password o chiavi nel codice
- Usare `.env.local` per le variabili d'ambiente locali (non versionato)
- Il file `.env` contiene solo placeholder senza valori reali
- Le password utenti sono hashate con bcrypt (costo 13, come da configurazione attuale)

```env
# .env (versionato, placeholder)
DATABASE_URL="mysql://user:password@127.0.0.1:3306/gescar"

# .env.local (NON versionato)
DATABASE_URL="mysql://gescar_user:password_reale@127.0.0.1:3306/gescar"
```
