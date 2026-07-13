<?php

namespace Davoodf1995\Desk365\DTO;

/**
 * Single conversation row for POST /v3/tickets/import_conversations.
 */
class ConversationImportItemDto
{
    use DTOCommon;

    public function __construct(
        public string $type,
        public string $body,
        public ?string $sender_type = null,
        public ?string $created_on = null,
        public ?string $created_by = null,
        public ?bool $public_note = null,
        public ?string $notified_agents = null,
        public ?string $cc_address = null,
        public ?string $bcc_address = null,
        public ?string $to_address = null,
        public ?string $attachment_public_urls = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'body' => $this->body,
            'sender_type' => $this->sender_type,
            'created_on' => $this->created_on,
            'created_by' => $this->created_by,
            'public_note' => $this->public_note,
            'notified_agents' => $this->notified_agents,
            'cc_address' => $this->cc_address,
            'bcc_address' => $this->bcc_address,
            'to_address' => $this->to_address,
            'attachment_public_urls' => $this->attachment_public_urls,
        ], static fn ($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        $notified = $data['notified_agents'] ?? null;
        if (is_array($notified)) {
            $notified = implode(',', array_filter($notified));
        }

        foreach (['cc_address', 'bcc_address', 'to_address', 'attachment_public_urls'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = implode(',', array_filter($data[$field]));
            }
        }

        $publicNote = $data['public_note'] ?? null;
        if (is_int($publicNote)) {
            $publicNote = $publicNote === 1;
        }

        return new self(
            type: (string) $data['type'],
            body: (string) $data['body'],
            sender_type: $data['sender_type'] ?? null,
            created_on: $data['created_on'] ?? null,
            created_by: $data['created_by'] ?? null,
            public_note: is_bool($publicNote) ? $publicNote : null,
            notified_agents: $notified ?: null,
            cc_address: $data['cc_address'] ?? null,
            bcc_address: $data['bcc_address'] ?? null,
            to_address: $data['to_address'] ?? null,
            attachment_public_urls: $data['attachment_public_urls'] ?? null,
        );
    }
}
