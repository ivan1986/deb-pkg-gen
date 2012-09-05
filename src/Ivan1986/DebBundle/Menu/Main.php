<?php
// src/Acme/DemoBundle/Menu/Builder.php
namespace Ivan1986\DebBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Ivan1986\DebBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class Main extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $auth = $this->container->get('security.context')->getToken()->getUser() instanceof User;
        $translator = $this->container->get('translator');
        /** @var $translator Translator */
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav');

        $menu->addChild($translator->trans('Все репозитории'), array(
                'route' => 'repos',
                'routeParameters' => array('my' => 'all')
            ));
        if ($auth) $menu->addChild($translator->trans('Мои репозитории'), array(
                'route' => 'repos',
                'routeParameters' => array('my' => 'my')
            ));
        $menu->addChild($translator->trans('Все пакеты'), array(
                'route' => 'packages',
                'routeParameters' => array('my' => 'all')
            ));
        if ($auth) $menu->addChild($translator->trans('Мои пакеты'), array(
                'route' => 'packages',
                'routeParameters' => array('my' => 'my')
            ));
        // ... add more children
        $menu->addChild($translator->trans('API'), array(
                'route' => 'nelmio_api_doc_index',
            ));

        return $menu;
    }
}