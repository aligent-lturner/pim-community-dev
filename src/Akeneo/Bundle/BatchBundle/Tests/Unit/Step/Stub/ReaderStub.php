<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Step\Stub;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

class ReaderStub extends AbstractConfigurableStepElement implements ItemReaderInterface, StepExecutionAwareInterface
{
    /**
     * {@inheritDoc}
     */
    public function read()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
    }

    public function getConfigurationFields()
    {
    }
}
