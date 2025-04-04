<?php


namespace Crealoz\EasyAudit\Service;

class PrepareMarkdownBody
{
    public function execute($result)
    {
        $body = $result->getSummary();
        $body .= "\n\n### Entries\n";
        foreach ($result->getEntries() as $entry) {
            $body .= "\n\n" . $entry->getEntry();
            foreach ($entry->getSubEntries() as $subEntry) {
                $body .= "\n- " . $subEntry->getSubentry();
            }
        }
        return $body;
    }
}
