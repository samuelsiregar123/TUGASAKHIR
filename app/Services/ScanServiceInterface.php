<?php

namespace App\Services;

interface ScanServiceInterface
{
    public function getName(): string;

    /** Returns array of butir_penilaian IDs this tool covers */
    public function getMappedButir(): array;

    /**
     * Run scan and return standardised result array:
     * [tool, target_url, scanned_at, status, findings[], raw_output]
     * findings item: [butir_id, severity, title, description, evidence, raw]
     */
    public function scan(string $url): array;
}
