<?php

namespace App\Services;

use Illuminate\Support\Str;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;

/**
 * Renders article Markdown to HTML while building a table of contents
 * (the "index") from the H2/H3 heading tags in the body. Each rendered
 * heading is given a slugified id so the TOC can deep-link into it.
 */
class ArticleContent
{
    /**
     * @return array{html: string, toc: array<int, array{level:int, text:string, slug:string, children:array}>}
     */
    public function render(?string $markdown): array
    {
        if (blank($markdown)) {
            return ['html' => '', 'toc' => []];
        }

        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new AutolinkExtension());

        $document = (new MarkdownParser($environment))->parse($markdown);

        $headings = [];
        $seen = [];

        foreach ($document->iterator() as $node) {
            if (! $node instanceof Heading || ! in_array($node->getLevel(), [2, 3], true)) {
                continue;
            }

            $text = $this->headingText($node);
            if ($text === '') {
                continue;
            }

            $slug = $this->uniqueSlug($text, $seen);

            // Add the anchor id (and a scroll offset so sticky chrome doesn't
            // cover the heading when jumped to) onto the rendered tag.
            $node->data->set('attributes/id', $slug);
            $node->data->set('attributes/class', 'scroll-mt-24');

            $headings[] = [
                'level' => $node->getLevel(),
                'text' => $text,
                'slug' => $slug,
            ];
        }

        $html = (new HtmlRenderer($environment))->renderDocument($document)->getContent();

        return [
            'html' => $html,
            'toc' => $this->nest($headings),
        ];
    }

    private function headingText(Heading $heading): string
    {
        $text = '';

        foreach ($heading->iterator() as $child) {
            if ($child instanceof Text || $child instanceof Code) {
                $text .= $child->getLiteral();
            }
        }

        return trim($text);
    }

    /**
     * @param  array<string, bool>  $seen
     */
    private function uniqueSlug(string $text, array &$seen): string
    {
        $base = Str::slug($text) ?: 'section';
        $slug = $base;
        $i = 1;

        while (isset($seen[$slug])) {
            $slug = $base.'-'.(++$i);
        }

        $seen[$slug] = true;

        return $slug;
    }

    /**
     * Nest H3 entries beneath the preceding H2 so the TOC renders as a tree.
     *
     * @param  array<int, array{level:int, text:string, slug:string}>  $headings
     */
    private function nest(array $headings): array
    {
        $toc = [];

        foreach ($headings as $heading) {
            if ($heading['level'] === 3 && ! empty($toc)) {
                $toc[array_key_last($toc)]['children'][] = $heading;

                continue;
            }

            $toc[] = $heading + ['children' => []];
        }

        return $toc;
    }
}
