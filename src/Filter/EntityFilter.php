<?php


namespace App\Filter;


use App\Entity\Quote;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class EntityFilter extends SQLFilter
{

    /**
     * Gets the SQL query part to add to a query.
     *
     * @param ClassMetaData $targetEntity
     * @param string $targetTableAlias
     *
     * @return string The constraint SQL if there is available, empty string otherwise.
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if ($targetEntity->getName() != Quote::class) {
            return '';
        }
        $userId = (int)trim($this->getParameter('id'),"'");

        return sprintf('%s.user_id = %d', $targetTableAlias, $userId);

    }

}
