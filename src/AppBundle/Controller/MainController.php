<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Helper\MapHelper;
use Ivory\GoogleMap\Overlays\Animation;
use Ivory\GoogleMap\Overlays\Marker;

use GuzzleHttp\Pool;
use GuzzleHttp\Client;

class MainController extends Controller
{
    /**
     * @Route("/", name="getHTML5Geolocation")
     * @Method("GET")
     */
    public function getHTML5GeolocationAction()
    {
        return $this->render('AppBundle:Main:getHTML5Geolocation.html.twig');
    }

    /**
     * @Route("/", name="setUserGeolocation")
     * @Method("POST")
     */
    public function setUserGeolocationAction(Request $request)
    {
        $latitude = $request->request->get('latitude');
        $longitude = $request->request->get('longitude');

        if (!$this->get('session')->isStarted()) {
            $session = new Session();
            $session->start();
        }
        $this->get('session')->set('latitude', $latitude);
        $this->get('session')->set('longitude', $longitude);
        $return = json_encode(array("success" => true));

        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @Route("/main", name="main")
     */
    public function mainAction()
    {
        //If there's no session we need to retrieve user geolocation
        if (!$this->get('session')->has('latitude')) {
            return $this->redirect($this->generateUrl('getHTML5Geolocation'));
        }

        $latitude = $this->get('session')->get('latitude');
        $longitude = $this->get('session')->get('longitude');
        //echo $latitude; exit;
        //$map = $this->createMap();
        //return $this->render('AppBundle:Main:main.html.twig', array('map' => $map));
        
        $client = new Client();
        $req = $client->request('GET', 'https://api.darksky.net/forecast/ea76e78f539ef7dae1879fd1a45d3628/'.$latitude.','.$longitude);
        $decode = json_decode($req->getBody());
        $lugar = $decode->timezone;
        $climaActual = $decode->currently;

        return $this->render('AppBundle:User:home.html.twig',
            ['climaActual' => $climaActual,
             'lugar' => $lugar
            ]);
    }
}