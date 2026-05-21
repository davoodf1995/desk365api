<?php

use Davoodf1995\Desk365\DTO\ApiConfigDto;
use Davoodf1995\Desk365\DTO\ApiResponseDto;
use Davoodf1995\Desk365\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->config = new ApiConfigDto(
        baseUrl: 'https://matika.desk365.io',
        apiKey: 'test_api_key',
        timeout: 30,
        version: 'v3'
    );

    $this->controller = new AttachmentController($this->config);
});

it('uploads via add_note_with_attachment not tickets id attachments', function () {
    Http::fake([
        'matika.desk365.io/apis/v3/tickets/add_note_with_attachment*' => Http::response([
            'success' => true,
            'data' => ['ticket_number' => '1046'],
        ], 200),
    ]);

    $path = sys_get_temp_dir().'/desk365-attach-'.uniqid().'.txt';
    file_put_contents($path, 'overflow bytes');

    $response = $this->controller->upload('1046', $path);

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();

    Http::assertSent(function ($request) {
        $url = $request->url();

        return str_contains($url, '/apis/v3/tickets/add_note_with_attachment')
            && str_contains($url, 'ticket_number=1046')
            && str_contains($url, 'note_object=')
            && ! str_contains($url, '/tickets/1046/attachments');
    });

    @unlink($path);
});

it('returns error for unsupported get attachments without calling desk365', function () {
    Http::fake();

    $response = $this->controller->getAll('1046');

    expect($response->isSuccess())->toBeFalse()
        ->and($response->getMessage())->toContain('does not expose');

    Http::assertNothingSent();
});
