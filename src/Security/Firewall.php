<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 11:46
 */

namespace MicronCMS\Security;

use MicronCMS\CompilableInterface;
use MicronCMS\Helper\CompilableDefaults;
use MicronCMS\HttpKernel\Request;
use MicronCMS\Security\Policy\PolicyInterface;


/**
 * Class Firewall
 * @package MicronCMS\Security
 */
class Firewall implements CompilableInterface
{
    use CompilableDefaults;

    const ALLOWED = 0x000;
    const DENIED = 0x001;

    /**
     * @var \SplObjectStorage|Rule[]
     */
    protected $rules;

    /**
     * @var \SplObjectStorage|PolicyInterface[]
     */
    protected $policies;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->rules = new \SplObjectStorage();
        $this->policies = new \SplObjectStorage();
    }

    /**
     * @param Rule $rule
     * @return $this
     */
    public function addRule(Rule $rule)
    {
        $this->rules->attach($rule);
        return $this;
    }

    /**
     * @param PolicyInterface $policy
     * @return $this
     */
    public function addPolicy(PolicyInterface $policy)
    {
        $this->policies->attach($policy);
        return $this;
    }

    /**
     * @param Request $request
     * @param bool $allowMissing
     * @return int
     */
    public function decide(Request $request, $allowMissing = true)
    {
        $path = $request->getPath();
        $matchesRule = false;

        foreach ($this->rules as $rule) {
            if ($rule->match($path)) {
                $matchesRule = true;
                break;
            }
        }

        if (!$matchesRule || $this->policies->count() <= 0) {
            return $allowMissing ? self::ALLOWED : self::DENIED;
        }

        foreach ($this->policies as $policy) {
            $decision = $policy->apply($request);

            if ($decision === PolicyInterface::DENY) {
                return self::DENIED;
            }
        }

        return self::ALLOWED;
    }
}