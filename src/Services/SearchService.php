<?php

namespace AIImageTagger\Services;

class SearchService {

    public function enhanceSearch(string $search, \WP_Query $query): string {
        global $wpdb;

        if (empty($search) || !$query->is_search()) {
            return $search;
        }

        // Get search terms
        $searchTerm = $query->get('s');

        if (empty($searchTerm)) {
            return $search;
        }

        // Add meta query for AI-generated content
        $metaSearch = " OR EXISTS (
            SELECT 1 FROM {$wpdb->postmeta}
            WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
            AND {$wpdb->postmeta}.meta_key IN ('_ai_title', '_ai_description', '_ai_tags')
            AND {$wpdb->postmeta}.meta_value LIKE '%" . $wpdb->esc_like($searchTerm) . "%'
        )";

        // Insert into existing search
        $search = preg_replace(
            '/\(\(\((.*?)\)\)\)/',
            '((($1))) ' . $metaSearch,
            $search
        );

        return $search;
    }
}
