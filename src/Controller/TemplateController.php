<?php

declare(strict_types=1);

namespace Alengo\SuluMcpServerBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Read-only access to local Sulu template XML files for the MCP server.
 *
 * Auth: requires `Authorization: Bearer <token>`. Token is configured via
 * `alengo_mcp_server.token` (defaults to the `MCP_SERVER_TOKEN` env var);
 * if empty, the endpoint is disabled.
 */
final class TemplateController
{
    /**
     * @param array<string, string> $templateDirs type => directory (relative to projectDir)
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly string $token,
        private readonly array $templateDirs,
    ) {
    }

    public function listAction(Request $request, string $type): JsonResponse
    {
        $this->assertAuthorized($request);
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

    public function showAction(Request $request, string $type, string $name): Response
    {
        $this->assertAuthorized($request);
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

    private function assertAuthorized(Request $request): void
    {
        if ('' === $this->token) {
            throw new AccessDeniedHttpException('MCP API disabled (token not configured)');
        }
        $header = $request->headers->get('Authorization', '');
        if (!\str_starts_with($header, 'Bearer ')) {
            throw new AccessDeniedHttpException('Missing bearer token');
        }
        if (!\hash_equals($this->token, \substr($header, 7))) {
            throw new AccessDeniedHttpException('Invalid bearer token');
        }
    }

    private function resolveDir(string $type): string
    {
        if (!isset($this->templateDirs[$type])) {
            throw new NotFoundHttpException(\sprintf('Unknown template type: %s', $type));
        }

        return $this->projectDir . '/' . $this->templateDirs[$type];
    }
}
