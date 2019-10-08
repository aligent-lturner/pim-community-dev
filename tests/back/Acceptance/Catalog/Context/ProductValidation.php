<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Catalog\Context;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Acceptance\Product\InMemoryProductRepository;
use Akeneo\Test\Common\EntityWithValue\Builder;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Use this context to check product validation rules. Create a product with specific values, valid the product
 * object and check errors.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ProductValidation implements Context
{
    /** @var ProductInterface */
    private $updatedProduct;

    /** @var Builder\Product */
    private $productBuilder;

    /** @var ValidatorInterface */
    private $productValidator;

    /** @var InMemoryProductRepository */
    private $productRepository;

    public function __construct(
        Builder\Product $productBuilder,
        ValidatorInterface $productValidator,
        InMemoryProductRepository $productRepository
    ) {
        $this->productBuilder = $productBuilder;
        $this->productValidator = $productValidator;
        $this->productRepository = $productRepository;
    }

    /**
     * @When another product is created with identifier :identifier
     */
    public function aProductIsCreatedWithIdentifier(string $identifier): void
    {
        $this->updatedProduct = $this->productBuilder->withIdentifier($identifier)->build(false);
    }

    /**
     * @Then the error :errorMessage is raised
     *
     * @throws \Exception
     */
    public function theErrorIsRaised(string $errorMessage): void
    {
        $violations = $this->productValidator->validate($this->updatedProduct);

        if ($violations->count() === 0) {
            throw new \Exception(
                sprintf('Expected error message "%s" but no violation was found', $errorMessage)
            );
        }

        $messages = [];
        $isFoundMessage = false;

        foreach ($violations as $violation) {
            $message = $violation->getMessage();
            $messages[] = $message;
            if ($message === $errorMessage) {
                $isFoundMessage = true;
            }
        }

        if (!$isFoundMessage) {
            throw new \Exception(
                sprintf(
                    'Expected error message "%s" was not found, %s given', $errorMessage,
                    implode(',', $messages)
                )
            );
        }
    }

    /**
     * @Then the error :errorMessage is raised on validation
     */
    public function theErrorIsRaisedOnValidation(string $errorMessage): void
    {
        $this->updatedProduct = $this->productRepository->findOneByIdentifier('my_product');

        $this->theErrorIsRaised($errorMessage);
    }
}
