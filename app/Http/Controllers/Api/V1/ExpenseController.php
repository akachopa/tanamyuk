<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Garden;
use App\Models\PlantingCycle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ExpenseController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $expenses = Expense::query()
            ->where('user_id', $this->userId())
            ->with(['expenseCategory', 'garden', 'plantingCycle.commodity'])
            ->when($request->filled('garden_id'), fn ($query) => $query->where('garden_id', $request->string('garden_id')))
            ->when($request->filled('planting_cycle_id'), fn ($query) => $query->where('planting_cycle_id', $request->string('planting_cycle_id')))
            ->orderByDesc('expense_date')
            ->get();

        return $this->ok($expenses);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateExpense($request, true);
        $this->assertLinks($data);

        $expense = Expense::query()->create([
            ...$data,
            'user_id' => $this->userId(),
        ]);

        return $this->ok($expense->load(['expenseCategory', 'garden', 'plantingCycle']), 'Biaya dicatat.', 201);
    }

    public function update(Request $request, string $expense): JsonResponse
    {
        /** @var Expense $model */
        $model = $this->owned(Expense::class, $expense);
        $data = $this->validateExpense($request, false);
        $this->assertLinks([
            'garden_id' => $data['garden_id'] ?? $model->garden_id,
            'planting_cycle_id' => $data['planting_cycle_id'] ?? $model->planting_cycle_id,
        ]);

        if (! isset($data['total'])) {
            $quantity = $data['quantity'] ?? $model->quantity;
            $unitPrice = $data['unit_price'] ?? $model->unit_price;
            if ($quantity !== null && $unitPrice !== null && ! $request->exists('total')) {
                $data['total'] = round(((float) $quantity) * ((float) $unitPrice), 2);
            }
        }

        $model->fill($data);
        $model->server_version = ((int) $model->server_version) + 1;
        $model->save();

        return $this->ok($model->load(['expenseCategory', 'garden', 'plantingCycle']), 'Biaya diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateExpense(Request $request, bool $creating): array
    {
        $data = $request->validate([
            'garden_id' => ['nullable', 'uuid'],
            'planting_cycle_id' => ['nullable', 'uuid'],
            'cycle_id' => ['nullable', 'uuid'],
            'expense_category_id' => ['nullable', 'uuid', 'exists:expense_categories,id'],
            'category_code' => ['nullable', 'string', 'exists:expense_categories,code'],
            'expense_date' => [$creating ? 'required' : 'sometimes', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
            'client_mutation_id' => ['nullable', 'string', 'max:255'],
        ]);

        if (! empty($data['cycle_id']) && empty($data['planting_cycle_id'])) {
            $data['planting_cycle_id'] = $data['cycle_id'];
        }
        unset($data['cycle_id']);

        if (empty($data['expense_category_id']) && ($creating || ! empty($data['category_code']))) {
            $data['expense_category_id'] = ExpenseCategory::query()
                ->where('code', $data['category_code'] ?? 'lain')
                ->value('id');
        }
        unset($data['category_code']);

        if ($creating && empty($data['expense_category_id'])) {
            throw ValidationException::withMessages(['expense_category_id' => 'Kategori biaya tidak ditemukan.']);
        }

        if (! $creating && empty($data['expense_category_id'])) {
            unset($data['expense_category_id']);
        }

        if ($creating && ! isset($data['total'])) {
            if (! isset($data['quantity'], $data['unit_price'])) {
                throw ValidationException::withMessages(['total' => 'Total biaya wajib diisi.']);
            }
            $data['total'] = round(((float) $data['quantity']) * ((float) $data['unit_price']), 2);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertLinks(array $data): void
    {
        if (! empty($data['garden_id']) && ! Garden::query()->where('user_id', $this->userId())->whereKey($data['garden_id'])->exists()) {
            throw ValidationException::withMessages(['garden_id' => 'Kebun tidak ditemukan.']);
        }

        if (! empty($data['planting_cycle_id']) && ! PlantingCycle::query()->where('user_id', $this->userId())->whereKey($data['planting_cycle_id'])->exists()) {
            throw ValidationException::withMessages(['planting_cycle_id' => 'Siklus tanam tidak ditemukan.']);
        }
    }
}
