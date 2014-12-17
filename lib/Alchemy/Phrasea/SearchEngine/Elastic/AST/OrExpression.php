<?php

namespace Alchemy\Phrasea\SearchEngine\Elastic\AST;

class OrExpression extends BinaryOperator
{
    protected $operator = 'OR';

    public function getQuery($fields = ['_all'])
    {
        $left  = $this->left->getQuery($fields);
        $right = $this->right->getQuery($fields);

        return array(
            'bool' => array(
                'should' => array($left, $right)
            )
        );
    }
}