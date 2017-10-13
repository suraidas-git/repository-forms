<?php
/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\RepositoryForms\Limitation\Exception;

use Exception;
use InvalidArgumentException;

class ValueMapperNotFoundException extends InvalidArgumentException
{
    public function __construct($limitationType, $code = 0, Exception $previous = null)
    {
        parent::__construct("No LimitationValueMapper found for '$limitationType'", $code, $previous);
    }
}
