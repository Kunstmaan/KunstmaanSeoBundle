<?php

namespace Kunstmaan\SeoBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Kunstmaan\SeoBundle\Entity\Seo,
    Kunstmaan\SeoBundle\Form\SeoType,
    Kunstmaan\SeoBundle\Form\SocialType;

use Kunstmaan\AdminBundle\Helper\FormWidgets\FormWidget;
use Kunstmaan\AdminBundle\Helper\FormWidgets\Tabs\Tab;
use Kunstmaan\NodeBundle\Event\AdaptFormEvent;



/**
 * This will add a seo tab on each page
 */
class NodeListener
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param AdaptFormEvent $event
     */
    public function adaptForm(AdaptFormEvent $event)
    {
    	if($event->getPage() instanceof HasNodeInterface) {
    		/* @var Seo $seo */
    		$seo = $this->em->getRepository('KunstmaanSeoBundle:Seo')->findOrCreateFor($event->getPage());
    		
    		$seoWidget = new FormWidget();
    		$seoWidget->addType('seo', new SeoType(), $seo);
    		$event->getTabPane()->addTab(new Tab('SEO', $seoWidget));
    		
    		$socialWidget = new FormWidget();
    		$socialWidget->addType('social', new SocialType(), $seo);
    		$event->getTabPane()->addTab(new Tab('Social', $socialWidget));
    	}
    }

}
