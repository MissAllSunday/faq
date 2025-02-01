<?php

namespace Faq\Services;

use Faq\Entities\CategoryEntity;
use Faq\Entities\FaqEntity;
use Faq\Faq;
use Faq\TypeError;
use Faq\ValidatorError;

class FaqValidation
{
    protected FaqEntity|CategoryEntity $entity;
    protected array $dataToSave = [];

    public function isValid(CategoryEntity | FaqEntity $entity, array $dataToSave): string
    {
        $this->entity = $entity;
        $this->dataToSave = $dataToSave;

        try {
            $this->areRequiredFieldsPresent();

            foreach ($this->dataToSave as $key => $value) {
                $type = $this->entity->getColumns()[$key];
                $callback = 'is' . ucfirst($type) . 'Expected';
                $this->{$callback}($value);
            }
        } catch (ValidatorError $error) {
            return $error->getMessage();
        } catch (TypeError $error) {
            fatal_lang_error(Faq::NAME .'_'. $error->getMessage(), false);
        }

        return '';
    }

    /**
     * @throws ValidatorError
     */
    protected function areRequiredFieldsPresent(): void
    {
        $missingFields = array_diff($this->entity->getRequiredFields(), array_keys(array_filter($this->dataToSave)));

        if (!empty($missingFields)) {
            throw new ValidatorError(implode(', ', $missingFields));
        }
    }

    /**
     * @throws TypeError
     */
    protected function isIntExpected($intValue) : void
    {
        if(!is_int($intValue)) {
            throw new TypeError('validation_type');
        }
    }

    /**
     * @throws TypeError
     */
    protected function isStringExpected($stringValue) : void
    {
        if(!is_string($stringValue)) {
            throw new TypeError('validation_type');
        }
    }
}