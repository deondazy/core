<?php

declare(strict_types=1);

namespace Deondazy\Core\Validation;

use Valitron\Validator;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;
use Deondazy\Core\Exceptions\ValidationException;

class FormDataValidation
{
    private EntityManagerInterface $entityManager;
    
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $validator = new Validator($data);

        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule);
                $ruleName = $ruleParts[0];

                match ($ruleName) {
                    'min' => $this->applyMinRule(
                        $validator,
                        $field,
                        $ruleParts[1]
                    ),
                    'max' => $this->applyMaxRule(
                        $validator,
                        $field,
                        $ruleParts[1]
                    ),
                    'unique' => $this->applyUniqueRule(
                        $validator,
                        $field,
                        $ruleParts[1]
                    ),
                    default => $this->applyStandardRule(
                        $validator,
                        $rule,
                        $field
                    ),
                };
            }
        }

        if ($validator->validate()) {
            return true;
        }

        $this->errors = $validator->errors();

        throw new ValidationException($this->errors);
    }

    private function applyMinRule(
        Validator $validator,
        string $field,
        string $value
    ): void {
        $validator->rule('lengthMin', $field, $value);
    }

    private function applyMaxRule(
        Validator $validator,
        string $field,
        string $value
    ): void {
        $validator->rule('lengthMax', $field, $value);
    }

    private function applyStandardRule(
        Validator $validator,
        string $rule,
        string $field
    ): void {
        $validator->rule($rule, $field);
    }

    private function applyUniqueRule(
        Validator $validator,
        string $field,
        string $pluralSnakeCaseTableName
    ): void {
        $inflector = InflectorFactory::create()->build();

        $singularSnakeCaseTableName = $inflector
            ->singularize($pluralSnakeCaseTableName);
        $classCaseName = $inflector
            ->classify($singularSnakeCaseTableName);

        $className = "Deondazy\\App\\Database\\Entities\\" . $classCaseName;

        $validator->rule(function (
            string $field,
            string $value,
            array $params,
            array $fields
        ) use ($className) {
            return !$this->entityManager
                ->getRepository($className)
                ->count([$field => $value]);
        }, $field)->message('{field} is already in use.');
    }

    public function setValidationEntityManager(
        EntityManagerInterface $entityManager
    ): self {
        $this->entityManager = $entityManager;

        return $this;
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
