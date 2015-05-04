<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 20:25
 */

namespace MicronCMS\Templating;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\Templating\Exception\UnresolvedVariableException;


/**
 * Class VariableDefinition
 * @package MicronCMS\Templating
 */
class VariableDefinition implements CompilableInterface
{
    use CompilableDefaults;

    const KEY = 0x000;
    const PROPERTY = 0x001;
    const METHOD = 0x002;
    const AS_ARGUMENT = 0x003;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $action;

    /**
     * @param string $action
     * @param int $type
     */
    public function __construct($action, $type)
    {
        $this->type = $type;
        $this->action = $action;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $prototype
     * @return mixed
     */
    public function extract($prototype)
    {
        switch ($this->type) {
            case self::KEY:
                return $prototype[$this->action];
                break;
            case self::PROPERTY:
                return $prototype->{$this->action};
                break;
            case self::METHOD:
                return $prototype->{$this->action}();
                break;
            case self::AS_ARGUMENT:
                return call_user_func($this->action, $prototype);
                break;
            default: throw new UnresolvedVariableException(sprintf("Unknown variable action type %s", $this->type));
        }
    }

    /**
     * @param mixed $prototype
     * @param array $rawActions
     * @return mixed
     */
    public static function extractVector($prototype, array $rawActions)
    {
        if (empty($rawActions)) {
            return $prototype;
        }

        foreach ($rawActions as $rawAction) {
            list($action, $type) = static::guessDefinitions($prototype, $rawAction);

            $prototype = (new static($action, $type))->extract($prototype);
        }

        return $prototype;
    }

    /**
     * @param mixed $prototype
     * @param string $rawAction
     * @return array
     */
    protected static function guessDefinitions($prototype, $rawAction)
    {
        $action = $rawAction;
        $type = null;

        if (is_array($prototype) || (is_object($prototype) && $prototype instanceof \ArrayAccess)) {
            $type = self::KEY;
        } elseif (is_object($prototype)) {
            if (method_exists($prototype, $action)) {
                $type = self::METHOD;
            } elseif (property_exists($prototype, $action)) {
                $type = self::PROPERTY;
            } elseif (method_exists($prototype, '__get')) {
                $type = self::PROPERTY;
            } elseif (method_exists($prototype, '__call')) {
                $type = self::METHOD;
            } else {
                $type = self::PROPERTY;
            }
        } else {
            $type = self::AS_ARGUMENT;
        }

        return [$action, $type];
    }
}