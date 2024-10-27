<?php

namespace App\Services;

use Spatie\PdfToText\Pdf;
use Smalot\PdfParser\Parser;
use PhpOffice\PhpWord\IOFactory;

class DocumentProcessor
{
    public function extractText(string $filePath, string $fileType): string
    {
        $fullPath = storage_path('app/public/' . $filePath);
        
        return match ($fileType) {
            'pdf' => $this->extractFromPdf($fullPath),
            'doc', 'docx' => $this->extractFromWord($fullPath),
            'txt' => $this->extractFromTxt($fullPath),
            default => throw new \Exception('Unsupported file type'),
        };
    }

    private function extractFromPdf(string $path): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }

    private function extractFromWord(string $path): string
    {
        $phpWord = IOFactory::load($path);
        $text = '';
        
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                }
            }
        }
        
        return $text;
    }

    private function extractFromTxt(string $path): string
    {
        return file_get_contents($path);
    }
}