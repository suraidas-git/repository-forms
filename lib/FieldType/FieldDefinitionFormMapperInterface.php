<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\RepositoryForms\FieldType;

@trigger_error(
    sprintf(
        'Interface %s has been deprecated in eZ Platform 3.0 and is going to be removed in 4.0. Please use %s interface instead.',
        FieldDefinitionFormMapperInterface::class,
        \EzSystems\EzPlatformAdminUi\FieldType\FieldDefinitionFormMapperInterface::class
    ),
    E_DEPRECATED
);

if (!class_exists(\EzSystems\EzPlatformAdminUi\FieldType\FieldDefinitionFormMapperInterface::class)) {
    /**
     * @deprecated Interface FieldDefinitionFormMapperInterface has been deprecated in eZ Platform 3.0
     *             and is going to be removed in 4.0. Please use
     *             \EzSystems\EzPlatformAdminUi\FieldType\FieldDefinitionFormMapperInterface interface instead.
     */
    interface FieldDefinitionFormMapperInterface
    {
    }
}
