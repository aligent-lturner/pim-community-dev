<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Family saver, contains custom logic for family's product saving
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySaver implements SaverInterface, BulkSaverInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ObjectManager                  $objectManager
     * @param CompletenessManager            $completenessManager (@deprecated will be removed in 2.1
     *                                                            {@see \Pim\Bundle\CatalogBundle\EventSubscriber\ComputeCompletenessOnFamilyUpdateSubscriber})
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        ObjectManager $objectManager,
        CompletenessManager $completenessManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->objectManager = $objectManager;
        $this->completenessManager = $completenessManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function save($family, array $options = [])
    {
        $this->validateFamily($family);

        $options['unitary'] = true;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($family, $options));

        $this->objectManager->persist($family);

        $this->objectManager->flush();

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($family, $options));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $families, array $options = [])
    {
        if (empty($families)) {
            return;
        }

        $options['unitary'] = false;

        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE_ALL, new GenericEvent($families, $options));

        foreach ($families as $family) {
            $this->validateFamily($family);

            $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($family, $options));

            $this->objectManager->persist($family);
        }

        $this->objectManager->flush();

        foreach ($families as $family) {
            $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($family, $options));
        }

        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE_ALL, new GenericEvent($families, $options));
    }

    /**
     * @param $family
     */
    protected function validateFamily($family)
    {
        if (!$family instanceof FamilyInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Pim\Structure\Component\Model\FamilyInterface", "%s" provided.',
                    ClassUtils::getClass($family)
                )
            );
        }
    }
}
