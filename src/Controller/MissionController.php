<?php

namespace App\Controller;

use App\Entity\Measurement;
use App\Entity\Mission;
use App\Entity\MissionSensor;
use App\Form\Type\MissionType;
use App\Repository\MissionRepository;
use App\Repository\MissionThemeRepository;
use App\Repository\SensorRepository;
use App\Scorpio\SubscriptionManager;
use App\Traits\LoggerTrait;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        ]);
    }

    private function getAppOptions(Mission $mission)
    {
        $appOptions['eventSourceUrl'] = $this->options['mercure']['event_source_url']
            .'?'.http_build_query([
                'topic' => 'mission:'.$mission->getId(),
            ]);

        $appOptions['measurementsUrl'] = $this->generateUrl('api_measurements_GET_collection', [
            'mission.id' => $mission->getId(),
            'order' => ['measuredAt' => 'asc'],
            'pagination' => false,
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
                ];
            })->toArray(),
            null,
            'id'
        );

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
    public function subscriptionNotity(Request $request, Mission $mission, EntityManagerInterface $entityManager, SensorRepository $sensorRepository): Response
    {
        $payload = json_decode($request->getContent(), true);

        $this->debug(sprintf('Subscription notification received: %s; %s', $mission->getId(), $request->getContent()));

        if (!isset($payload['data'])) {
            throw new BadRequestHttpException();
        }

        foreach ($payload['data'] as $data) {
            try {
                $measuredAt = isset($data['https://uri.fiware.org/ns/data-models#dateObserved']['value']['@value'])
                    ? new DateTimeImmutable($data['https://uri.fiware.org/ns/data-models#dateObserved']['value']['@value'])
                    : null;

                if (null === $measuredAt) {
                    $this->debug('Cannot get time of measurement');

                    return new BadRequestHttpException('Cannot get time of measurement');
                }

                $sensorId = $data['id'];
                $sensor = $sensorRepository->find($sensorId);
                if (null === $sensor) {
                    $this->debug(sprintf('Cannot find sensor with id: %s', $sensorId));

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
                    ->setMission($mission);
                $entityManager->persist($measurement);
                $entityManager->flush();
            } catch (\Exception $exception) {
                $this->error($exception->getMessage(), ['exception' => $exception]);
            }
        }

        return new JsonResponse(['status' => 'ok']);
    }
}
