<?php
declare(strict_types=1);

namespace Template;

use Helper\General;

/**
 * Simple Template Engine
 *
 * Simplified Template engine to load a template file and replace keys by variables in it.
 *
 */
class SimpleTemplateEngine {

    /**
     * @var string Base template path
     */
    protected string $templatePath;

    /**
     * @var string Default template file extension
     */
    protected string $templateFileExt = '.html';

    /**
     * Template marker
     *
     * @var array|string[]
     */
    protected array $templateMarkers = [
        'start' => '###',
        'end' => '###'
    ];

    /**
     * @var string|null Loaded template string
     */
    protected string|null $template = null;

    /**
     * @var array Array of key/value pairs as variables to replace on rendering
     */
    protected array $templateVariables = [];

    /**
     * Constructor
     * Set default values like template Path
     *
     * @param string|null $templatePath
     */
    public function __construct(string|null $templatePath = null) {
        // Prepare path
        $executingScriptPath = General::getCallerPath();
        $defaultTemplatePath = implode(DIRECTORY_SEPARATOR, [
            // Ends with an empty value to add an ending slash to the path
            $executingScriptPath, 'Resources', 'Private', 'Templates', ''
        ]);

        // Use constructor path or default path instead
        $this->templatePath = $templatePath ?: $defaultTemplatePath;
    }

    /**
     * Loads a template file
     *
     * @param string $templateName
     * @return bool
     */
    public function loadTemplate(string $templateName): bool {
        $filePathAndName = $this->templatePath . $templateName . $this->templateFileExt;

        // Load template content if file exists
        if ( file_exists($filePathAndName) ) {
            $this->template = file_get_contents($filePathAndName);
        } else throw new \Exception('Template file not found: ' . $filePathAndName);

        return false;
    }

    public function setTemplateExtension(string $templateExtension): void {
        $this->templateFileExt = str_starts_with($templateExtension, '.') ? $templateExtension : '.' . $templateExtension;
    }

    /**
     * Just returns a template content without rendering or whatever
     *
     * @param string $templateName
     * @return string|false
     */
    public function getTemplate(string $templateName): string|false {
        $filePathAndName = $this->templatePath . $templateName . $this->templateFileExt;

        // Load template content if file exists
        if ( file_exists($filePathAndName) ) {
            return file_get_contents($filePathAndName);
        }

        return false;
    }

    /**
     * Assign a template variable
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function assignVar(string $name, string $value): bool {
        $this->templateVariables[$name] = $value;
        return true;
    }

    /**
     * Assign multiple key/value template variables
     *
     * @param array $variables
     * @return bool
     */
    public function assignMultiple(array $variables): bool {
        if ( is_array($variables) && count($variables) > 0 ) {
            foreach ( $variables as $name => $value ) {
                $this->assignVar($name, $value);
            }

            return true;
        }

        return false;
    }

    /**
     * Renderes a loaded template based on assigned variables
     *
     * @return string
     */
    public function render(): string|false {
        if ( !$this->template )
            return false;

        // Prepare result
        $renderedContent = $this->template;

        // Replace variables by values in cloned content
        if ( sizeof($this->templateVariables) > 0 ) {
            foreach ( $this->templateVariables as $name => $value ) {
                // Prepare search marker
                $search = $this->templateMarkers['start'] . strtoupper($name) . $this->templateMarkers['end'];

                // Search and replace
                $renderedContent = str_replace($search, $value, $renderedContent);
            }
        }

        return $renderedContent;
    }
}