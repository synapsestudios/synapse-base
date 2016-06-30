<?php
namespace Synapse\Template;

use Handlebars\Handlebars;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class TemplateService
{
    /**
     * @var CssToInlineStyles
     */
    protected $cssToInlineStyles;

    /**
     * @var Handlebars
     */
    protected $handlebars;

    public function __construct(
        Handlebars $handlebars,
        CssToInlineStyles $cssToInlineStyles
    ) {
        $this->cssToInlineStyles = $cssToInlineStyles;
        $this->handlebars = $handlebars;
    }

    public function renderHbsForEmail($templateName, array $params = []) {
        return $this->renderHbs($templateName, $params, true);
    }

    public function renderHbs($templateName, array $params = [], $inlineCss = false)
    {
        $html = $this->handlebars->render(
            $templateName,
            $params
        );

        if ($inlineCss) {
            $html = $this->cssToInlineStyles->convert($html);
        }

        return $html;
    }
}
