<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    NoteDto,
    ReplyDto
};
use Davoodf1995\Desk365\Traits\LogsApiCalls;
use Davoodf1995\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

/**
 * Desk365 has no standalone tickets/{id}/attachments REST resource.
 * Attachments are sent only via multipart:
 * - tickets/create_with_attachment
 * - tickets/add_reply_with_attachment
 * - tickets/add_note_with_attachment
 *
 * upload() uses add_note_with_attachment (private note by default) for extra files
 * after the primary create/reply/note call hits the multipart file cap.
 */
class AttachmentController
{
    use LogsApiCalls, HandlesApiResponses;

    private const UNSUPPORTED_MESSAGE = 'Desk365 API does not expose tickets/{ticket_number}/attachments. '
        . 'Use create_with_attachment, add_reply_with_attachment, or add_note_with_attachment. '
        . 'Attachment metadata is returned on ticket details and conversations (attachment_url).';

    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    /**
     * Upload a file to an existing ticket via add_note_with_attachment or add_reply_with_attachment.
     *
     * @param  array<string, mixed>  $metadata  Optional: body, agent_email, private_note, notify_emails, use_reply,
     *                                          plus reply fields (cc_emails, bcc_emails, from_email, include_prev_ccs, include_prev_messages)
     */
    public function upload(string $ticketNumber, $file, array $metadata = []): ApiResponseDto
    {
        try {
            $useReply = ! empty($metadata['use_reply']);

            if ($useReply) {
                $reply = new ReplyDto(
                    body: (string) ($metadata['body'] ?? '(attachment)'),
                    cc_emails: $metadata['cc_emails'] ?? null,
                    bcc_emails: $metadata['bcc_emails'] ?? null,
                    agent_email: $metadata['agent_email'] ?? null,
                    from_email: $metadata['from_email'] ?? null,
                    include_prev_ccs: isset($metadata['include_prev_ccs']) ? (int) $metadata['include_prev_ccs'] : 0,
                    include_prev_messages: isset($metadata['include_prev_messages']) ? (int) $metadata['include_prev_messages'] : 0,
                );
                $replyObject = json_encode($reply->toArray());
                $endpoint = $this->getEndpoint('tickets/add_reply_with_attachment', [
                    'ticket_number' => $ticketNumber,
                    'reply_object' => $replyObject,
                ]);
                $operation = 'uploadAttachmentViaReply';
            } else {
                $note = new NoteDto(
                    body: (string) ($metadata['body'] ?? '(attachment)'),
                    agent_email: $metadata['agent_email'] ?? null,
                    notify_emails: $metadata['notify_emails'] ?? null,
                    private_note: isset($metadata['private_note']) ? (int) $metadata['private_note'] : 1,
                );
                $noteObject = json_encode($note->toArray());
                $endpoint = $this->getEndpoint('tickets/add_note_with_attachment', [
                    'ticket_number' => $ticketNumber,
                    'note_object' => $noteObject,
                ]);
                $operation = 'uploadAttachmentViaNote';
            }

            $response = $this->makeLoggedApiCallWithFile(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [],
                file: $file,
                timeout: $this->config->timeout,
                operation: $operation
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Upload Attachment', [
                'ticket_number' => $ticketNumber,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to upload attachment: '.$e->getMessage());
        }
    }

    public function getAll(string $ticketId, array $params = []): ApiResponseDto
    {
        Log::warning('Desk365 API - getAttachments called but endpoint is not in API spec', [
            'ticket_number' => $ticketId,
        ]);

        return ApiResponseDto::error(self::UNSUPPORTED_MESSAGE);
    }

    public function getById(string $ticketId, string $attachmentId): ApiResponseDto
    {
        Log::warning('Desk365 API - getAttachment called but endpoint is not in API spec', [
            'ticket_number' => $ticketId,
            'attachment_id' => $attachmentId,
        ]);

        return ApiResponseDto::error(self::UNSUPPORTED_MESSAGE);
    }

    public function delete(string $ticketId, string $attachmentId): ApiResponseDto
    {
        Log::warning('Desk365 API - deleteAttachment called but endpoint is not in API spec', [
            'ticket_number' => $ticketId,
            'attachment_id' => $attachmentId,
        ]);

        return ApiResponseDto::error(self::UNSUPPORTED_MESSAGE);
    }

    public function download(string $ticketId, string $attachmentId): ApiResponseDto
    {
        Log::warning('Desk365 API - downloadAttachment called but endpoint is not in API spec', [
            'ticket_number' => $ticketId,
            'attachment_id' => $attachmentId,
        ]);

        return ApiResponseDto::error(self::UNSUPPORTED_MESSAGE);
    }
}
