@php($currentVariations = collect($variations ?? [])->values())

<div class="grid gap-6 lg:grid-cols-2">
    <div class="space-y-4 rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Product details</h2>

        <label class="block text-sm">
            <span class="text-gray-600">Name</span>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
        </label>

        <label class="block text-sm">
            <span class="text-gray-600">Category</span>
            <input type="text" name="category" value="{{ old('category', $product->category) }}" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
        </label>

        <label class="block text-sm">
            <span class="text-gray-600">Brand</span>
            <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
        </label>

        <label class="block text-sm">
            <span class="text-gray-600">Description</span>
            <textarea name="description" rows="4" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">{{ old('description', $product->description) }}</textarea>
        </label>
    </div>

    <div class="space-y-4 rounded-lg bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Pricing & tax</h2>

        <label class="block text-sm">
            <span class="text-gray-600">Base price</span>
            <input type="number" name="base_price" step="0.01" min="0" value="{{ old('base_price', $product->base_price ?? 0) }}" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
        </label>

        <label class="block text-sm">
            <span class="text-gray-600">Tax rate (%)</span>
            <input type="number" name="tax_rate" step="0.01" min="0" value="{{ old('tax_rate', $product->tax_rate ?? 0) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
        </label>

        <p class="text-sm text-gray-500">Leave variation price blank to inherit the base price at checkout.</p>
    </div>
</div>

<div class="mt-8 rounded-lg bg-white p-6 shadow">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Variations</h2>
        <button type="button" id="add-variation" class="inline-flex items-center rounded border border-indigo-200 px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50">Add variation</button>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left font-medium uppercase tracking-wider text-gray-500">Color</th>
                    <th class="px-4 py-2 text-left font-medium uppercase tracking-wider text-gray-500">Size</th>
                    <th class="px-4 py-2 text-left font-medium uppercase tracking-wider text-gray-500">SKU</th>
                    <th class="px-4 py-2 text-left font-medium uppercase tracking-wider text-gray-500">Barcode</th>
                    <th class="px-4 py-2 text-right font-medium uppercase tracking-wider text-gray-500">Price</th>
                    <th class="px-4 py-2 text-right font-medium uppercase tracking-wider text-gray-500">Stock</th>
                    <th class="px-4 py-2 text-right font-medium uppercase tracking-wider text-gray-500"></th>
                </tr>
            </thead>
            <tbody id="variation-rows" data-index="{{ $currentVariations->count() }}" class="divide-y divide-gray-200">
                @foreach ($currentVariations as $index => $variation)
                    <tr data-variation-row>
                        <td class="px-4 py-2">
                            <input type="hidden" name="variations[{{ $index }}][id]" value="{{ $variation['id'] ?? '' }}">
                            <input type="text" name="variations[{{ $index }}][color]" value="{{ old("variations.$index.color", $variation['color'] ?? '') }}" required class="w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" name="variations[{{ $index }}][size]" value="{{ old("variations.$index.size", $variation['size'] ?? '') }}" required class="w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" name="variations[{{ $index }}][sku]" value="{{ old("variations.$index.sku", $variation['sku'] ?? '') }}" required class="w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
                        </td>
                        <td class="px-4 py-2">
                            <input type="text" name="variations[{{ $index }}][barcode]" value="{{ old("variations.$index.barcode", $variation['barcode'] ?? '') }}" class="w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none">
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" step="0.01" min="0" name="variations[{{ $index }}][price]" value="{{ old("variations.$index.price", $variation['price'] ?? '') }}" class="w-full rounded border border-gray-300 px-3 py-2 text-right focus:border-indigo-500 focus:outline-none">
                        </td>
                        <td class="px-4 py-2">
                            <input type="number" min="0" name="variations[{{ $index }}][stock]" value="{{ old("variations.$index.stock", $variation['stock'] ?? 0) }}" required class="w-full rounded border border-gray-300 px-3 py-2 text-right focus:border-indigo-500 focus:outline-none">
                        </td>
                        <td class="px-4 py-2 text-right">
                            <button type="button" class="text-sm font-semibold text-red-500 hover:text-red-600" data-remove-variation>Remove</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const rows = document.getElementById('variation-rows');
        const addButton = document.getElementById('add-variation');

        if (!rows || !addButton) {
            return;
        }

        let index = Number(rows.dataset.index) || rows.querySelectorAll('[data-variation-row]').length;

        const createCell = (name, type = 'text', options = {}) => {
            const input = document.createElement('input');
            input.type = type;
            input.name = name;
            input.className = options.className || 'w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none';
            if (options.required) {
                input.required = true;
            }
            if (options.placeholder) {
                input.placeholder = options.placeholder;
            }
            if (options.step) {
                input.step = options.step;
            }
            if (options.min !== undefined) {
                input.min = options.min;
            }
            return input;
        };

        const buildRow = () => {
            const row = document.createElement('tr');
            row.dataset.variationRow = '';

            const cells = [
                () => {
                    const cell = document.createElement('td');
                    cell.className = 'px-4 py-2';
                    const hiddenId = createCell(`variations[${index}][id]`, 'hidden');
                    const input = createCell(`variations[${index}][color]`, 'text', { required: true });
                    cell.append(hiddenId, input);
                    return cell;
                },
                () => {
                    const cell = document.createElement('td');
                    cell.className = 'px-4 py-2';
                    const input = createCell(`variations[${index}][size]`, 'text', { required: true });
                    cell.append(input);
                    return cell;
                },
                () => {
                    const cell = document.createElement('td');
                    cell.className = 'px-4 py-2';
                    const input = createCell(`variations[${index}][sku]`, 'text', { required: true });
                    cell.append(input);
                    return cell;
                },
                () => {
                    const cell = document.createElement('td');
                    cell.className = 'px-4 py-2';
                    const input = createCell(`variations[${index}][barcode]`);
                    cell.append(input);
                    return cell;
                },
                () => {
                    const cell = document.createElement('td');
                    cell.className = 'px-4 py-2';
                    const input = createCell(`variations[${index}][price]`, 'number', {
                        step: '0.01',
                        min: '0',
                        className: 'w-full rounded border border-gray-300 px-3 py-2 text-right focus:border-indigo-500 focus:outline-none',
                    });
                    cell.append(input);
                    return cell;
                },
                () => {
                    const cell = document.createElement('td');
                    cell.className = 'px-4 py-2';
                    const input = createCell(`variations[${index}][stock]`, 'number', {
                        min: '0',
                        required: true,
                        className: 'w-full rounded border border-gray-300 px-3 py-2 text-right focus:border-indigo-500 focus:outline-none',
                    });
                    cell.append(input);
                    return cell;
                },
                () => {
                    const cell = document.createElement('td');
                    cell.className = 'px-4 py-2 text-right';
                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.textContent = 'Remove';
                    removeBtn.className = 'text-sm font-semibold text-red-500 hover:text-red-600';
                    removeBtn.dataset.removeVariation = '';
                    cell.append(removeBtn);
                    return cell;
                },
            ];

            cells.forEach((makeCell) => row.append(makeCell()));
            index += 1;
            rows.dataset.index = index;
            return row;
        };

        addButton.addEventListener('click', () => {
            rows.append(buildRow());
        });

        rows.addEventListener('click', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.dataset.removeVariation !== undefined) {
                const row = target.closest('tr');
                if (row && rows.children.length > 1) {
                    row.remove();
                }
            }
        });
    });
</script>
@endpush
