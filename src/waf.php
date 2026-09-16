<?php
// Mini-WAF: scans uploaded content for attack signatures.
// Returns a string reason if blocked, or false if clean.
function waf_check($content) {
    $lower = strtolower($content);

    $signatures = [
        // PHP tags  (note: '<?=' deliberately absent)
        '<?php', '<script language="php"',
        // command execution
        'system(', 'exec(', 'shell_exec(', 'passthru(',
        'popen(', 'proc_open(', 'pcntl_exec(',
        // code evaluation
        'eval(', 'assert(', 'create_function(', 'preg_replace',
        // common obfuscation helpers
        'base64_decode(', 'gzinflate(', 'gzuncompress(', 'str_rot13(',
        // file / network
        'file_put_contents(', 'fsockopen(', 'curl_exec(',
        // apache directives  (note: 'sethandler' deliberately absent)
        'addtype', 'addhandler', 'php_value', 'php_flag', 'php_admin_value',
    ];

    foreach ($signatures as $sig) {
        if (strpos($lower, $sig) !== false) {
            return 'blocked';   // deliberately vague - no hints to the attacker
        }
    }
    return false;
}
