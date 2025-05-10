<?php

declare(strict_types=1);

namespace Macrose\Hound;

use Macrose\Hound\DTO\CodeSnippet;
use Macrose\Hound\DTO\Event;
use Macrose\Hound\DTO\Metadata;
use Macrose\Hound\DTO\StackFrame;

final class EventBuilder
{
    private const string GIT_HEAD_FILE = '.git/HEAD';

    private const string GIT_REFS_DIR = '.git/refs/heads/';

    public function buildFromThrowable(
        \Throwable $e,
        string $environment,
        ?string $release = null,
        int $count = 1
    ): Event {
        $release = $release ?? $this->detectRelease();

        return new Event(
            eventId: $this->generateUuidV7(),
            message: $e->getMessage(),
            level: 'error',
            type: get_class($e),
            count: $count,
            metadata: $this->buildMetadata($e),
            environment: $environment,
            release: $release,
        );
    }

    private function buildMetadata(\Throwable $e): Metadata
    {
        $trace = $e->getTrace();
        $mainFrame = [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'function' => '{main}',
            'class' => '',
            'type' => '',
        ];

        $frames = array_merge([$mainFrame], $trace);
        $stacktrace = [];

        foreach ($frames as $frame) {
            $stacktrace[] = new StackFrame(
                file: $frame['file'] ?? 'unknown',
                line: $frame['line'] ?? 0,
                function: $frame['function'] ?? 'unknown',
                class: $frame['class'] ?? '',
                type: $frame['type'] ?? '',
                codeSnippet: $this->getCodeSnippet(
                    $frame['file'] ?? 'unknown',
                    $frame['line'] ?? 0
                ),
            );
        }

        $firstFrame = $frames[0];

        return new Metadata(
            fingerprint: $this->generateFingerprint($e),
            stacktrace: $stacktrace,
            file: $firstFrame['file'],
            line: $firstFrame['line'],
            function: $firstFrame['function'],
            class: $firstFrame['class'] ?? '',
            type: $firstFrame['type'] ?? '',
        );
    }

    private function getCodeSnippet(string $file, int $line): CodeSnippet
    {
        if (! file_exists($file) || $line <= 0) {
            return new CodeSnippet([], '', []);
        }

        try {
            $fileLines = @file($file);
            if ($fileLines === false) {
                return new CodeSnippet([], '', []);
            }

            $startLine = max(0, $line - 4);
            $endLine = min(count($fileLines), $line + 3);

            $preContext = array_slice($fileLines, $startLine, $line - $startLine - 1);
            $postContext = array_slice($fileLines, $line, $endLine - $line);

            return new CodeSnippet(
                preContext: array_map('trim', $preContext),
                contextLine: trim($fileLines[$line - 1] ?? ''),
                postContext: array_map('trim', $postContext),
            );
        } catch (\Throwable) {
            return new CodeSnippet([], '', []);
        }
    }

    private function detectRelease(): string
    {
        if (! file_exists(self::GIT_HEAD_FILE)) {
            return 'unknown/unknown';
        }

        $headContent = file_get_contents(self::GIT_HEAD_FILE);
        if (str_starts_with($headContent, 'ref:')) {
            $branch = trim(substr($headContent, 5));
            $branchName = basename($branch);
            $commitHash = $this->getCommitHashFromRef($branch);
        } else {
            $branchName = 'detached';
            $commitHash = trim($headContent);
        }

        return $branchName.'/'.substr($commitHash, 0, 7);
    }

    private function getCommitHashFromRef(string $ref): string
    {
        $refFile = self::GIT_REFS_DIR.basename($ref);

        return file_exists($refFile) ? trim(file_get_contents($refFile)) : 'unknown';
    }

    private function generateUuidV7(): string
    {
        $time = (int) (microtime(true) * 1000);
        $timeHex = dechex($time);

        return sprintf(
            '%08s-%04s-7%03s-%04x-%012s',
            substr($timeHex, 0, 8),
            substr($timeHex, 8, 4),
            substr($timeHex, 12, 3),
            random_int(0, 0x0FFF) | 0x4000,
            bin2hex(random_bytes(6))
        );
    }

    private function generateFingerprint(\Throwable $e): int
    {
        return crc32($e->getFile().':'.$e->getLine().':'.$e->getMessage());
    }

    public function buildFromMessage(
        string $message,
        string $level,
        string $environment,
        ?string $release = null
    ): Event {
        $release = $release ?? $this->detectRelease();
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? [];

        return new Event(
            eventId: $this->generateUuidV7(),
            message: $message,
            level: $level,
            type: 'message',
            count: 1,
            metadata: new Metadata(
                fingerprint: crc32($message),
                stacktrace: $this->buildStackTrace($backtrace),
                file: $caller['file'] ?? 'unknown',
                line: $caller['line'] ?? 0,
                function: $caller['function'] ?? '{main}',
                class: $caller['class'] ?? '',
                type: $caller['type'] ?? '',
            ),
            environment: $environment,
            release: $release,
        );
    }

    private function buildStackTrace(array $trace): array
    {
        $stacktrace = [];

        foreach ($trace as $frame) {
            $stacktrace[] = new StackFrame(
                file: $frame['file'] ?? 'unknown',
                line: $frame['line'] ?? 0,
                function: $frame['function'] ?? 'unknown',
                class: $frame['class'] ?? '',
                type: $frame['type'] ?? '',
                codeSnippet: $this->getCodeSnippet(
                    $frame['file'] ?? 'unknown',
                    $frame['line'] ?? 0
                ),
            );
        }

        return $stacktrace;
    }
}