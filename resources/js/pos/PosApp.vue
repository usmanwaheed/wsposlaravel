<template>
    <div class="grid gap-6 lg:grid-cols-[2fr,1fr]">
        <section class="space-y-4">
            <header class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Point of Sale</h1>
                    <p class="text-sm text-gray-500">Process walk-in sales and synchronize with WooCommerce.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-600">Cashier</p>
                    <p class="text-lg font-semibold">{{ user.name }}</p>
                </div>
            </header>

            <div class="rounded-lg bg-white p-4 shadow">
                <label class="block text-sm font-medium text-gray-600">Search Products</label>
                <div class="mt-2 flex gap-2">
                    <input v-model="searchTerm" @keyup.enter="loadProducts" type="search" placeholder="Search by name or SKU" class="w-full rounded border border-gray-300 px-3 py-2 focus:border-indigo-500 focus:outline-none" />
                    <button @click="loadProducts" class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Search</button>
                </div>
            </div>

            <div class="rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Variation</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Stock</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="product in products.data" :key="product.id">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">{{ product.name }}</p>
                                <p class="text-xs text-gray-500">{{ product.brand }} · {{ product.category }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-2">
                                    <div v-for="variation in product.variations" :key="variation.id" class="flex items-center justify-between rounded border border-gray-200 px-3 py-2">
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">{{ variation.color }} / {{ variation.size }}</p>
                                            <p class="text-xs text-gray-500">SKU {{ variation.sku }}</p>
                                        </div>
                                        <button @click="addToCart(product, variation)" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500" :disabled="variation.stock < 1">
                                            {{ variation.stock < 1 ? 'Out of stock' : 'Add' }}
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ totalStock(product) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ currency(product.base_price) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-gray-700">
                                <span class="rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-600">Tax {{ product.tax_rate ?? 0 }}%</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="border-t border-gray-200 px-4 py-3">
                    <button v-if="products.prev_page_url" @click="changePage(products.current_page - 1)" class="text-sm font-medium text-indigo-600">Previous</button>
                    <button v-if="products.next_page_url" @click="changePage(products.current_page + 1)" class="ml-3 text-sm font-medium text-indigo-600">Next</button>
                </div>
            </div>
        </section>

        <aside class="space-y-4">
            <div class="rounded-lg bg-white p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Cart</h2>
                <p v-if="cart.length === 0" class="mt-4 text-sm text-gray-500">Scan a barcode or add a product variation to begin.</p>
                <ul v-else class="mt-4 space-y-3">
                    <li v-for="item in cart" :key="item.key" class="rounded border border-gray-200 p-3">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ item.name }}</p>
                                <p class="text-xs text-gray-500">{{ item.color }} / {{ item.size }} · SKU {{ item.sku }}</p>
                            </div>
                            <button @click="removeFromCart(item.key)" class="text-xs font-semibold text-red-500">Remove</button>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button @click="decrement(item.key)" class="rounded border px-2">-</button>
                                <input type="number" min="1" class="w-16 rounded border px-2 py-1" v-model.number="item.quantity" @change="updateQuantity(item.key, item.quantity)" />
                                <button @click="increment(item.key)" class="rounded border px-2">+</button>
                            </div>
                            <div class="text-right text-sm">
                                <p class="font-semibold text-gray-900">{{ currency(item.unitPrice * item.quantity) }}</p>
                                <p class="text-xs text-gray-500">Discount</p>
                                <input type="number" min="0" class="w-20 rounded border px-2 py-1" v-model.number="item.discount" @change="updateQuantity(item.key, item.quantity)" />
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="rounded-lg bg-white p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Payment</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>{{ currency(summary.subtotal) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Discount</span>
                        <span>-{{ currency(summary.discount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tax</span>
                        <span>{{ currency(summary.tax) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-semibold">
                        <span>Total</span>
                        <span>{{ currency(summary.total) }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600">Payment Method</label>
                    <select v-model="payment.method" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-600">Customer Email (optional)</label>
                    <input v-model="customer.email" type="email" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" />
                </div>

                <label class="mt-3 flex items-center gap-2 text-sm">
                    <input type="checkbox" v-model="syncToWooCommerce" />
                    Sync order to WooCommerce
                </label>

                <button :disabled="cart.length === 0 || submitting" @click="checkout" class="mt-5 w-full rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-gray-300">
                    {{ submitting ? 'Processing...' : 'Complete Sale' }}
                </button>
            </div>

            <div class="rounded-lg bg-white p-4 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Invoice Preview</h2>
                <div class="mt-3 space-y-2 text-xs">
                    <p>Garment Shop POS</p>
                    <p>{{ new Date().toLocaleString() }}</p>
                    <p>Cashier: {{ user.name }}</p>
                    <hr />
                    <div v-for="item in cart" :key="`preview-${item.key}`" class="flex justify-between">
                        <div>
                            <p class="font-semibold">{{ item.name }}</p>
                            <p>{{ item.color }} / {{ item.size }} × {{ item.quantity }}</p>
                        </div>
                        <div class="text-right">
                            <p>{{ currency(item.unitPrice * item.quantity) }}</p>
                            <p v-if="item.discount">-{{ currency(item.discount) }}</p>
                        </div>
                    </div>
                    <hr />
                    <p>Total: {{ currency(summary.total) }}</p>
                </div>
            </div>
        </aside>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue';

const props = defineProps({
    user: { type: Object, required: true },
});

const user = props.user;

const products = reactive({ data: [], current_page: 1, next_page_url: null, prev_page_url: null });
const searchTerm = ref('');
const cart = reactive([]);
const payment = reactive({ method: 'cash', amount: 0 });
const customer = reactive({ email: '' });
const syncToWooCommerce = ref(false);
const submitting = ref(false);

const loadProducts = async (page = 1) => {
    try {
        const response = await window.axios.get('/api/products', {
            params: {
                search: searchTerm.value,
                page,
            },
        });

        Object.assign(products, response.data);
    } catch (error) {
        console.error(error);
        alert('Unable to load products.');
    }
};

const changePage = (page) => {
    loadProducts(page);
};

const totalStock = (product) => product.variations.reduce((total, variation) => total + Number(variation.stock || 0), 0);

const currency = (value) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value || 0);

const addToCart = (product, variation) => {
    const key = `${variation.id}`;
    const existing = cart.find((line) => line.key === key);

    if (existing) {
        existing.quantity += 1;
        return;
    }

    cart.push({
        key,
        productId: product.id,
        variationId: variation.id,
        name: product.name,
        color: variation.color,
        size: variation.size,
        sku: variation.sku,
        quantity: 1,
        unitPrice: Number(variation.price ?? product.base_price ?? 0),
        discount: 0,
        taxRate: Number(product.tax_rate || 0),
    });
};

const removeFromCart = (key) => {
    const index = cart.findIndex((line) => line.key === key);
    if (index > -1) {
        cart.splice(index, 1);
    }
};

const increment = (key) => {
    const item = cart.find((line) => line.key === key);
    if (item) {
        item.quantity += 1;
    }
};

const decrement = (key) => {
    const item = cart.find((line) => line.key === key);
    if (item && item.quantity > 1) {
        item.quantity -= 1;
    }
};

const updateQuantity = (key, quantity) => {
    const item = cart.find((line) => line.key === key);
    if (item) {
        item.quantity = Math.max(1, quantity || 1);
    }
};

const summary = computed(() => {
    let subtotal = 0;
    let discount = 0;
    let tax = 0;

    cart.forEach((item) => {
        const lineSubtotal = Number(item.unitPrice || 0) * Number(item.quantity || 0);
        subtotal += lineSubtotal;
        const lineDiscount = Number(item.discount || 0);
        discount += lineDiscount;
        tax += (Number(item.taxRate || 0) / 100) * (lineSubtotal - lineDiscount);
    });

    const total = subtotal - discount + tax;
    payment.amount = total;

    return { subtotal, discount, tax, total };
});

const checkout = async () => {
    submitting.value = true;
    try {
        await window.axios.post('/api/orders', {
            customer: customer.email ? { email: customer.email, name: customer.email } : null,
            items: cart.map((item) => ({
                product_variation_id: item.variationId,
                quantity: item.quantity,
                discount: item.discount,
            })),
            payment: {
                method: payment.method,
                amount: payment.amount,
            },
            sync_to_woocommerce: syncToWooCommerce.value,
        });

        cart.splice(0, cart.length);
        syncToWooCommerce.value = false;
        customer.email = '';
        await loadProducts();
        alert('Sale completed successfully.');
    } catch (error) {
        console.error(error);
        alert('Unable to complete sale.');
    } finally {
        submitting.value = false;
    }
};

onMounted(() => {
    loadProducts();
});
</script>
