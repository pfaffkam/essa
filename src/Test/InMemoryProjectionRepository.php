<?php

namespace PfaffKIT\Essa\Test;

use PfaffKIT\Essa\EventSourcing\Projection\Projection;
use PfaffKIT\Essa\EventSourcing\Projection\ProjectionRepository;
use PfaffKIT\Essa\Shared\Identity;

class InMemoryProjectionRepository implements ProjectionRepository
{
    private array $data = [];

    public function save(Projection $projection): void
    {
        $this->data[(string) $projection->id] = $projection;
    }

    public function getById(Identity $id): ?Projection
    {
        return $this->data[(string) $id] ?? null;
    }

    public function findBy(array $criteria): array
    {
        if (empty($criteria)) {
            return array_values($this->data);
        }

        return array_values(array_filter($this->data, function ($item) use ($criteria) {
            return $this->matchesItem($item, $criteria);
        }));
    }

    /**
     * Recursively checks if an item matches the given criteria.
     */
    private function matchesItem($item, $criteria): bool
    {
        // First handle regular field conditions (implicit AND)
        $regularConditions = [];
        $specialOperators = [];

        // Separate regular conditions from special operators
        foreach ($criteria as $field => $condition) {
            if (in_array($field, ['$or', '$and'])) {
                $specialOperators[$field] = $condition;
            } else {
                $regularConditions[$field] = $condition;
            }
        }

        // Check regular conditions (implicit AND)
        foreach ($regularConditions as $field => $condition) {
            if (!isset($item->$field)) {
                return false;
            }

            $fieldValue = $item->$field;

            // Handle nested conditions
            if (is_array($condition)) {
                // Check if this is an operator or a nested condition
                $isOperator = false;
                foreach (array_keys($condition) as $key) {
                    if (str_starts_with($key, '$')) {
                        $isOperator = true;
                        if (!$this->evaluateOperatorCondition($fieldValue, $key, $condition[$key])) {
                            return false;
                        }
                    }
                }

                // If it's not an operator, treat it as a nested condition
                if (!$isOperator && !$this->matchesItem($fieldValue, $condition)) {
                    return false;
                }
            } else {
                // Simple equality check
                if ($fieldValue != $condition) {
                    return false;
                }
            }
        }

        // Handle special operators if they exist
        if (!empty($specialOperators)) {
            // Handle $or operator (at least one condition must be true)
            if (isset($specialOperators['$or']) && is_array($specialOperators['$or'])) {
                $orMatched = false;
                foreach ($specialOperators['$or'] as $orCondition) {
                    if ($this->matchesItem($item, $orCondition)) {
                        $orMatched = true;
                        break;
                    }
                }
                if (!$orMatched) {
                    return false;
                }
            }

            // Handle $and operator (all conditions must be true)
            if (isset($specialOperators['$and']) && is_array($specialOperators['$and'])) {
                if (array_any($specialOperators['$and'], fn ($andCondition) => !$this->matchesItem($item, $andCondition))) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Evaluates a single operator condition.
     */
    private function evaluateOperatorCondition($fieldValue, string $operator, $value): bool
    {
        switch ($operator) {
            case '$ne':
                return $fieldValue != $value;
            case '$gt':
                return $fieldValue > $value;
            case '$gte':
                return $fieldValue >= $value;
            case '$lt':
                return $fieldValue < $value;
            case '$lte':
                return $fieldValue <= $value;
            case '$in':
                return is_array($value) && in_array($fieldValue, $value, true);
            case '$nin':
                return !is_array($value) || !in_array($fieldValue, $value, true);
            case '$exists':
                return (bool) $value === isset($fieldValue);
            case '$regex':
                return (bool) preg_match("/$value/", (string) $fieldValue);
            case '$or':
                if (!is_array($value)) {
                    return false;
                }

                return array_any($value, fn ($orCondition) => $this->evaluateOperatorCondition($fieldValue, key($orCondition), current($orCondition)));
            case '$and':
                if (!is_array($value)) {
                    return false;
                }

                return array_all($value, fn ($andCondition) => $this->evaluateOperatorCondition($fieldValue, key($andCondition), current($andCondition)));
            default:
                return $fieldValue == $value;
        }
    }

    public function findOneBy(array $criteria): ?Projection
    {
        $results = $this->findBy($criteria);

        return $results[0] ?? null;
    }

    public function deleteBy(array $criteria): int
    {
        $itemsToDelete = $this->findBy($criteria);
        $deletedCount = 0;

        foreach ($itemsToDelete as $item) {
            if (isset($this->data[(string) $item->id])) {
                unset($this->data[(string) $item->id]);
                ++$deletedCount;
            }
        }

        return $deletedCount;
    }

    public static function getProjectionClass(): string
    {
        return Projection::class;
    }
}
