<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 11:52
 */

namespace MicronCMS\Security;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;


/**
 * Class Rule
 * @package MicronCMS\Security
 */
class Rule implements CompilableInterface
{
    use CompilableDefaults;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @param string $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function match($path)
    {
        return (bool)preg_match(sprintf('~^%s$~ui', $this->expression), $path);
    }
}