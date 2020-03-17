<?php

namespace App\Controller;

use App\Entity\Experiment;
use App\Entity\Measurement;
use App\Entity\Sensor;
use App\Form\ExperimentType;
use App\Repository\ExperimentRepository;
use App\Traits\LoggerTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/experiment")
 */
class ExperimentController extends AbstractController implements LoggerAwareInterface
{
    use LoggerTrait;

    /**
     * @Route("/", name="experiment_index", methods={"GET"})
     */
    public function index(ExperimentRepository $experimentRepository): Response
    {
        return $this->render('experiment/index.html.twig', [
            'experiments' => $experimentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="experiment_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $experiment = new Experiment();
        $form = $this->createForm(ExperimentType::class, $experiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($experiment);
            $entityManager->flush();

            return $this->redirectToRoute('experiment_index');
        }

        return $this->render('experiment/new.html.twig', [
            'experiment' => $experiment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="experiment_show", methods={"GET"})
     */
    public function show(Experiment $experiment): Response
    {
        return $this->render('experiment/show.html.twig', [
            'experiment' => $experiment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="experiment_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Experiment $experiment): Response
    {
        $form = $this->createForm(ExperimentType::class, $experiment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('experiment_index');
        }

        return $this->render('experiment/edit.html.twig', [
            'experiment' => $experiment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="experiment_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Experiment $experiment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$experiment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($experiment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('experiment_index');
    }

    /**
     * @Route("/{id}/subscription/notify", name="experiment_subscription_notify", methods={"POST"})
     */
    public function subscriptionNotity(Request $request, Experiment $experiment, EntityManagerInterface $entityManager, PublisherInterface $publisher): Response
    {
        $payload = json_decode($request->getContent(), true);
        foreach ($payload['data'] as $data) {
            // $measuredAt = $item['https://uri.fiware.org/ns/data-models#dateObserved']['value']['@value'];
            $measuredAt = $data['modifiedAt'];
            $measuredValue = 0;
            foreach ($data as $key => $value) {
                if (isset($value['value'])
                    && 'https://uri.fiware.org/ns/data-models#dateObserved' !== $key
                    && 0 === strpos($key, 'https://uri.fiware.org/ns/data-models#')) {
                    $measuredValue = (float) $value['value'];
                }
            }
            $measurement = (new Measurement())
                ->setMeasuredAt(new DateTimeImmutable($measuredAt))
                ->setSensor($data['id'])
                ->setValue($measuredValue)
                ->setData($data)
                ->setPayload($payload)
                ->setExperiment($experiment);
            $entityManager->persist($measurement);

            $update = new Update(
                $experiment->getSensors()->first()->getId(),
                json_encode(['data' => $data])
            );
            $publisher($update);
        }
        $entityManager->flush();
        $this->info(sprintf('Subscription notification received: %s; %s; %s', $experiment->getId(), $request->get('sensor'), $request->getContent()));

        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/{id}/app", name="experiment_app", methods={"GET"})
     */
    public function app(Experiment $experiment): Response
    {
        $appOptions = [
            'eventSourceUrl' => 'http://0.0.0.0:1337/.well-known/mercure?'
                .implode('&', $experiment->getSensors()->map(static function (Sensor $sensor) {
                    return 'topic='.urlencode($sensor->getId());
                })->toArray()),
        ];

        return $this->render('experiment/app.html.twig', [
            'experiment' => $experiment,
            'app_options' => $appOptions,
        ]);
    }
}
