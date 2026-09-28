<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "handlebars".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\Typo3Handlebars\Tests\Functional\DataProcessing\Media;

use CPSIT\Typo3Handlebars as Src;
use CPSIT\Typo3Handlebars\Tests;
use PHPUnit\Framework;
use TYPO3\CMS\Core;
use TYPO3\CMS\Extbase;
use TYPO3\CMS\Frontend;
use TYPO3\TestingFramework;

/**
 * ImageProcessorTest
 *
 * @author Elias Häußler <e.haeussler@familie-redlich.de>
 * @license GPL-2.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\DataProcessing\Media\ImageProcessor::class)]
final class ImageProcessorTest extends TestingFramework\Core\Functional\FunctionalTestCase
{
    use Tests\FrontendRequestTrait;

    protected array $testExtensionsToLoad = [
        'handlebars',
        'typed_extconf',
    ];

    protected array $pathsToProvideInTestInstance = [
        'typo3conf/ext/handlebars/Tests/Functional/Fixtures/Files/Media/' => 'fileadmin/test_media/',
    ];

    /**
     * Disables actual image processing (no GraphicsMagick/ImageMagick required in test environments);
     * TYPO3 then falls back to returning the original file's dimensions unmodified.
     */
    protected array $configurationToUseInTestInstance = [
        'GFX' => [
            'processor_enabled' => false,
        ],
    ];

    private Src\DataProcessing\Media\ImageProcessor $subject;
    private Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer;
    private Core\Resource\File $imageFile;
    private Core\Resource\File $documentFile;

    public function setUp(): void
    {
        parent::setUp();

        $storageRepository = $this->get(Core\Resource\StorageRepository::class);
        $storageUid = $storageRepository->createLocalStorage(
            'fixtures',
            'fileadmin/test_media',
            'relative',
        );
        $storage = $storageRepository->findByUid($storageUid);

        self::assertInstanceOf(Core\Resource\ResourceStorage::class, $storage);

        $imageFile = $storage->getFile('/image.png');
        $documentFile = $storage->getFile('/document.txt');

        self::assertInstanceOf(Core\Resource\File::class, $imageFile);
        self::assertInstanceOf(Core\Resource\File::class, $documentFile);

        $this->imageFile = $imageFile;
        $this->documentFile = $documentFile;

        $request = $this->buildServerRequest();

        $this->subject = new Src\DataProcessing\Media\ImageProcessor();
        $this->contentObjectRenderer = $this->get(Frontend\ContentObject\ContentObjectRenderer::class);
        $this->contentObjectRenderer->setRequest($request);
        $this->get(Extbase\Configuration\ConfigurationManagerInterface::class)->setRequest($request);
        $this->contentObjectRenderer->start(['uid' => 123], 'tt_content');
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueForImageFile(): void
    {
        self::assertTrue($this->subject->supports($this->imageFile));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsFalseForNonImageFile(): void
    {
        self::assertFalse($this->subject->supports($this->documentFile));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueForFileReferenceWrappingImageFile(): void
    {
        $fileReference = new Core\Resource\FileReference(['uid_local' => $this->imageFile->getUid(), 'crop' => '']);

        self::assertTrue($this->subject->supports($fileReference));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueForExtbaseFileWrappingImageFile(): void
    {
        $extbaseFile = new Extbase\Domain\Model\File();
        $extbaseFile->setOriginalResource($this->imageFile);

        self::assertTrue($this->subject->supports($extbaseFile));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsTrueForExtbaseFileReferenceWrappingImageFile(): void
    {
        $fileReference = new Core\Resource\FileReference(['uid_local' => $this->imageFile->getUid(), 'crop' => '']);
        $extbaseFileReference = new Extbase\Domain\Model\FileReference();
        $extbaseFileReference->setOriginalResource($fileReference);

        self::assertTrue($this->subject->supports($extbaseFileReference));
    }

    #[Framework\Attributes\Test]
    public function supportsReturnsFalseForUnsupportedResource(): void
    {
        self::assertFalse($this->subject->supports('not-a-resource'));
        self::assertFalse($this->subject->supports(null));
    }

    #[Framework\Attributes\Test]
    public function processReturnsOriginalFileAndEmptySourceSetsIfNoSourceSetsAreConfigured(): void
    {
        $result = $this->subject->process($this->contentObjectRenderer, $this->imageFile, []);

        $expected = [
            'sourceSets' => [],
            'originalFile' => $this->imageFile,
        ];

        self::assertSame($expected, $result);
    }

    #[Framework\Attributes\Test]
    public function processGeneratesConfiguredSourceSets(): void
    {
        $configuration = [
            'sourceSets' => [
                'default' => [
                    'maxW' => 20,
                ],
                'large' => [
                    'maxW' => 40,
                ],
            ],
        ];

        $result = $this->subject->process($this->contentObjectRenderer, $this->imageFile, $configuration);

        self::assertIsArray($result['sourceSets']);
        self::assertSame(['default', 'large'], array_keys($result['sourceSets']));

        foreach ($result['sourceSets'] as $sourceSet) {
            self::assertIsArray($sourceSet);
            self::assertIsString($sourceSet['src']);
            self::assertNotSame('', $sourceSet['src']);
            self::assertSame(40, $sourceSet['width']);
            self::assertSame(24, $sourceSet['height']);
            self::assertSame('png', $sourceSet['extension']);
            self::assertInstanceOf(Core\Resource\ProcessedFile::class, $sourceSet['processedFile']);
        }

        self::assertSame($this->imageFile, $result['originalFile']);
    }

    #[Framework\Attributes\Test]
    public function processResolvesOriginalFileFromFileReference(): void
    {
        $fileReference = new Core\Resource\FileReference(['uid_local' => $this->imageFile->getUid(), 'crop' => '']);

        $result = $this->subject->process($this->contentObjectRenderer, $fileReference, []);

        self::assertSame($this->imageFile, $result['originalFile']);
    }

    #[Framework\Attributes\Test]
    public function processResolvesOriginalFileFromExtbaseFileReference(): void
    {
        $fileReference = new Core\Resource\FileReference(['uid_local' => $this->imageFile->getUid(), 'crop' => '']);
        $extbaseFileReference = new Extbase\Domain\Model\FileReference();
        $extbaseFileReference->setOriginalResource($fileReference);

        $configuration = [
            'sourceSets' => [
                'default' => [
                    'maxW' => 20,
                ],
            ],
        ];

        $result = $this->subject->process($this->contentObjectRenderer, $extbaseFileReference, $configuration);

        self::assertSame($this->imageFile, $result['originalFile']);
        self::assertIsArray($result['sourceSets']);
        self::assertArrayHasKey('default', $result['sourceSets']);
    }
}
