<?php
// Simple IP-based rate limiter.
//
// Counts attempts per (ip, action) inside a rolling time window and reports
// whether the caller has already used up its allowance. One row per recorded
// attempt; the count query measures the trailing window, so there is no
// fixed-boundary reset an attacker can time.
//
// State is kept in SQLite rather than $_SESSION on purpose: an attacker driving
// these forms with curl sends no cookie jar, so PHP would hand out a fresh
// session on every request and a session-based counter would limit nothing.

function client_ip(): string {
    // REMOTE_ADDR is the TCP peer address - it cannot be forged by the client.
    // X-Forwarded-For is deliberately NOT used: it is a request header an
    // attacker can set to any value, which would defeat the limiter entirely.
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// True when this ip+action has already reached $max attempts within $window seconds.
function rate_limited(PDO $pdo, string $ip, string $action, int $max, int $window): bool {
    $s = $pdo->prepare('SELECT COUNT(*) FROM rate_limits WHERE ip = ? AND action = ? AND created_at > ?');
    $s->execute([$ip, $action, time() - $window]);
    return (int)$s->fetchColumn() >= $max;
}

// Records one attempt against the ip+action bucket.
function rate_record(PDO $pdo, string $ip, string $action): void {
    $pdo->prepare('INSERT INTO rate_limits (ip, action, created_at) VALUES (?, ?, ?)')
        ->execute([$ip, $action, time()]);
    // Opportunistic cleanup so the table cannot grow without bound.
    $pdo->prepare('DELETE FROM rate_limits WHERE created_at < ?')->execute([time() - 3600]);
}

// Clears the bucket. Called after a successful sign-in so that a legitimate
// administrator who mistyped a few times is not one mistake away from a lockout.
function rate_clear(PDO $pdo, string $ip, string $action): void {
    $pdo->prepare('DELETE FROM rate_limits WHERE ip = ? AND action = ?')->execute([$ip, $action]);
}
