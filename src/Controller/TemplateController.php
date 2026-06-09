<?php

declare(strict_types=1);

namespace Alengo\SuluMcpServerBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Read-only access to local Sulu template XML files for the MCP server.
 *
 * Auth: the endpoints live under the admin API prefix (`^/admin`), so the
 * standard Sulu admin firewall protects them — a request without a valid
 * admin session is rejected before reaching the controller.
 */
final class TemplateController
{
    /**
     * @param array<string, string> $templateDirs type => directory (relative to projectDir)
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly array $templateDirs,
    ) {
    }

    public function listAction(string $type): JsonResponse
    {
        $dir = $this->resolveDir($type);
        $names = [];
        if (\is_dir($dir)) {
            foreach (\scandir($dir) ?: [] as $entry) {
                if (\str_ends_with($entry, '.xml')) {
                    $names[] = \substr($entry, 0, -4);
                }
            }
            \sort($names);
        }

        return new JsonResponse(['type' => $type, 'templates' => $names]);
    }

    public function showAction(string $type, string $name): Response
    {
        $path = $this->resolveDir($type) . '/' . $name . '.xml';
        if (!\is_file($path)) {
            throw new NotFoundHttpException(\sprintf('Template "%s/%s" not found', $type, $name));
        }
        $xml = \file_get_contents($path);
        if (false === $xml) {
            throw new NotFoundHttpException(\sprintf('Template "%s/%s" not readable', $type, $name));
        }

        return new Response($xml, 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }

    private function resolveDir(string $type): string
    {
        if (!isset($this->templateDirs[$type])) {
            throw new NotFoundHttpException(\sprintf('Unknown template type: %s', $type));
        }

        return $this->projectDir . '/' . $this->templateDirs[$type];
    }
}
