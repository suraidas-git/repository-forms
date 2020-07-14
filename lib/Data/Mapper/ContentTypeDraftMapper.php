<?php

/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\RepositoryForms\Data\Mapper;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeCreateStruct;
use EzSystems\RepositoryForms\Data\ContentTypeCreateData;
use EzSystems\RepositoryForms\Data\ContentTypeData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\Event\FieldDefinitionMappingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypeDraftMapper implements FormDataMapperInterface
{
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Maps a ValueObject from eZ content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param ValueObject|\eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     *
     * @return ContentTypeData
     */
    public function mapToFormData(ValueObject $contentTypeDraft, array $params = [])
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);
        $params = $optionsResolver->resolve($params);

        /** @var \eZ\Publish\API\Repository\Values\Content\Language $language */
        $language = $params['language'] ?? null;

        /** @var \eZ\Publish\API\Repository\Values\Content\Language|null $baseLanguage */
        $baseLanguage = $params['baseLanguage'] ?? null;

        $contentTypeData = new ContentTypeData(['contentTypeDraft' => $contentTypeDraft]);
        if (!$contentTypeData->isNew()) {
            $contentTypeData->identifier = $contentTypeDraft->identifier;
        }

        $contentTypeData->remoteId = $contentTypeDraft->remoteId;
        $contentTypeData->urlAliasSchema = $contentTypeDraft->urlAliasSchema;
        $contentTypeData->nameSchema = $contentTypeDraft->nameSchema;
        $contentTypeData->isContainer = $contentTypeDraft->isContainer;
        $contentTypeData->mainLanguageCode = $contentTypeDraft->mainLanguageCode;
        $contentTypeData->defaultSortField = $contentTypeDraft->defaultSortField;
        $contentTypeData->defaultSortOrder = $contentTypeDraft->defaultSortOrder;
        $contentTypeData->defaultAlwaysAvailable = $contentTypeDraft->defaultAlwaysAvailable;
        $contentTypeData->names = $contentTypeDraft->getNames();
        $contentTypeData->descriptions = $contentTypeDraft->getDescriptions();

        $contentTypeData->languageCode = $language ? $language->languageCode : $contentTypeDraft->mainLanguageCode;

        if ($baseLanguage && $language) {
            $contentTypeData->names[$language->languageCode] = $contentTypeDraft->getName($baseLanguage->languageCode);
            $contentTypeData->descriptions[$language->languageCode] = $contentTypeDraft->getDescription($baseLanguage->languageCode);
        }

        foreach ($contentTypeDraft->fieldDefinitions as $fieldDef) {
            $fieldDefinitionData = new FieldDefinitionData([
                'fieldDefinition' => $fieldDef,
                'contentTypeData' => $contentTypeData,
            ]);

            $event = new FieldDefinitionMappingEvent(
                $fieldDefinitionData,
                $baseLanguage,
                $language
            );

            $this->eventDispatcher->dispatch(
                FieldDefinitionMappingEvent::NAME,
                $event
            );

            $contentTypeData->addFieldDefinitionData($event->getFieldDefinitionData());
        }
        $contentTypeData->sortFieldDefinitions();

        return $contentTypeData;
    }

    /**
     * Maps given ContentTypeCreateData object to a ContentTypeCreateStruct object.
     */
    public function reverseMap(ContentTypeCreateData $data, string $mainLanguageCode): ContentTypeCreateStruct
    {
        $createStruct = new ContentTypeCreateStruct([
            'mainLanguageCode' => $mainLanguageCode,
            'names' => [$mainLanguageCode => $data->getName()],
            'identifier' => $data->identifier,
            'descriptions' => null !== $data->getDescription() ? [$mainLanguageCode => $data->getDescription()] : null,
            'nameSchema' => $data->nameSchema,
            'urlAliasSchema' => $data->urlAliasSchema,
            'isContainer' => $data->isContainer,
            'defaultSortField' => $data->defaultSortField,
            'defaultSortOrder' => $data->defaultSortOrder,
            'defaultAlwaysAvailable' => $data->defaultAlwaysAvailable,
        ]);

        $fieldTypeIdentifier = $data->getFieldTypeSelection();
        $fieldDefinitionIdentifier = sprintf('new_%s_%d', $fieldTypeIdentifier, 1);

        $fieldDefCreateStruct = new FieldDefinitionCreateStruct([
            'fieldTypeIdentifier' => $fieldTypeIdentifier,
            'identifier' => $fieldDefinitionIdentifier,
            'names' => [$mainLanguageCode => 'New FieldDefinition'],
            'position' => 1,
        ]);

        $createStruct->addFieldDefinition($fieldDefCreateStruct);

        return $createStruct;
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setDefined(['language'])
            ->setDefined(['baseLanguage'])
            ->setAllowedTypes('baseLanguage', ['null', Language::class])
            ->setAllowedTypes('language', Language::class);
    }
}
