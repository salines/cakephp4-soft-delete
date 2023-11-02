<?php
namespace SoftDelete\ORM\Query;

use ArrayObject;
use Cake\ORM\Query\SelectQuery as CakeSelectQuery;

class SelectQuery extends CakeSelectQuery
{
    /**
     * Cake\ORM\Query::triggerBeforeFind overwritten to add the condition `deleted IS NULL` to every find request
     * in order not to return soft deleted records.
     * If the query contains the option `withDeleted` the condition `deleted IS NULL` is not applied.
     */
    public function triggerBeforeFind(): void
    {
        if (!$this->_beforeFindFired && $this->_type === 'select') {
            $this->_beforeFindFired = true;

            $repository = $this->getRepository();
            $repository->dispatchEvent('Model.beforeFind', [
                $this,
                new ArrayObject($this->_options),
                !$this->isEagerLoaded(),
            ]);

            $aliasedField = $this
                ->getRepository()
                ->aliasField($this->getRepository()->getSoftDeleteField());
            if (!is_array($this->getOptions()) || !in_array('withDeleted', $this->getOptions())) {
                $this->andWhere($aliasedField . ' IS NULL');
            }
        }
    }
}
