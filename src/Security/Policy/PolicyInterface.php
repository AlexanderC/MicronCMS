<?php
/**
 * Created by PhpStorm.
 * User: AlexanderC <alexanderc@pycoding.biz>
 * Date: 5/4/15
 * Time: 11:48
 */

namespace MicronCMS\Security\Policy;

use MicronCMS\HttpKernel\Request;


/**
 * Interface PolicyInterface
 * @package MicronCMS\Security\Policy
 */
interface PolicyInterface 
{
    const ALLOW = 0x000;
    const DENY = 0x001;
    const ABSTAIN = 0x002;

    /**
     * @param Request $request
     * @return int
     */
    public function apply(Request $request);
}