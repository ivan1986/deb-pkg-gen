<?php

// src/Acme/DemoBundle/Menu/Builder.php

namespace Ivan1986\DebBundle\Menu;

use Ivan1986\DebBundle\Entity\User;
use Knp\Menu\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Main implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $auth = $this->container->get('security.token_storage')->getToken()->getUser() instanceof User;
        $translator = $this->container->get('translator');
        /** @var $translator Translator */
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild($translator->trans('Все репозитории'), [
                'route' => 'repos',
                'routeParameters' => ['my' => 'all'],
            ]);
        if ($auth) {
            $menu->addChild($translator->trans('Мои репозитории'), [
                'route' => 'repos',
                'routeParameters' => ['my' => 'my'],
            ]);
        }
        /*
        $menu->addChild($translator->trans('Все пакеты'), array(
                'route' => 'packages',
                'routeParameters' => array('my' => 'all')
            ));
        if ($auth) $menu->addChild($translator->trans('Мои пакеты'), array(
                'route' => 'packages',
                'routeParameters' => array('my' => 'my')
            ));
        */
        // ... add more children
        $menu->addChild($translator->trans('API'), [
                'route' => 'nelmio_api_doc_index',
            ]);

        return $menu;
    }
}
