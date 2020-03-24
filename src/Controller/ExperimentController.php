<?php

namespace App\Controller;

use App\Entity\Experiment;
use App\Entity\ExperimentLogEntry;
use App\Entity\Measurement;
use App\Entity\Sensor;
use App\Form\ExperimentType;
use App\Repository\ExperimentRepository;
use App\Repository\SensorRepository;
use App\Traits\LoggerTrait;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mercure\PublisherInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/experiment", name="experiment_")
 */
class ExperimentController extends AbstractController implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var array */
    private $options;

    public function __construct(array $experimentOptions)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($experimentOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('mercure', static function (OptionsResolver $mercureResolver) {
            $mercureResolver->setRequired('event_source_url');
        });
    }

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index(ExperimentRepository $experimentRepository): Response
    {
        return $this->render('experiment/index.html.twig', [
            'experiments' => $experimentRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
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
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Experiment $experiment): Response
    {
        return $this->render('experiment/show.html.twig', [
            'experiment' => $experiment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
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
     * @Route("/{id}", name="delete", methods={"DELETE"})
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
     * @Route("/{id}/subscription/notify", name="subscription_notify", methods={"POST"})
     */
    public function subscriptionNotity(Request $request, Experiment $experiment, EntityManagerInterface $entityManager, SensorRepository $sensorRepository, PublisherInterface $publisher, SerializerInterface $serializer): Response
    {
        $payload = json_decode($request->getContent(), true);
        foreach ($payload['data'] as $data) {
            $measuredAt = isset($data['https://uri.fiware.org/ns/data-models#dateObserved']['value']['@value'])
                ? new DateTimeImmutable($data['https://uri.fiware.org/ns/data-models#dateObserved']['value']['@value'])
                : null;

            if (null === $measuredAt) {
                return new BadRequestHttpException('Cannot get time of measurement');
            }

            $sensorId = $data['id'];
            $sensor = $sensorRepository->find($sensorId);
            if (null === $sensor) {
                return new BadRequestHttpException(sprintf('Invalid sensor: %s', $sensorId));
            }

            $measuredValue = 0;
            foreach ($data as $key => $value) {
                if (isset($value['value'])
                    && 'https://uri.fiware.org/ns/data-models#dateObserved' !== $key
                    && 0 === strpos($key, 'https://uri.fiware.org/ns/data-models#')) {
                    $measuredValue = (float) $value['value'];
                }
            }
            $measurement = (new Measurement())
                ->setMeasuredAt($measuredAt)
                ->setSensor($sensor)
                ->setValue($measuredValue)
                ->setData($data)
                ->setPayload($payload)
                ->setExperiment($experiment);
            $entityManager->persist($measurement);
            $entityManager->flush();

            $update = new Update(
                'experiment:'.$experiment->getId(),
                $serializer->serialize([
                    'measurement' => $measurement,
                ], 'json', ['groups' => ['experiment']])
            );
            $publisher($update);

            // @TODO Move this to a service
            // @TODO Check measurement outside bounds
            if (0 !== 1) {
                $logEntry = (new ExperimentLogEntry())
                    ->setExperiment($experiment)
                    ->setSensor($sensor)
                    ->setLoggedAt($measuredAt)
                    ->setType('alert')
                    ->setContent(sprintf('Value %f outside bounds (%f; %f)', $measuredValue, -42, 87));
                $entityManager->persist($logEntry);
                $entityManager->flush();

                $update = new Update(
                    'experiment:'.$experiment->getId(),
                    $serializer->serialize([
                        'log_entry' => $logEntry,
                    ], 'json', ['groups' => ['experiment']])
                );
                $publisher($update);
            }
        }
        $this->info(sprintf('Subscription notification received: %s; %s; %s', $experiment->getId(), $request->get('sensor'), $request->getContent()));

        return new JsonResponse(['status' => 'ok']);
    }

    /**
     * @Route("/{id}/app", name="app", methods={"GET"})
     */
    public function app(Experiment $experiment): Response
    {
        $appOptions['eventSourceUrl'] = $this->options['mercure']['event_source_url']
            .'?'.http_build_query([
                'topic' => 'experiment:'.$experiment->getId(),
            ]);

        $appOptions['measurementsUrl'] = $this->generateUrl('experiment_measurements', ['id' => $experiment->getId()]);
        $appOptions['logEntriesUrl'] = $this->generateUrl('experiment_log_entries', ['id' => $experiment->getId()]);

        $appOptions['sensors'] = array_column(
            $experiment->getSensors()->map(static function (Sensor $sensor) {
                return [
                    'id' => $sensor->getId(),
                    'name' => $sensor->getId(),
                ];
            })->toArray(),
            null,
            'id'
        );

        return $this->render('experiment/app.html.twig', [
            'experiment' => $experiment,
            'app_options' => $appOptions,
        ]);
    }

    /**
     * @Route("/{id}/measurements", name="measurements", methods={"GET"})
     */
    public function measurements(Experiment $experiment, SerializerInterface $serializer): Response
    {
        $measurements = $experiment->getMeasurements()->toArray();
        usort($measurements, static function (Measurement $a, Measurement $b) {
            return $a->getMeasuredAt() <=> $b->getMeasuredAt();
        });
        $data = $serializer->serialize($measurements, 'json', ['groups' => ['experiment', 'measurement']]);

        return (new JsonResponse())->setJson($data);
    }

    /**
     * @Route("/{id}/log-entries", name="log_entries", methods={"GET"})
     */
    public function logEntries(Experiment $experiment, SerializerInterface $serializer): Response
    {
        $logEntries = $experiment->getLogEntries()->toArray();
        usort($logEntries, static function (ExperimentLogEntry $a, ExperimentLogEntry $b) {
            return $a->getLoggedAt() <=> $b->getLoggedAt();
        });
        $data = $serializer->serialize($logEntries, 'json', ['groups' => ['experiment', 'log_entry']]);

        return (new JsonResponse())->setJson($data);
    }
}
