<?php

namespace FOM\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOM\ManagerBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Generic Manager interface controller.
 *
 * Provides module list and menu for manager interface
 *
 * @author Christian Wygoda
 */
class ManagerController extends Controller
{
    /**
     * Simply redirect to the applications list.
     *
     * @Route("/")
     * @Method("GET")
     */
    public function indexAction()
    {
        $controllers = $this->getManagerControllersDefinition();
        return $this->redirect($this->generateUrl($controllers[0]['route']));
    }

    /**
     * Renders the navigation menu
     *
     * @Template
     */
    public function menuAction($request)
    {
        $current_route = $request->attributes->get('_route');

        $menu = $this->getManagerControllersDefinition();
        foreach($menu as &$item) {
            $item['active'] = false;
            foreach($item['routes'] as $route) {
                if(substr($current_route, 0, strlen($route))
                    === $route){
                    $item['active'] = true;
                }
            }
        }

        return array('menu' => $menu);
    }

    protected function getManagerControllersDefinition()
    {
        $manager_controllers = array();
        foreach($this->get('kernel')->getBundles() as $bundle) {
            if(is_subclass_of($bundle, 'FOM\ManagerBundle\Component\ManagerBundle')) {
                $controllers = $bundle->getManagerControllers();
                if($controllers) {
                    $manager_controllers = array_merge($manager_controllers, $controllers);
                }
            }
        }

        usort($manager_controllers, function($a, $b) {
            if($a['weight'] == $b['weight']) {
                return 0;
            }

            return ($a['weight'] < $b['weight']) ? -1 : 1;
        });

        if(count($manager_controllers) === 0) {
            throw new \RuntimeException('No manager controllers registered');
        }

        return $manager_controllers;
    }
}
