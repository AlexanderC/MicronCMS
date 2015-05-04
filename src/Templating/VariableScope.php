<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 19:58
 */

namespace MicronCMS\Templating;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;


/**
 * Class VariableScope
 * @package MicronCMS\Templating
 */
class VariableScope implements CompilableInterface
{
    use CompilableDefaults;

    const T_VAR_REGEXP = '/(\${\s*(?P<var>[^\s](?:[^}]*[^\s])?)\s*})/ui';

    /**
     * @var array
     */
    protected $variables;

    /**
     * @param array $variables
     */
    public function __construct(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * @param \Iterator $iterator
     * @return static
     */
    public static function fromIterator(\Iterator $iterator)
    {
        return new static(iterator_to_array($iterator));
    }


    /**
     * @param $content
     * @return string
     */
    public function inject($content)
    {
        return preg_replace_callback(self::T_VAR_REGEXP, function ($matches) {
            $variableDefinition = $matches['var'];

            return $this->resolveVariable($variableDefinition);
        }, $content);
    }

    /**
     * @param string $variableDefinition
     * @return mixed
     */
    protected function resolveVariable($variableDefinition)
    {
        if (empty($variableDefinition)) {
            return '';
        }

        $actionsVector = array_map('trim', explode('.', $variableDefinition));
        $prototype = $this->variables[array_shift($actionsVector)];

        return VariableDefinition::extractVector($prototype, $actionsVector);
    }
}