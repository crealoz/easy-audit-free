<?php

namespace Crealoz\EasyAudit\Tests\Service;

use Crealoz\EasyAudit\Service\Localization;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Phrase;
use Magento\Framework\TranslateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LocalizationTest extends TestCase
{
    private $filesystem;
    private $moduleReader;
    private $translator;
    private $translateRenderer;
    private $appState;
    private $logger;
    private $localization;

    private string $moduleDir = '/path/to/module/i18n';

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->moduleReader = $this->createMock(Reader::class);
        $this->translator = $this->createMock(TranslateInterface::class);
        $this->translateRenderer = $this->createMock(\Magento\Framework\Phrase\Renderer\Translate::class);
        $this->appState = $this->createMock(State::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->directoryRead = $this->createMock(\Magento\Framework\Filesystem\Directory\Read::class);
        $this->directoryRead->method('read')
            ->willReturn(['en_US.csv', 'fr_FR.csv']);

        $this->moduleReader->method('getModuleDir')
            ->with(\Magento\Framework\Module\Dir::MODULE_I18N_DIR, 'Crealoz_EasyAudit')
            ->willReturn($this->moduleDir);

        $this->filesystem->method('getDirectoryReadByPath')
            ->with($this->moduleDir)
            ->willReturn($this->directoryRead);

        $this->localization = new Localization(
            $this->filesystem,
            $this->moduleReader,
            $this->translator,
            $this->translateRenderer,
            $this->appState,
            $this->logger
        );
    }

    public function testGetAvailableLanguages()
    {

        $resultDirectoryRead = $this->filesystem->getDirectoryReadByPath($this->moduleDir);
        $this->assertNotNull($resultDirectoryRead);

        $result = $this->localization->getAvailableLanguages();

        $this->assertEquals(['en_US', 'fr_FR'], $result);
    }

    public function testInitializeLanguage()
    {
        $locale = 'fr_FR';
        $this->appState->method('setAreaCode')
            ->will($this->throwException(new \Exception()));

        $this->translator->expects($this->once())
            ->method('setLocale')
            ->with($locale);

        $this->translator->expects($this->once())
            ->method('loadData')
            ->with(Area::AREA_GLOBAL, true);

        $result = $this->localization->initializeLanguage($locale);
        $this->assertEquals($locale, $result);
    }

    protected function tearDown(): void
    {
        unset($this->filesystem);
        unset($this->moduleReader);
        unset($this->translator);
        unset($this->translateRenderer);
        unset($this->appState);
        unset($this->logger);
        unset($this->localization);
        Phrase::setRenderer(new \Magento\Framework\Phrase\Renderer\Placeholder());
    }
}