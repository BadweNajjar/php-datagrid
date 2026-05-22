<?php
namespace DataGrid\Asset;

/**
 * InlineAssets — inline CSS / JS file primitives.
 *
 * Reads a CSS or JS file from disk (resolved against the library root) and
 * echoes a self-contained `<style>` / `<script>` tag. Defensively escapes the
 * closing tag string to prevent embedded source from breaking out of the
 * wrapper. Extracted from `DataGrid::InlineCss()` / `DataGrid::InlineJs()`
 * during the Phase 4 refactor.
 *
 * @package DataGrid\Asset
 * @since   8.6.0
 */
class InlineAssets
{
    /** @var string Absolute filesystem path to the library root, with trailing slash. */
    private $directory;

    /** @var string Optional fallback root (e.g. project public/datagrid/) checked when a file is not found under $directory. */
    private $fallbackDirectory = '';

    /** @var string Newline string used for echoed HTML. */
    private $nl;

    public function __construct($directory, $nl = "\n", $fallbackDirectory = '')
    {
        $this->directory = $directory;
        $this->fallbackDirectory = $fallbackDirectory;
        $this->nl = $nl;
    }

    /** Resolve $relative_path against $directory, falling back to $fallbackDirectory when set. */
    private function ResolveFile($relative_path)
    {
        $relative_path = ltrim($relative_path, '/');
        $file = $this->directory . $relative_path;
        if (is_file($file)) return $file;
        if ($this->fallbackDirectory !== '') {
            $fallback = rtrim(str_replace('\\', '/', $this->fallbackDirectory), '/') . '/' . $relative_path;
            if (is_file($fallback)) return $fallback;
        }
        return '';
    }

    /**
     * Inline a CSS file into a <style> tag.
     *
     * @param string $relative_path Path relative to the library root.
     * @param string $media         Optional media attribute (e.g. 'print', 'screen').
     * @param bool   $ie_only       Wrap output in IE conditional comment when true.
     */
    public function Css($relative_path, $media = '', $ie_only = false)
    {
        $file = $this->ResolveFile($relative_path);
        if ($file === '') return;
        // Defensively neutralize any literal </style> inside the CSS so it doesn't close our wrapper tag.
        $css = str_replace('</style', '<\/style', file_get_contents($file));
        $media_attr = ($media !== '') ? ' media="'.$media.'"' : '';
        $tag = '<style type="text/css"'.$media_attr.'>'.$css.'</style>';
        if ($ie_only) $tag = '<!--[if IE]>'.$tag.'<![endif]-->';
        echo $this->nl.$tag;
    }

    /**
     * Inline a JS file into a <script> tag.
     *
     * @param string $relative_path Path relative to the library root.
     * @param bool   $leading_nl    Prepend a newline before the tag when true.
     */
    public function Js($relative_path, $leading_nl = false)
    {
        $file = $this->ResolveFile($relative_path);
        if ($file === '') return;
        // Escape any literal </script> inside the JS so it doesn't prematurely close our wrapper tag.
        $js = str_replace('</script', '<\/script', file_get_contents($file));
        echo ($leading_nl ? "\n" : '').'<script type="text/javascript">'."\n".$js."\n".'</script>'.$this->nl;
    }
}
