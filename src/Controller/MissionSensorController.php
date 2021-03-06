<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\MissionSensor;
use App\Form\Type\MissionSensorType;
use App\Repository\MissionSensorRepository;
use App\Scorpio\SensorManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/mission/{mission}/sensor", name="mission_sensor_")
 */
class MissionSensorController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(Mission $mission, MissionSensorRepository $missionSensorRepository): Response
    {
        return $this->render('mission_sensor/index.html.twig', [
            'mission' => $mission,
            'mission_sensors' => $missionSensorRepository->findByMission($mission),
        ]);
    }

    /**
     * @Route("/add", name="add", methods={"GET"})
     */
    public function add(Request $request, Mission $mission): Response
    {
        return $this->render('mission_sensor/add.html.twig', [
            'mission' => $mission,
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(Request $request, Mission $mission, SensorManager $sensorManager, TranslatorInterface $translation): Response
    {
        $sensorId = $request->query->get('sensor') ?? '';
        try {
            $sensor = $sensorManager->getSensor($sensorId);
            $missionSensor = (new MissionSensor())
                ->setSensor($sensor);
            $mission->addMissionSensor($missionSensor);

            return $this->edit($request, $missionSensor);
        } catch (Exception $exception) {
            $this->addFlash('danger', $translation->trans('Error adding sensor %sensor% (Message: “%message%”)', [
                '%sensor%' => $sensorId,
                '%message%' => $exception->getMessage(),
            ]));

            return $this->redirectToRoute('mission_sensor_add', ['mission' => $mission->getId()]);
        }
    }

    /**
     * @Route("/search", name="search", methods={"GET"})
     */
    public function search(Request $request, Mission $mission, SensorManager $sensorManager): Response
    {
        $missionSensors = [];
        foreach ($mission->getMissionSensors() as $missionSensor) {
            $missionSensors[$missionSensor->getSensor()->getId()] = [
                'id' => $missionSensor->getId(),
                'name' => $missionSensor->getName(),
            ];
        }
        $options = [
            'mission_sensors' => $missionSensors,
            'query' => $request->query->all(),
        ];
        $sensors = $sensorManager->search($options);

        return new JsonResponse($sensors);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     */
    public function show(MissionSensor $missionSensor): Response
    {
        return $this->render('mission_sensor/show.html.twig', [
            'mission_sensor' => $missionSensor,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(Request $request, MissionSensor $missionSensor): Response
    {
        $form = $this->createForm(MissionSensorType::class, $missionSensor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($missionSensor);
            $em->flush();

            return $this->redirectToRoute('mission_show', ['id' => $missionSensor->getMission()->getId()]);
        }

        $deleteForm = null;

        if (null !== $missionSensor->getId()) {
            $deleteForm = $this->createFormBuilder()
                ->setMethod('DELETE')
                ->setAction($this->generateUrl('mission_sensor_delete', ['mission' => $missionSensor->getMission()->getId(), 'id' => $missionSensor->getId()]))
                ->getForm();
        }

        return $this->render('mission_sensor/edit.html.twig', [
            'mission_sensor' => $missionSensor,
            'form' => $form->createView(),
            'delete_form' => null !== $deleteForm ? $deleteForm->createView() : null,
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, MissionSensor $missionSensor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$missionSensor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($missionSensor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('mission_show', ['id' => $missionSensor->getMission()->getId()]);
    }
}
