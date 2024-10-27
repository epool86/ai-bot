<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;

class VectorService
{
    public function cosineSimilarity(array $vector1, array $vector2): float
    {
        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        foreach ($vector1 as $i => $value1) {
            $value2 = $vector2[$i];
            $dotProduct += $value1 * $value2;
            $magnitude1 += $value1 * $value1;
            $magnitude2 += $value2 * $value2;
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    public function findSimilarDocuments(array $queryVector, $documents, int $limit = 5): array
    {
        $similarities = [];
        
        // Convert to array if it's a collection
        $documents = $documents instanceof Collection ? $documents->all() : $documents;

        foreach ($documents as $document) {
            $docVector = is_string($document->content_vector) 
                ? json_decode($document->content_vector, true) 
                : $document->content_vector;

            if (!empty($docVector)) {
                $similarity = $this->cosineSimilarity($queryVector, $docVector);
                $similarities[] = [
                    'document' => $document,
                    'similarity' => $similarity
                ];
            }
        }

        usort($similarities, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($similarities, 0, $limit);
    }
}