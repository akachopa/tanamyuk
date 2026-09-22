<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function ok(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $status);
    }

    protected function fail(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return ApiResponse::error($message, $status, $errors);
    }

    protected function userId(): string
    {
        return (string) auth()->id();
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function owned(string $modelClass, string $id): Model
    {
        return $modelClass::query()->where('user_id', $this->userId())->findOrFail($id);
    }
}
