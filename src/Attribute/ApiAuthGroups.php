<?php


namespace App\Attribute;


/**
 *@Annotation(
 * \Attribute(\Attribute::TARGET_CLASS)
 *)
 */
class ApiAuthGroups
{

    public $groups;
    public function __construct($groups)
    {
        $this->groups = $groups;
    }
}
