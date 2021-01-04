<?php

namespace App\Controller;

use App\Entity\Measurement;
use App\Entity\Mission;
use App\Entity\MissionSensor;
use App\Export\MissionExport;
use App\Form\Type\MissionType;
use App\Repository\MissionRepository;
use App\Repository\MissionSensorRepository;
use App\Repository\MissionThemeRepository;
use App\Repository\SensorRepository;
use App\Scorpio\Client;
use App\Scorpio\SubscriptionManager;
use App\Traits\LoggerTrait;
use Box\Spout\Common\Type;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/mission", name="mission_")
 */
class MissionController extends AbstractController implements LoggerAwareInterface
{
    use LoggerTrait;

    /** @var SerializerInterface */
    private $serializer;

    /** @var array */
    private $options;

    public function __construct(SerializerInterface $serializer, array $missionOptions)
    {
        $this->serializer = $serializer;
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($missionOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'measurements_page_size' => 100,
            'max_number_of_measurements_to_load' => 500,
            'initial_data_window_size' => 4 * 60 * 60, // 4 hours
        ]);

        $resolver->setDefault('mercure', static function (OptionsResolver $mercureResolver) {
            $mercureResolver->setRequired('event_source_url');
        });
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(MissionRepository $missionRepository): Response
    {
        $missions = $missionRepository->findAll();

        return $this->render('mission/index.html.twig', [
            'missions' => $missions,
            'missions_url' => $this->generateUrl('api_missions_get_collection'),
            'missions_data' => json_decode($this->serializer->serialize($missions, 'jsonld', ['groups' => 'mission_read'])),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request, MissionThemeRepository $missionThemeRepository): Response
    {
        $theme = $missionThemeRepository->find($request->get('theme'));
        if (null === $theme) {
            return $this->redirectToRoute('theme_index');
        }

        $mission = (new Mission())
            ->setTheme($theme);
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($mission);
            $entityManager->flush();

            return $this->redirectToRoute('mission_show', ['id' => $mission->getId()]);
        }

        return $this->render('mission/new.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(Mission $mission): Response
    {
        $finishMissionForm = $this->createFinishForm($mission);
        $appOptions = $this->getAppOptions($mission);

        return $this->render('mission/show.html.twig', [
            'mission' => $mission,
            'finish_mission_form' => $finishMissionForm->createView(),
            'app_options' => $appOptions,
            'export_formats' => [Type::CSV, Type::ODS, Type::XLSX],
            'export_name' => 'mission-'.$mission->getTitle(),
        ]);
    }

    /**
     * @Route("/{id}/log.{_format}", name="log", methods={"GET"},
     *     format="csv",
     *     requirements={
     *         "_format": "csv|ods|xlsx",
     *     }
     *  )
     */
    public function logEntries(Mission $mission, string $_format, MissionExport $export): void
    {
        $export->exportLogEntries($mission, $_format);
        exit;
    }

    /**
     * @Route("/{id}/measurements.{_format}", name="measurements", methods={"GET"},
     *     format="csv",
     *     requirements={
     *         "_format": "csv|ods|xlsx",
     *     }
     *  )
     */
    public function measurements(Mission $mission, string $_format, MissionExport $export): void
    {
        $export->exportMeasurements($mission, $_format);
        exit;
    }

    private function getAppOptions(Mission $mission)
    {
        $appOptions['eventSourceUrl'] = $this->options['mercure']['event_source_url']
            .'?'.http_build_query([
                'topic' => 'mission:'.$mission->getId(),
            ]);

        $measurementsPageSize = $this->options['measurements_page_size'];
        $maxNumberOfMeasurementsToLoad = $this->options['max_number_of_measurements_to_load'] ?? 3 * $measurementsPageSize;

        $appOptions['measurementsUrl'] = $this->generateUrl('api_missions_measurements_get_subresource', [
            'id' => $mission->getId(),
            'order' => ['measuredAt' => 'desc'],
            'itemsPerPage' => $measurementsPageSize,
        ]);
        $appOptions['logEntriesUrl'] = $this->generateUrl('api_mission_log_entries_GET_collection', [
            'mission.id' => $mission->getId(),
            'order' => ['loggedAt' => 'desc'],
            'pagination' => false,
        ]);
        $appOptions['logEntryPostUrl'] = $this->generateUrl('api_mission_log_entries_POST_collection');

        $appOptions['mission'] = $this->serializer->serialize($mission, 'jsonld', ['groups' => 'mission_read']);

        $appOptions['sensors'] = array_column(
            $mission->getMissionSensors()->map(static function (MissionSensor $missionSensor) {
                $sensor = $missionSensor->getSensor();

                return [
                    'id' => $sensor->getId(),
                    'type' => $sensor->getType(),
                    'name' => $missionSensor->getName() ?: $sensor->getId(),
                    'enabled' => $missionSensor->getEnabled(),
                ];
            })->toArray(),
            null,
            'id'
        );

        $appOptions['options'] = [
            'maxNumberOfMeasurementsToLoad' => $maxNumberOfMeasurementsToLoad,
            'initialDataWindowSize' => $this->options['initial_data_window_size'],
        ];

        return $appOptions;
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Mission $mission): Response
    {
        $form = $this->createForm(MissionType::class, $mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('mission_show', ['id' => $mission->getId()]);
        }

        return $this->render('mission/edit.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/finish", name="finish", methods={"POST"})
     */
    public function finish(Request $request, Mission $mission, SubscriptionManager $subscriptionManager): Response
    {
        $form = $this->createFinishForm($mission);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mission->setFinishedAt(new DateTime());
            $this->getDoctrine()->getManager()->flush();
            $subscriptionManager->deleteSubscription($mission);

            return $this->redirectToRoute('mission_show', ['id' => $mission->getId()]);
        }

        return $this->redirectToRoute('mission_show', ['id' => $mission->getId()]);
    }

    private function createFinishForm(Mission $mission)
    {
        return $this->createFormBuilder($mission)
            ->setAction($this->generateUrl('mission_finish', [
                'id' => $mission->getId(),
            ]))
            ->getForm();
    }

    /**
     * @Route("/{id}/subscription/notify", name="subscription_notify", methods={"POST"})
     */
    public function subscriptionNotity(Request $request, Mission $mission, EntityManagerInterface $entityManager, SensorRepository $sensorRepository, MissionSensorRepository $missionSensorRepository): Response
    {
        $payload = json_decode($request->getContent(), true);

        $this->debug(sprintf('Subscription notification received: %s; %s', $mission->getId(), $request->getContent()));

        if (!isset($payload['data'])) {
            throw new BadRequestHttpException();
        }

        foreach ($payload['data'] as $data) {
            try {
                $measuredAt = null;
                if (isset($data[Client::ENTITY_ATTRIBUTE_RESULT_TIME]['value'])) {
                    try {
                        $measuredAt = new DateTimeImmutable($data[Client::ENTITY_ATTRIBUTE_RESULT_TIME]['value']);
                    } catch (\Exception $exception) {
                    }
                }

                if (null === $measuredAt) {
                    $this->debug('Cannot get time of measurement');

                    throw new BadRequestHttpException('Cannot get time of measurement');
                }

                $streamId = $data[Client::ENTITY_ATTRIBUTE_BELONGS_TO]['object']
                    ?? $data[Client::ENTITY_ATTRIBUTE_BELONGS_TO_SHORT]['object']
                    ?? null;
                if (null === $streamId) {
                    throw new BadRequestHttpException('Missing stream id');
                }
                $sensor = $sensorRepository->findOneBy(['streamId' => $streamId]);
                if (null === $sensor) {
                    $this->debug(sprintf('Cannot find sensor with stream id: %s', $streamId));

                    throw new BadRequestHttpException(sprintf('Invalid stream id: %s', $streamId));
                }

                $missionSensor = $missionSensorRepository->findOneBy([
                   'sensor' => $sensor,
                   'mission' => $mission,
                ]);

                if (null === $missionSensor) {
                    $this->debug(sprintf(
                        'Cannot find mission sensor for mission %s and sensor %s',
                        $mission->getId(),
                        $sensor->getId(),
                    ));

                    throw new BadRequestHttpException(sprintf('Invalid mission sensor %s in mission %s', $sensor->getId(), $mission->getId()));
                }

                if (!$missionSensor->getEnabled()) {
                    $this->debug(sprintf('Mission sensor %s not enabled', $missionSensor->getId()));
                } else {
                    $value = $data[Client::ENTITY_ATTRIBUTE_HAS_SIMPLE_RESULT]['value'] ?? null;
                    if (null === $value) {
                        throw new BadRequestHttpException('Missing value');
                    }

                    try {
                        $value = Client::getTypedValue($value);
                    } catch (\Exception $exception) {
                        throw new BadRequestException('Cannot get value: %s', $value);
                    }

                    $measurement = (new Measurement())
                        ->setMeasuredAt($measuredAt)
                        ->setSensor($sensor)
                        ->setValue($value)
                        ->setData($data)
                        ->setPayload($payload)
                        ->setMission($mission);
                    $entityManager->persist($measurement);
                    $entityManager->flush();
                }
            } catch (\Exception $exception) {
                $this->error($exception->getMessage(), ['exception' => $exception]);

                return new JsonResponse([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
