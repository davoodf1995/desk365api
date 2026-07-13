<?php

namespace Davoodf1995\Desk365\Services;

use Davoodf1995\Desk365\Models\Desk365ApiLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ApiLoggingService
{
    /**
     * Log API call to database
     */
    public function log(
        string $method,
        string $endpoint,
        ?array $requestHeaders,
        ?string $requestBody,
        ?int $responseStatus,
        ?string $responseBody,
        ?int $durationMs,
        ?string $operation,
        ?string $errorMessage
    ): void {
        try {
            if (! $this->tableExists()) {
                Log::debug('Desk365 API Log table does not exist, skipping database log', [
                    'endpoint' => $endpoint,
                    'method' => $method,
                ]);

                return;
            }

            $modelClass = config('desk365.api_log_model', Desk365ApiLog::class);
            if (! is_string($modelClass) || ! is_a($modelClass, Model::class, true)) {
                Log::warning('Desk365 api_log_model config invalid; using package default model.', [
                    'configured' => $modelClass,
                ]);
                $modelClass = Desk365ApiLog::class;
            }

            /** @var class-string<Model> $modelClass */
            $modelClass::query()->create([
                'method' => $method,
                'endpoint' => $endpoint,
                'request_headers' => $requestHeaders,
                'request_body' => $requestBody,
                'response_status' => $responseStatus,
                'response_body' => $responseBody,
                'duration_ms' => $durationMs,
                'operation' => $operation,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log Desk365 API call to database', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method,
            ]);
        }
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    public function sanitizeHeaders(array $headers): array
    {
        if (empty($headers)) {
            return [];
        }
        
        $sanitized = $headers;
        
        // Remove or mask sensitive headers (case-insensitive check)
        $sensitiveKeys = ['api-key', 'api_secret', 'authorization', 'x-api-key', 'x-api-secret'];
        
        foreach ($sanitized as $headerKey => $value) {
            $headerKeyLower = strtolower($headerKey);
            foreach ($sensitiveKeys as $sensitiveKey) {
                if ($headerKeyLower === strtolower($sensitiveKey)) {
                    $sanitized[$headerKey] = '***REDACTED***';
                    break;
                }
            }
        }
        
        return $sanitized;
    }

    /**
     * Check if the desk365_api_logs table exists
     */
    private function tableExists(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('desk365_api_logs');
        } catch (\Exception $e) {
            return false;
        }
    }
}

