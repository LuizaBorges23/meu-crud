<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const form = ref({
    id: null,
    name: '',
    description: '',
    price: ''
});

const isEditing = ref(false);
const message = ref('');
const products = ref([]);

const fetchProducts = async () => {
    try {
        const response = await axios.get('/api/products');
        products.value = response.data;
    } catch (error) {
        console.error("Erro ao carregar produtos:", error);
    }
};

const submitProduct = async () => {
    try {
        if (isEditing.value) {
            // Se estiver a editar, faz um pedido PUT para atualizar
            const response = await axios.put(`/api/products/${form.value.id}`, form.value);
            message.value = response.data.message;
        } else {
            // Se não, faz um pedido POST para criar
            const response = await axios.post('/api/products', form.value);
            message.value = response.data.message;
        }
        
        resetForm();
        fetchProducts();
        setTimeout(() => message.value = '', 3000);
    } catch (error) {
        console.error(error);
        message.value = 'Erro ao processar o produto.';
    }
};

const editProduct = (product) => {
    isEditing.value = true;
    form.value = { 
        id: product.id,
        name: product.name, 
        description: product.description, 
        price: product.price 
    };
    window.scrollTo({ top: 0, behavior: 'smooth' }); // Sobe o ecrã até ao formulário
};

const deleteProduct = async (id) => {
    if (!confirm('Tem a certeza que deseja apagar este produto?')) return;
    try {
        await axios.delete(`/api/products/${id}`);
        fetchProducts();
    } catch (error) {
        console.error("Erro ao apagar produto:", error);
    }
};

const resetForm = () => {
    isEditing.value = false;
    form.value = { id: null, name: '', description: '', price: '' };
};

onMounted(() => {
    fetchProducts();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestão de Produtos</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Formulário de Cadastro / Edição -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-900">
                        {{ isEditing ? 'Editar Produto' : 'Cadastrar Novo Produto' }}
                    </h3>
                    <form @submit.prevent="submitProduct" class="space-y-4 max-w-md">
                        <div>
                            <input v-model="form.name" type="text" placeholder="Nome do Produto" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                        </div>
                        <div>
                            <input v-model="form.description" type="text" placeholder="Descrição" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <input v-model="form.price" type="number" step="0.01" placeholder="Preço (Ex: 19.99)" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition">
                                {{ isEditing ? 'Atualizar Produto' : 'Salvar Produto' }}
                            </button>
                            <button v-if="isEditing" @click="resetForm" type="button" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-500 transition">
                                Cancelar
                            </button>
                        </div>
                    </form>
                    <p v-if="message" class="mt-4 text-green-600 font-medium">{{ message }}</p>
                </div>

                <!-- Tabela de Produtos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-900">Produtos Cadastrados</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ product.name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ product.description || 'Sem descrição' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">€ {{ parseFloat(product.price).toFixed(2) }}</td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <button @click="editProduct(product)" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                            Editar
                                        </button>
                                        <button @click="deleteProduct(product.id)" class="text-red-600 hover:text-red-900 font-bold">
                                            Apagar
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="products.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum produto cadastrado ainda.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>