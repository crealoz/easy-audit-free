<?php

namespace Crealoz\EasyAudit\Test\Unit\Service\FileSystem;

use Crealoz\EasyAudit\Exception\Processor\Getters\NotAClassException;
use Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter;
use Crealoz\EasyAudit\Service\FileSystem\ModulePaths;
use Crealoz\EasyAudit\Service\ModuleTools;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;

class ClassNameGetterTest extends TestCase
{
    private $driverMock;
    private $ioMock;
    private $modulePathsMock;
    private $moduleToolsMock;
    private $classNameGetter;

    protected function setUp(): void
    {
        $this->driverMock = $this->createMock(DriverInterface::class);
        $this->ioMock = $this->createMock(File::class);
        $this->modulePathsMock = $this->createMock(ModulePaths::class);
        $this->moduleToolsMock = $this->createMock(ModuleTools::class);

        $this->classNameGetter = new ClassNameGetter(
            $this->driverMock,
            $this->ioMock,
            $this->modulePathsMock,
            $this->moduleToolsMock
        );
    }

    public function testGetClassFullNameFromFile()
    {
        $filePath = 'app/code/Crealoz/EasyAudit/Service/FileSystem/ClassNameGetter.php';
        $fileContent = '<?php namespace Crealoz\EasyAudit\Service\FileSystem; class ClassNameGetter {}';

        $this->driverMock->method('fileGetContents')
            ->with($filePath)
            ->willReturn($fileContent);

        $result = $this->classNameGetter->getClassFullNameFromFile($filePath);

        $this->assertEquals('\Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter', $result);

    }

    public function testGetClassFullNameFromFileVendor()
    {
        $filePath = 'vendor/crealoz/easy-audit/Service/FileSystem/ClassNameGetter.php';
        $fileContent = '<?php namespace Crealoz\EasyAudit\Service\FileSystem; class ClassNameGetter {}';
        $this->ioMock->method('getPathInfo')
            ->with($filePath)
            ->willReturn(['dirname' => 'vendor/crealoz/easy-audit/Service/FileSystem', 'basename' => 'ClassNameGetter.php', 'extension' => 'php', 'filename' => 'ClassNameGetter']);
        $this->moduleToolsMock->method('getModuleNameByAnyFile')
            ->with('vendor/crealoz/easy-audit/Service/FileSystem/ClassNameGetter.php', true)
            ->willReturn('Crealoz_EasyAudit');

        $this->driverMock->method('fileGetContents')
            ->with($filePath)
            ->willReturn($fileContent);

        $result = $this->classNameGetter->getClassFullNameFromFile($filePath);

        $this->assertEquals('\Crealoz\EasyAudit\Service\FileSystem\ClassNameGetter', $result);
    }

    public function testGetClassFullNameFromFileThrowsNotAClassException()
    {
        $this->expectException(NotAClassException::class);
        $this->expectExceptionMessage('The file app/code/Crealoz/EasyAudit/registration.php is a registration file');

        $filePath = 'app/code/Crealoz/EasyAudit/registration.php';

        $this->classNameGetter->getClassFullNameFromFile($filePath);
    }

    public function testGetClassFullNameFromFileThrowsFileSystemException()
    {
        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage('Could not read the file app/code/Crealoz/EasyAudit/Service/FileSystem/UnknownClass.php');

        $filePath = 'app/code/Crealoz/EasyAudit/Service/FileSystem/UnknownClass.php';

        $this->classNameGetter->getClassFullNameFromFile($filePath);
    }

    public function testNorInVendorOrAppCode()
    {
        $this->expectException(FileSystemException::class);
        $this->expectExceptionMessage('The file foo/bar/Crealoz/EasyAudit/Model/Bar.php is not in app/code or vendor');

        $filePath = 'foo/bar/Crealoz/EasyAudit/Model/Bar.php';

        $this->classNameGetter->getClassFullNameFromFile($filePath);
    }

    public function testMissingNamespace()
    {
        $this->expectException(NotAClassException::class);
        $this->expectExceptionMessage('The file app/code/Crealoz/EasyAudit/Service/FileSystem/ClassNameGetter.php does not contain a namespace Crealoz\EasyAudit\Service\FileSystem');
        $filePath = 'app/code/Crealoz/EasyAudit/Service/FileSystem/ClassNameGetter.php';
        $fileContent = '<?php class ClassNameGetter {}';

        $this->driverMock->method('fileGetContents')
            ->with($filePath)
            ->willReturn($fileContent);

        $this->classNameGetter->getClassFullNameFromFile($filePath);
    }

    public function testWrongClassname()
    {
        $this->expectException(NotAClassException::class);
        $this->expectExceptionMessage('The file app/code/Crealoz/EasyAudit/Service/FileSystem/ClassNameGetter.php does not contain a class named ClassNameGetter');
        $filePath = 'app/code/Crealoz/EasyAudit/Service/FileSystem/ClassNameGetter.php';
        $fileContent = '<?php namespace Crealoz\EasyAudit\Service\FileSystem; class Foo {}';

        $this->driverMock->method('fileGetContents')
            ->with($filePath)
            ->willReturn($fileContent);

        $this->classNameGetter->getClassFullNameFromFile($filePath);
    }
}