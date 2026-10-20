<?php

namespace Blueways\BwCaptcha\Utility;

use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Stores the solutions of generated captchas in the frontend user session.
 *
 * A solution never leaves the server: images and audio files reference a phrase by its handle,
 * which is a random value that is only meaningful inside the session that created it.
 */
class PhraseSessionUtility
{
    protected const SESSION_KEY = 'captchaPhrases';

    /**
     * Number of unsolved captchas kept per session. Older ones are discarded.
     */
    protected const MAX_PHRASES = 10;

    public static function createHandle(): string
    {
        return 'h' . bin2hex(random_bytes(8));
    }

    public static function isHandle(string $handle): bool
    {
        return preg_match('/^h[0-9a-f]{16}$/', $handle) === 1;
    }

    public static function store(
        FrontendUserAuthentication $feUser,
        string $handle,
        string $phrase,
        int $lifetime
    ): void {
        $phrases = self::read($feUser);
        unset($phrases[$handle]);
        $phrases[$handle] = [
            'phrase' => $phrase,
            'expires' => time() + max(1, $lifetime),
        ];

        if (count($phrases) > self::MAX_PHRASES) {
            $phrases = array_slice($phrases, -self::MAX_PHRASES, null, true);
        }

        self::write($feUser, $phrases);
    }

    public static function get(FrontendUserAuthentication $feUser, string $handle): ?string
    {
        return self::read($feUser)[$handle]['phrase'] ?? null;
    }

    public static function getLatest(FrontendUserAuthentication $feUser): ?string
    {
        $phrases = self::read($feUser);
        $latest = array_pop($phrases);

        return $latest['phrase'] ?? null;
    }

    /**
     * Removes the phrase that matches the given input and reports whether there was one.
     */
    public static function consume(FrontendUserAuthentication $feUser, string $input): bool
    {
        $phrases = self::read($feUser);
        $solvedHandle = null;

        foreach ($phrases as $handle => $entry) {
            if ($entry['phrase'] !== '' && hash_equals($entry['phrase'], $input)) {
                $solvedHandle = $handle;
            }
        }

        if ($solvedHandle === null) {
            return false;
        }

        unset($phrases[$solvedHandle]);
        self::write($feUser, $phrases);

        return true;
    }

    /**
     * @return array<string, array{phrase: string, expires: int}>
     */
    protected static function read(FrontendUserAuthentication $feUser): array
    {
        $phrases = $feUser->getKey('ses', self::SESSION_KEY);
        if (!is_array($phrases)) {
            return [];
        }

        $time = time();
        $validPhrases = [];
        foreach ($phrases as $handle => $entry) {
            if (!is_string($handle)
                || !is_array($entry)
                || !is_string($entry['phrase'] ?? null)
                || !is_int($entry['expires'] ?? null)
                || $entry['expires'] <= $time
            ) {
                continue;
            }
            $validPhrases[$handle] = ['phrase' => $entry['phrase'], 'expires' => $entry['expires']];
        }

        return $validPhrases;
    }

    /**
     * @param array<string, array{phrase: string, expires: int}> $phrases
     */
    protected static function write(FrontendUserAuthentication $feUser, array $phrases): void
    {
        $feUser->setKey('ses', self::SESSION_KEY, $phrases);
        $feUser->storeSessionData();
    }
}
