<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageStorage\Modifier\Facade;

use Intervention\Image\Image;
use SixtyEightPublishers\FileStorage\PathInfoInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Applicator\ModifierApplicatorInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Codec\CodecInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Collection\ModifierCollectionInterface;
use SixtyEightPublishers\ImageStorage\Modifier\ModifierInterface;
use SixtyEightPublishers\ImageStorage\Modifier\Validator\ValidatorInterface;

interface ModifierFacadeInterface
{
    /**
     * @param array<ModifierInterface> $modifiers
     */
    public function setModifiers(array $modifiers): void;

    /**
     * @param array<string, array<string, string|numeric|bool>> $presets
     */
    public function setPresets(array $presets): void;

    /**
     * @param array<ModifierApplicatorInterface> $applicators
     */
    public function setApplicators(array $applicators): void;

    /**
     * @param array<ValidatorInterface> $validators
     */
    public function setValidators(array $validators): void;

    public function getModifierCollection(): ModifierCollectionInterface;

    public function getCodec(): CodecInterface;

    /**
     * @param string|array<string, string|numeric|bool> $modifiers
     */
    public function modifyImage(Image $image, PathInfoInterface $info, string|array $modifiers): Image;
}
