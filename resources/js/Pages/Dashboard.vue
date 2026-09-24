<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const form = ref({
    id: null,
    name: '',
    description: '',
    price: '',
    image: null
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

const handleImageUpload = (event) => {
    form.value.image = event.target.files[0];
};

const submitProduct = async () => {
    try {
        const formData = new FormData();
        formData.append('name', form.value.name);
        if (form.value.description) formData.append('description', form.value.description);
        formData.append('price', form.value.price);
        if (form.value.image) {
            formData.append('image', form.value.image);
        }

        if (isEditing.value) {
            formData.append('_method', 'PUT'); // Laravel usa _method PUT em formulários multpart para update
            const response = await axios.post(`/api/products/${form.value.id}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            message.value = response.data.message;
        } else {
            const response = await axios.post('/api/products', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
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
        price: product.price,
        image: null
    };
    document.getElementById('image').value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
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
    form.value = { id: null, name: '', description: '', price: '', image: null };
    if(document.getElementById('image')) {
        document.getElementById('image').value = '';
    }
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Produto</label>
                            <input v-model="form.name" type="text" placeholder="Nome do Produto" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <input v-model="form.description" type="text" placeholder="Descrição" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preço</label>
                            <input v-model="form.price" type="number" step="0.01" placeholder="Preço (Ex: 19.99)" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Imagem do Produto</label>
                            <input id="image" type="file" @change="handleImageUpload" accept="image/*" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm p-2 bg-white" />
                        </div>
                        <div class="flex space-x-2 pt-2">
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Imagem</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="product in products" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <img v-if="product.image_url" :src="product.image_url" alt="Imagem" class="h-12 w-12 object-cover rounded-md border border-gray-200">
                                        <span v-else class="text-gray-400 text-xs italic">Sem imagem</span>
                                    </td>
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
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum produto cadastrado ainda.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>