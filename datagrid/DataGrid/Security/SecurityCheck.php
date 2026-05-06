<?php
namespace DataGrid\Security;

/**
 * SecurityCheck — query-string blacklist checker.
 *
 * Stateless. Returns true when the request appears clean for the given
 * security level, false when a suspicious pattern is detected. Extracted
 * from `DataGrid::SecurityCheck()` during the Phase 5 refactor.
 *
 * Levels: `'low'` | `'medium'` | `'high'` (anything else => pass-through).
 *
 * @package DataGrid\Security
 * @since   8.6.0
 */
class SecurityCheck
{
    /**
     * @param string $rid           Current row id from the request.
     * @param string $queryString   The raw QUERY_STRING.
     * @param string $level         Security level: low | medium | high.
     * @return bool                 true when request is clean, false on hit.
     */
    public static function Check($rid, $queryString, $level)
    {
        // check rid variable
        if (preg_match("/'/", $rid) || preg_match('/"/', $rid) || preg_match('/%27/', $rid) || preg_match('/%22/', $rid)) {
            return false;
        }

        $query_string = strtolower(rawurldecode($queryString));

        if ($level == 'low') {
            $bad_string = ['%00', '%01', 'document.cooki', '<script', 'script>', 'expression(', '<frame', ':'];
            foreach ($bad_string as $string_value) {
                if (strstr($query_string, $string_value)) return false;
            }
        }

        if ($level == 'medium') {
            $bad_string = ['%00', '%01', '%20union%20', '/*', '*/union/*', '+union+', 'document.cooki', '%3Cscrip', 'javascript:', '<script', 'script>', 'expression(', '<frame', '<iframe', '<applet', '<form', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'https://', 'ftp://', 'smb://', ':'];
            foreach ($bad_string as $string_value) {
                if (strstr($query_string, $string_value)) return false;
            }
        }

        if ($level == 'high') {
            $bad_string = ['%00', '%01', '%20union%20', '/*', '*/union/*', '+union+', 'insert+', 'update+', 'delete+', 'load_file', 'outfile', 'document.cooki', 'onmouse', '%3Cscrip', 'javascript:', '<script', 'script>', 'expression(', '<frame', '<iframe', '<applet', '<meta', '<style', '<form', '<img', '<body', '<link', '_GLOBALS', '_REQUEST', '_GET', '_POST', 'include_path', 'prefix', 'http://', 'https://', 'ftp://', 'smb://', ':'];
            foreach ($bad_string as $string_value) {
                if (strstr($query_string, $string_value)) return false;
            }
            if ((preg_match('/<[^>]*script*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*object*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*iframe*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*applet*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*meta*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*style*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*form*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*img*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*onmouseover*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/<[^>]*body*\"?[^>]*>/i', $query_string)) ||
                (preg_match('/\([^>]*\"?[^)]*\)/i', $query_string)) ||
                (preg_match('/ftp:\/\//i', $query_string)) ||
                (preg_match('/https:\/\//i', $query_string)) ||
                (preg_match('/http:\/\//i', $query_string))) {
                return false;
            }
        }

        return true;
    }
}
