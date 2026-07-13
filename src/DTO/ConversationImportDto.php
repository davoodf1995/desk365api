<?php

namespace Davoodf1995\Desk365\DTO;

/**
 * Request body for POST /v3/tickets/import_conversations.
 */
class ConversationImportDto
{
    use DTOCommon;

    /**
     * @param  array<int, ConversationImportItemDto>  $conversations
     */
    public function __construct(
        public array $conversations,
    ) {}

    public function toArray(): array
    {
        return [
            'conversations' => array_map(
                static fn (ConversationImportItemDto $item) => $item->toArray(),
                $this->conversations,
            ),
        ];
    }

    public static function fromArray(array $data): self
    {
        $items = [];
        foreach ($data['conversations'] ?? [] as $row) {
            if (is_array($row)) {
                $items[] = ConversationImportItemDto::fromArray($row);
            }
        }

        return new self($items);
    }
}
