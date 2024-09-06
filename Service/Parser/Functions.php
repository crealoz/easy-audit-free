<?php

namespace Crealoz\EasyAudit\Service\Parser;

use SplFileObject;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Functions
{

    /**
     * @throws \ReflectionException
     */
    public function getFunctionContent(string $class, string $filePath, string $functionName) : string
    {
        $reflection = new \ReflectionClass($class);
        $method = $reflection->getMethod($functionName);
        $startLine = $method->getStartLine();
        $endLine = $method->getEndLine();
        $length = $endLine - $startLine;

        $file = new SplFileObject($filePath);
        $file->seek($startLine-1);
        $content = '';
        for ($i = 0; $i <= $length; $i++) {
            $content .= $file->current();
            $file->next();
        }
        return $content;
    }
}
