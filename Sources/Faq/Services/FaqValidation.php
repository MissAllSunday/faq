<?php

namespace Faq\Services;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\ValidatorError;

class FaqValidation
{
    protected FaqEntity|CategoryEntity $entity;
    protected array $dataToSave = [];

    public function isValid(CategoryEntity | FaqEntity $entity, array $dataToSave): bool
    {
        $this->entity = $entity;
        $this->dataToSave = $dataToSave;

        try {
            $this->areRequiredFieldsPresent();

            foreach ($this->entity->getColumns() as $valueName => $type) {
                $callback = 'is' . ucfirst($type) . 'Expected';
                $callback($this->dataToSave[$valueName]);
            }
        } catch (ValidatorError $error) {
            return false;
        }

        return true;
    }

    /**
     * @throws ValidatorError
     */
    protected function areRequiredFieldsPresent(): void
    {
        if (!empty(array_diff_key($this->entity->getColumns(), $this->dataToSave))) {
            throw new ValidatorError();
        }
    }

    /**
     * @throws ValidatorError
     */
    protected function isIntExpected($intValue) : void
    {
        if(!is_int($intValue)) {
            throw new ValidatorError();
        }
    }

    /**
     * @throws ValidatorError
     */
    protected function isStringExpected($stringValue) : void
    {
        if(!is_string($stringValue)) {
            throw new ValidatorError();
        }
    }
}