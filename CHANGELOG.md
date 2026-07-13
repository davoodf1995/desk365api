# Changelog

## 2.5.0 — 2026-07-13

### Fixed

- **Contact update (API spec):** `PUT /contacts/update` now sends `primary_email` only as a query parameter. Request body uses `CustomerDto::toUpdateArray()` (`UpdateContactRequestModel` fields only).
- **Contact lookup:** `primary_email` is trimmed before `GET /contacts/details` and update calls.
- **Attachment endpoints (HTTP 414):** `ticket_object`, `reply_object`, and `note_object` are sent as **multipart form fields**, not in the URL query string (`TicketController`, `Desk365TicketingService`).
- **Ticket update:** Empty `subject` is omitted from update payload.

### Added

- `CustomerDto::toUpdateArray()` for contact updates.
- `ConversationImportDto` / `ConversationImportItemDto` and `importConversations()` on `TicketController`, `Desk365TicketingService`, and `Desk365` facade.
- `config('desk365.api_log_model')` support in `ApiLoggingService` for host apps that need a custom Eloquent log model (e.g. multi-tenant).

## 2.4.x

Previous releases — see git history.
